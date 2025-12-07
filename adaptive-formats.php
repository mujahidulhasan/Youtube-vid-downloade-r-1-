<?php
// adaptive-formats.php
// index.php থেকে $adaptiveFormats ব্যবহার করছে।

?>
<h3>Adaptive Formats (Video Only / Audio Only)</h3>
<p style="font-size:12px; color:#555;">Note: High resolution videos may be Video-Only. You may need external software to combine them with audio.</p>
<table class="striped">
    <tr>
        <th>Quality</th>
        <th>Type</th>
        <th>Download</th>
    </tr>
    <?php
    foreach ($adaptiveFormats as $videoFormat) {
        $url = @$videoFormat->url;
        $quality = @$videoFormat->qualityLabel ? $videoFormat->qualityLabel : (@$videoFormat->audioQuality ? @$videoFormat->audioQuality : "Unknown");
        $mimeType = @$videoFormat->mimeType ? explode(";",explode("/",$videoFormat->mimeType)[1])[0] : "mp4";
        
        // Signature Cipher Handling
        if (empty($url) && isset($videoFormat->signatureCipher)) {
            parse_str(parse_url("https://example.com?" . $videoFormat->signatureCipher, PHP_URL_QUERY), $parse_signature);
            $url = $parse_signature['url'] . "&sig=" . $parse_signature['s'];
        }
        
        // যদি URL না পাওয়া যায়, skip করুন
        if (empty($url)) continue;
        
        // ভিডিও নাকি অডিও তা নির্ধারণ করা
        $fileType = (isset($videoFormat->mimeType) && str_contains($videoFormat->mimeType, 'audio')) ? 'Audio' : 'Video';
        
        ?>
        <tr>
            <td><?php echo htmlspecialchars($quality); ?></td>
            <td><?php echo htmlspecialchars($fileType) . " (" . htmlspecialchars($mimeType) . ")"; ?></td>
            <td>
                <a href="video-downloader.php?link=<?php echo urlencode($url)?>&title=<?php echo urlencode($videoTitle)?>&type=<?php echo urlencode($mimeType); ?>">
                Download <?php echo $fileType; ?></a>
            </td>
        </tr>
    <?php }?>
</table>
