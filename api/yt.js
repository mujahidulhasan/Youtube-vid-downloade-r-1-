// File: api/yt.js  (Vercel Serverless Function)

export default async function handler(req, res) {
  try {
    const { videoId, url } = req.query;

    // 1) URL / ID check
    if (!videoId && !url) {
      return res.status(400).json({
        ok: false,
        where: "api",
        error: "Missing videoId or url query param",
      });
    }

    // 2) RapidAPI key (Vercel env থেকে)
    const apiKey = process.env.RAPIDAPI_KEY;
    if (!apiKey) {
      return res.status(500).json({
        ok: false,
        where: "api",
        error: "RAPIDAPI_KEY env is NOT set on server",
      });
    }

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

    // --- RapidAPI কল ---
    const r = await fetch(apiUrl, {
      method: "GET",
      headers: {
        "x-rapidapi-host": "youtube-media-downloader.p.rapidapi.com",
        "x-rapidapi-key": apiKey,
      },
    });

    const text = await r.text();
    let raw;
    try {
      raw = JSON.parse(text);
    } catch {
      raw = null;
    }

    // 3) RapidAPI থেকে error এলে একই status + debug ফেরত দাও
    if (!r.ok) {
      return res.status(r.status).json({
        ok: false,
        where: "rapidapi",
        status: r.status,
        error: "RapidAPI returned non-200 status (Key Invalid/Quota Exceeded)",
        body: raw || text,
      });
    }

    if (raw && raw.errorId && raw.errorId !== "Success") {
      return res.status(200).json({
        ok: false,
        where: "rapidapi-payload",
        errorId: raw.errorId,
        reason: raw.reason || raw.message || "Unknown API payload error",
        raw,
      });
    }

    // ---- স্ট্রিম প্রসেসিং (ডুপ্লিকেট ফিক্স ও ক্যাটাগরি) ----
    const streams = [];
    const addArr = (arr) => {
      if (Array.isArray(arr)) arr.forEach((it) => streams.push(it));
    };

    if (raw.videos && Array.isArray(raw.videos.items))
      addArr(raw.videos.items);
    ["medias", "formats", "streams", "results", "downloads", "videoStreams", "audioStreams", "items"].forEach(
      (k) => addArr(raw[k])
    );

    const norm = [];
    const seenUrls = new Set();

    for (const it of streams) {
      if (!it || typeof it !== "object") continue;
      const u =
        it.url ||
        it.downloadUrl ||
        it.streamUrl ||
        it.mediaUrl ||
        it.source ||
        it.link;
      if (!u || seenUrls.has(u)) continue;
      seenUrls.add(u);

      const mimeType = (it.mimeType || it.type || "").toLowerCase();
      const ext =
        (it.extension || (mimeType.split("/").pop()) || "").toLowerCase();

      norm.push({
        url: u,
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
          (it.height ? it.height + "p" : null),
        bitrate: it.bitrate || it.audioBitrate || it.bitrateKbps || null,
      });
    }

    const mp4 = [];
    const webm = [];
    const audio = [];
    const other = [];
    const videoOnly = [];

    for (const s of norm) {
      const mt = s.mimeType || "";
      const ext = s.extension || "";
      const lu = s.url.toLowerCase();
      const hasRes = s.height > 0;

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
      
      const isMuxed = 
          (s.height > 0 && s.height <= 480) || 
          (s.height > 0 && (s.hasAudio === true || s.bitrate));

      if (ext === "mp4" || mt.includes("video/mp4")) {
        if (isMuxed) {
            mp4.push(s); 
        } else if(hasRes) {
            videoOnly.push(s); 
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
    
    // Add high-resolution video-only streams to the main lists for user choice
    for (const s of videoOnly) {
        if (s.extension === 'mp4' || s.mimeType.includes("video/mp4")) {
            mp4.push(s);
        } else if (s.extension === 'webm' || s.mimeType.includes("video/webm")) {
            webm.push(s);
        }
    }

    // Deduplication by Resolution+Extension
    function dedupeRes(list) {
      const seen = new Set();
      const out = [];
      for (const s of list) {
        const key = (s.height || "0") + "|" + (s.extension || "");
        if (!seen.has(key)) {
          seen.add(key);
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
      ok: true, // Success flag for frontend
      errorId: raw.errorId,
      id: raw.id,
      title: raw.title,
      description: raw.description,
      lengthSeconds: raw.lengthSeconds,
      viewCount: raw.viewCount,
      thumbnails: raw.thumbnails,
      channel: raw.channel,
      streams: {
        mp4: sortByHeight(dedupeRes(mp4)),
        webm: sortByHeight(dedupeRes(webm)),
        audio: audio,
        other: other,
      },
    });
  } catch (e) {
    console.error(e);
    return res.status(500).json({
      ok: false,
      where: "handler-catch",
      error: e.message,
    });
  }
}
