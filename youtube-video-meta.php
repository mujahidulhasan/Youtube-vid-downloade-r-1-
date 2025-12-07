<?php
// youtube-video-meta.php

function getYoutubeVideoMeta($videoId, $key){
    $ch = curl_init();
    $curlUrl = 'https://www.youtube.com/youtubei/v1/player?key=' . $key;
    curl_setopt($ch, CURLOPT_URL, $curlUrl);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    
    // PHP কোডের উদাহরণের মতো জটিল JSON Payload
    $curlOptions = '{"context": {"client": {"hl": "en","clientName": "WEB",
        "clientVersion": "2.20210721.00.00","clientFormFactor": "UNKNOWN_FORM_FACTOR","clientScreen": "WATCH",
        "mainAppWebInfo": {"graftUrl": "/watch?v=' . $videoId . '",}},"user": {"lockedSafetyMode": false},
        "request": {"useSsl": true,"internalExperimentFlags": [],"consistencyTokenJars": []}},
        "videoId": "' . $videoId . '",  "playbackContext": {"contentPlaybackContext":
        {"vis": 0,"splay": false,"autoCaptionsDefaultOn": false,
        "autonavState": "STATE_NONE","html5Preference": "HTML5_PREF_WANTS","lactMilliseconds": "-1"}},
        "racyCheckOk": false,  "contentCheckOk": false}';
        
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlOptions);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $curlResult = curl_exec($ch);
    
    if (curl_errno($ch)) {
        // ত্রুটি হলে সার্ভারের লগ দেখতে হবে
        error_log('cURL Error: ' . curl_error($ch));
        return json_encode(['error' => 'cURL connection failed.']);
    }
    curl_close($ch);
    return $curlResult;
}
?>
