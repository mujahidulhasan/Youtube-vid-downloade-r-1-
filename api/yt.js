// File: api/yt.js (Vercel serverless function)
// This function fetches data from RapidAPI, processes the streams, and returns clean JSON.

export default async function handler(req, res) {
  try {
    const { videoId, url } = req.query;

    const apiKey = process.env.RAPIDAPI_KEY; // Key must be set in Vercel Environment Variables
    if (!apiKey) {
      return res.status(500).json({ error: "RAPIDAPI_KEY env missing. Please set the key in Vercel settings." });
    }

    if (!videoId && !url) {
      return res.status(400).json({ error: "videoId or url is required" });
    }

    // --- Build Query Parameters ---
    const params = new URLSearchParams({
      videos: "auto",
      audios: "auto",
      urlAccess: "normal",
    });

    if (videoId) params.set("videoId", videoId);
    if (url) params.set("url", url);

    const apiUrl =
      "https://youtube-media-downloader.p.rapidapi.com/v2/video/details?" +
      params.toString();

    // --- Fetch from RapidAPI ---
    const r = await fetch(apiUrl, {
      method: "GET",
      headers: {
        "x-rapidapi-host": "youtube-media-downloader.p.rapidapi.com",
        "x-rapidapi-key": apiKey,
      },
    });

    if (!r.ok) {
      const txt = await r.text();
      return res
        .status(500)
        .json({ error: "RapidAPI external error", status: r.status, body: txt });
    }

    const raw = await r.json();

    // If API returns an error message
    if (raw.errorId && raw.errorId !== "Success") {
      return res.status(200).json(raw);
    }

    // --- Stream Collection and Normalization ---
    const streams = [];
    function addArr(arr) {
      if (Array.isArray(arr)) arr.forEach((it) => streams.push(it));
    }

    if (raw.videos && Array.isArray(raw.videos.items))
      addArr(raw.videos.items);
    ["medias", "formats", "streams", "results", "downloads", "videoStreams", "audioStreams", "items"].forEach(
      (k) => addArr(raw[k])
    );

    const norm = [];
    const seenUrls = new Set();

    for (const it of streams) {
      if (!it || typeof it !== "object") continue;
      const url2 =
        it.url ||
        it.downloadUrl ||
        it.streamUrl ||
        it.mediaUrl ||
        it.source ||
        it.link;
      if (!url2 || seenUrls.has(url2)) continue;
      seenUrls.add(url2);

      const mimeType = (it.mimeType || it.type || "").toLowerCase();
      const ext =
        (it.extension || (mimeType.split("/").pop()) || "").toLowerCase();

      // Normalize common stream data
      norm.push({
        url: url2,
        mimeType,
        extension: ext,
        hasAudio:
          typeof it.hasAudio !== "undefined" ? !!it.hasAudio : null,
        height: it.height || null,
        width: it.width || null,
        size:
          it.sizeText || it.size || it.filesize || it.data_size || null,
        quality:
          it.quality_label ||
          it.quality ||
          it.format_note ||
          null,
        bitrate: it.bitrate || it.audioBitrate || it.bitrateKbps || null,
      });
    }

    const mp4 = [];
    const webm = [];
    const audio = [];
    const other = [];
    const videoOnly = []; // To store streams without guaranteed audio

    for (const s of norm) {
      const mt = s.mimeType || "";
      const ext = s.extension || "";
      const lu = s.url.toLowerCase();
      const hasRes = s.height > 0;
      
      // Heuristic Check for Audio-Only:
      const isAudioCandidate = 
        mt.startsWith("audio/") ||
        ["mp3", "m4a", "aac", "opus"].includes(ext) ||
        lu.includes(".m4a") ||
        lu.includes(".mp3") ||
        lu.includes("mime=audio");

      if (isAudioCandidate && !hasRes) {
        audio.push(s);
        continue;
      }
      
      // Heuristic Check for Muxed/Combined Audio+Video:
      // We consider it muxed if it's low resolution or explicitly marked with audio props
      const isMuxed = 
          (s.height > 0 && s.height <= 480) || 
          (s.height > 0 && (s.hasAudio === true || s.bitrate));

      if (ext === "mp4" || mt.includes("video/mp4")) {
        if (isMuxed) {
            mp4.push(s); // Muxed (Guaranteed Audio for low res or high confidence)
        } else if(hasRes) {
            videoOnly.push(s); // High Res (Likely Video-Only, needs warning)
        }
      } else if (ext === "webm" || mt.includes("video/webm")) {
         if (isMuxed) {
            webm.push(s);
        } else if(hasRes) {
            videoOnly.push(s);
        }
      } else {
        other.push(s);
      }
    }
    
    // Add high-resolution video-only streams to the main lists for user choice, 
    // even though they lack audio (audio is a warning on the frontend).
    for (const s of videoOnly) {
        if (s.extension === 'mp4' || s.mimeType.includes("video/mp4")) {
            mp4.push(s);
        } else if (s.extension === 'webm' || s.mimeType.includes("video/webm")) {
            webm.push(s);
        }
    }


    function dedupeRes(list) {
      const seen = new Set();
      const out = [];
      for (const s of list) {
        // Dedupe by resolution + extension, prioritizing streams with bitrate info (likely better muxed/audio quality)
        const resKey = (s.height || "") + "|" + (s.extension || "");
        if (!seen.has(resKey)) {
          seen.add(resKey);
          out.push(s);
        }
      }
      return out;
    }

    function sortByHeight(list) {
      return list.sort(
        (a, b) => (a.height || 0) - (b.height || 0)
      );
    }

    return res.status(200).json({
      errorId: raw.errorId,
      id: raw.id,
      title: raw.title,
      description: raw.description,
      lengthSeconds: raw.lengthSeconds,
      viewCount: raw.viewCount,
      thumbnails: raw.thumbnails,
      channel: raw.channel,
      streams: {
        // We dedupe and sort the lists before sending to frontend
        mp4: sortByHeight(dedupeRes(mp4)),
        webm: sortByHeight(dedupeRes(webm)),
        audio: audio, 
        other: other,
      },
    });
  } catch (e) {
    console.error(e);
    return res.status(500).json({ error: "Server error", message: e.message });
  }
    }
