<?php
function resizeAndCropImage($srcPath, $destPath, $width = 300, $height = 300) {
    $imgInfo = getimagesize($srcPath);
    if (!$imgInfo) return false;

    [$origWidth, $origHeight] = $imgInfo;
    $mime = $imgInfo['mime'];

    switch ($mime) {
        case 'image/jpeg':
            $srcImage = imagecreatefromjpeg($srcPath);
            break;
        case 'image/png':
            $srcImage = imagecreatefrompng($srcPath);
            break;
        case 'image/gif':
            $srcImage = imagecreatefromgif($srcPath);
            break;
        default:
            return false;
    }

    // Crop logic
    $srcAspect = $origWidth / $origHeight;
    $destAspect = $width / $height;

    if ($srcAspect > $destAspect) {
        // Source is wider
        $newHeight = $height;
        $newWidth = (int)($height * $srcAspect);
    } else {
        // Source is taller
        $newWidth = $width;
        $newHeight = (int)($width / $srcAspect);
    }

    $tempImage = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($tempImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

    // Crop to center
    $x0 = ($newWidth - $width) / 2;
    $y0 = ($newHeight - $height) / 2;
    $finalImage = imagecreatetruecolor($width, $height);
    imagecopy($finalImage, $tempImage, 0, 0, $x0, $y0, $width, $height);

    imagejpeg($finalImage, $destPath, 90); // Save as JPEG

    imagedestroy($srcImage);
    imagedestroy($tempImage);
    imagedestroy($finalImage);

    return true;
}
