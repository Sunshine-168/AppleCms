<?php
session_start();
ob_clean();
error_reporting(0);

$length = 5;
$characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
$captcha = '';
for ($i = 0; $i < $length; $i++) {
    $captcha .= $characters[random_int(0, strlen($characters) - 1)];
}
$_SESSION['captcha_code'] = $captcha;
$width = 120;
$height = 40;
$image = imagecreatetruecolor($width, $height);
$bgColor = imagecolorallocate($image, 245, 245, 245);
$textColor = imagecolorallocate($image, 50, 50, 50);
$lineColor = imagecolorallocate($image, 150, 150, 150);
$pixelColor = imagecolorallocate($image, 100, 100, 100);

imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

for ($i = 0; $i < 5; $i++) {
    imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $lineColor);
}

for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, random_int(0, $width), random_int(0, $height), $pixelColor);
}

$font = 5;
$fontWidth = imagefontwidth($font);
$fontHeight = imagefontheight($font);
$textWidth = $fontWidth * strlen($captcha);
$x = ($width - $textWidth) / 2;
$y = ($height - $fontHeight) / 2;
imagestring($image, $font, $x, $y, $captcha, $textColor);

header('Content-Type: image/png');
header('Cache-Control: no-cache');
imagepng($image);
imagedestroy($image);
exit;