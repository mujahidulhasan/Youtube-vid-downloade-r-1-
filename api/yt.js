// File: api/yt.js (Vercel Serverless Function)
// এই ফাইলটি শুধু URL প্যারামিটার গ্রহণ করবে এবং এরর হ্যান্ডলিং করে ডেটা ফিল্টার করবে।

export default async function handler(req, res) {
  try {
    const { url } = req.query;

    if (!url) {
      return res.status(400).json({
        ok: false,
        where: "api",
        error: "Missing url query param. Use /api/yt?url=YOUTUBE_URL",
      });
    }

    const apiKey = process.env.RAPIDAPI_KEY;
    if (!apiKey) {
      return res.status(500).json({
        ok: false,
        where: "api",
        error: "Configuration Error: RAPIDAPI_KEY env is NOT set on server",
      });
    }

    // প্যারামিটার হিসেবে পুরো URL পাঠানো হচ্ছে (videoId extract এর ঝামেলা এড়াতে)
    const params = new URLSearchParams({
      url: url, 
      urlAccess: "normal",
      videos: "auto",
      audios: "auto",
    });

    const apiUrl =
      "https://youtube-media-downloader.p.rapidapi.com/v2/video/details?" +
      params.toString();

    const r = await fetch(apiUrl, {
      method: "GET",
      headers: {
        "x-rapidapi-host": "youtube-media-downloader.p.rapidapi.com",
        "x-rapidapi-key": apiKey,
      },
    });

    const txt = await r.text();
    let raw;
    try { raw = JSON.parse(txt); } catch { raw = null; }

    if (!r.ok) {
      return res.status(r.status).json({
        ok: false,
        where: "rapidapi",
        status: r.status,
        error: "RapidAPI returned non-200 status (Invalid Key or Quota Exceeded)",
        body: raw || txt,
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

    // ---- স্ট্রিম প্রসেসিং (Dedupe, Audio/Video Separate) ----
    const streams = [];
    const addArr = (arr) => { if (Array.isArray(arr)) arr.forEach((it) => streams.push(it)); };

    if (raw.videos && Array.isArray(raw.videos.items)) addArr(raw.videos.items);
    ["medias", "formats", "streams", "results", "downloads", "videoStreams", "audioStreams", "items"].forEach(
      (k) => addArr(raw[k])
    );

    const norm = [];
    const seenUrls = new Set();

    for (const it of streams) {
      if (!it || typeof it !== "object") continue;
      const u =
        it.url || it.downloadUrl || it.streamUrl || it.mediaUrl || it.source || it.link;
      if (!u || seenUrls.has(u)) continue;
      seenUrls.add(u);

      const mimeType = (it.mimeType || it.type || "").toLowerCase();
      const ext =
        (it.extension || (mimeType.split("/").pop()) || "").toLowerCase();

      norm.push({
        url: u,
        mimeType,
        extension: ext,
        hasAudio: typeof it.hasAudio !== "undefined" ? !!it.hasAudio : null,
        height: it.height || null,
        width: it.width || null,
        size: it.sizeText || it.size || it.filesize || it.data_size || null,
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
    const audioOnly = [];
    const other = [];

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
        audioOnly.push(s);
        continue;
      }
      
      const isMuxedCandidate = (s.height > 0 && s.height <= 480) || s.hasAudio === true || s.bitrate;

      if (ext === "mp4" || mt.includes("video/mp4")) {
        if (isMuxedCandidate) {
            mp4.push(s); 
        } else if(hasRes) {
            // Video-only streams (high res) also added for full options, but frontend will warn.
            mp4.push(s); 
        }
      } else if (ext === "webm" || mt.includes("video/webm")) {
         if (isMuxedCandidate) {
            webm.push(s);
        } else if(hasRes) {
            webm.push(s);
        }
      } else {
        other.push(s);
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
      return list.sort((a, b) => (a.height || 0) - (b.height || 0));
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
        audio: audioOnly,
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
