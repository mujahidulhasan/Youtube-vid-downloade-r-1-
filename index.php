<?php
// index.php

if (isset($_POST['submit'])) {
    $videoUrl = $_POST['youtube-video-url'];
    
    // ভিডিও আইডি বের করা
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $videoUrl, $match);
    $youtubeVideoId = $match[1];
    
    if (!isset($youtubeVideoId)) {
        $error_message = "Invalid YouTube URL provided!";
    } else {
        // API Key সেট করা
        $apiKey = "AIzaSyBj8z2uULHa0MpZCu3r29r9N2ZZSmhMEHE"; // 💡 আপনার API Key এখানে সেট করা হয়েছে
        
        // মেটাডেটা ফেচ করার জন্য রিকোয়ার
        require_once './youtube-video-meta.php'; 
        
        $videoMeta = json_decode(getYoutubeVideoMeta($youtubeVideoId, $apiKey));
        
        // ত্রুটি চেক (যদি API ত্রুটি দেয়)
        if (!isset($videoMeta->streamingData)) {
             $error_message = "Video data could not be fetched from YouTube API. Please check your API key or video link.";
        } else {
            $videoTitle = $videoMeta->videoDetails->title;
            $videoThumbnails = $videoMeta->videoDetails->thumbnail->thumbnails;
            $thumbnail = end($videoThumbnails)->url;
            $videoFormats = $videoMeta->streamingData->formats;
            $adaptiveFormats = $videoMeta->streamingData->adaptiveFormats;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YT Player API Video Downloader</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .row { display: flex; gap: 10px; margin-bottom: 20px; }
        input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 15px; background-color: #ff0000; color: white; border: none; border-radius: 4px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 14px; }
        th { background-color: #f2f2f2; }
        .error { color: red; font-weight: bold; }
        img { max-width: 100%; height: auto; border-radius: 4px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <form method="post" action="">
            <h1>PHP YouTube Video Downloader Script</h1>
            <div class="row">
                <input type="text" name="youtube-video-url" placeholder="Paste YouTube Video URL" value="<?php echo isset($videoUrl) ? htmlspecialchars($videoUrl) : ''; ?>">
                <button type="submit" name="submit" id="submit">Download Video</button>
            </div>
        </form>

        <?php if (isset($error_message)): ?>
            <p class="error">ERROR: <?php echo $error_message; ?></p>
        <?php elseif (isset($videoTitle)): ?>
            
            <p>URL: <a href="<?php echo htmlspecialchars($videoUrl);?>"><?php echo htmlspecialchars($videoUrl);?></a></p>
            <p><img src="<?php echo htmlspecialchars($thumbnail); ?>"></p>
            <h2>Video title: <?php echo htmlspecialchars($videoTitle); ?></h2>
            <p><?php echo isset($videoMeta->videoDetails->shortDescription) ? str_split(htmlspecialchars($videoMeta->videoDetails->shortDescription), 100)[0] : '';?></p>

            <?php if (! empty($videoFormats)): ?>
                
                <h3>Combined Streams (Video & Sound)</h3>
                <table class="striped">
                    <tr>
                        <th>Quality</th>
                        <th>Type</th>
                        <th>Download</th>
                    </tr>
                    <?php
                    foreach ($videoFormats as $videoFormat) {
                        $url = @$videoFormat->url;
                        $mimeType = @$videoFormat->mimeType ? explode(";",explode("/",$videoFormat->mimeType)[1])[0] : "mp4";
                        $quality = @$videoFormat->qualityLabel ? $videoFormat->qualityLabel : "Unknown";
                        
                        // Signature Cipher Handling (যদি সরাসরি URL না থাকে) - বট ডিটেকশন এড়াতে সাহায্য করে
                        if (empty($url) && isset($videoFormat->signatureCipher)) {
                            parse_str(parse_url("https://example.com?" . $videoFormat->signatureCipher, PHP_URL_QUERY), $parse_signature);
                            $url = $parse_signature['url'] . "&sig=" . $parse_signature['s'];
                        }
                        
                        // যদি URL এখনও না পাওয়া যায় বা ভিডিও-অনলি হয়, skip করুন
                        if (empty($url) || (isset($videoFormat->qualityLabel) && !isset($videoFormat->audioQuality))) continue;
                        
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($quality); ?></td>
                            <td><?php echo htmlspecialchars($mimeType); ?></td>
                            <td>
                                <a href="video-downloader.php?link=<?php echo urlencode($url)?>&title=<?php echo urlencode($videoTitle)?>&type=<?php echo urlencode($mimeType); ?>">
                                Download Video</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                
                <?php 
                // Adaptive Formats (ভিডিও-অনলি এবং অডিও-অনলি)
                if (!empty($adaptiveFormats)) {
                    require './adaptive-formats.php';
                }
                ?>

            <?php endif; ?>

        <?php endif; ?>
    </div>
</body>
</html>
