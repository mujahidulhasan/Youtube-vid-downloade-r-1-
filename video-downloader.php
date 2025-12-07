<?php
// video-downloader.php: এই স্ক্রিপ্টটি ডাউনলোড প্রক্রিয়া সম্পন্ন করে

$downloadURL = isset($_GET['link']) ? urldecode($_GET['link']) : '';
$downloadFileName = isset($_GET['title']) ? urldecode($_GET['title']) : 'video_download';
$mimeType = isset($_GET['type']) ? urldecode($_GET['type']) : 'mp4';

$finalFileName = preg_replace('/[^a-zA-Z0-9\s\.\-]/', '', $downloadFileName) . '.' . $mimeType;

if (!empty($downloadURL) && substr($downloadURL, 0, 8) === 'https://') {
    // ডাউনলোড হেডার সেট করা
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=\"$finalFileName\"");
    header("Content-Type: application/$mimeType");
    header("Content-Transfer-Encoding: binary");
    
    // ফাইলটি সরাসরি YouTube থেকে পড়ে ব্রাউজারে পাঠানো
    readfile($downloadURL);
} else {
    echo "Invalid download URL.";
}
?>
