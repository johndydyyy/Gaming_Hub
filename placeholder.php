<?php

header('Content-Type: image/png');

$text = isset($_GET['text']) ? $_GET['text'] : 'GamingHub';
$width = isset($_GET['width']) ? (int)$_GET['width'] : 400;
$height = isset($_GET['height']) ? (int)$_GET['height'] : 300;


$image = imagecreatetruecolor($width, $height);

$bgColor = imagecolorallocate($image, 41, 45, 62);
$textColor = imagecolorallocate($image, 255, 255, 255);
$accentColor = imagecolorallocate($image, 0, 150, 255);


imagefill($image, 0, 0, $bgColor);


for ($i = 0; $i < 5; $i++) {
    $x1 = rand(0, $width);
    $y1 = rand(0, $height);
    $x2 = rand(0, $width);
    $y2 = rand(0, $height);
    $color = imagecolorallocatealpha($image, 0, 150, 255, 90);
    imageline($image, $x1, $y1, $x2, $y2, $color);
}


$fontSize = min($width / 10, $height / 5);
$font = 5; // Default font
$textBoundingBox = imagettfbbox($fontSize, 0, 'arial.ttf', $text);
$textWidth = $textBoundingBox[2] - $textBoundingBox[0];
$textX = ($width - $textWidth) / 2;
$textY = $height / 2 + $fontSize / 2;

$textBgPadding = 20;
imagefilledrectangle(
    $image, 
    $textX - $textBgPadding, 
    $textY - $fontSize - $textBgPadding, 
    $textX + $textWidth + $textBgPadding, 
    $textY + $textBgPadding, 
    imagecolorallocatealpha($image, 0, 0, 0, 70)
);


imagettftext($image, $fontSize, 0, $textX, $textY, $textColor, 'arial.ttf', $text);


imagerectangle($image, 0, 0, $width-1, $height-1, $accentColor);

imagepng($image);

imagedestroy($image);
?>
