<?php
/*
MINIGAL NANO
- A PHP/HTML/CSS based image gallery script

This script and included files are subject to licensing from Creative Commons (http://creativecommons.org/licenses/by-sa/2.5/)
You may use, edit and redistribute this script, as long as you pay tribute to the original author by NOT removing the linkback to www.minigal.dk ("Powered by MiniGal Nano x.x.x")

MiniGal Nano is created by Thomas Rybak

Copyright 2010 by Thomas Rybak
Support: www.minigal.dk
Community: www.minigal.dk/forum

Please enjoy this free script!


USAGE EXAMPLE:
File: createthumb.php
Example: <img src="createthumb.php?filename=photo.jpg&amp;width=100&amp;height=100">
*/
//  error_reporting(E_ALL);

function create_thumb($filename, $outfile, $size = 120) {
    // Define variables
    $target = "";
    $xoord = 0;
    $yoord = 0;

    if (preg_match("/\.mp4$|\.mts$|\.mov$|\.m4v$|\.m4a$|\.aiff$|\.avi$|\.caf$|\.dv$|\.qtz$|\.flv$/i", $filename)) {
        exec("ffmpegthumbnailer -i " . escapeshellarg($filename) . " -o " . escapeshellarg($outfile) . " -s " . escapeshellarg($size) . " -c jpeg -a -f");
        return;
    }

    $imgsize = GetImageSize($filename);
    $width = $imgsize[0];
    $height = $imgsize[1];
    if ($width > $height) { // If the width is greater than the height it’s a horizontal picture
        $xoord = ceil(($width-$height)/2);
        $width = $height;      // Then we read a square frame that  equals the width
    } else {
        $yoord = ceil(($height-$width)/2);
        $height = $width;
    }

    // Rotate JPG pictures
    if (preg_match("/\.jpg$|\.jpeg$/i", $filename)) {
        if (function_exists('exif_read_data') && function_exists('imagerotate')) {
            $exif = exif_read_data($filename);
            $ort = $exif['IFD0']['Orientation'];
            $degrees = 0;
            switch($ort) {
                case 6: // 90 rotate right
                    $degrees = 270;
                break;
                case 8:    // 90 rotate left
                    $degrees = 90;
                break;
            }
            if ($degrees != 0)  $target = imagerotate($target, $degrees, 0);
        }
    }

    $target = ImageCreatetruecolor($size,$size);
    if (preg_match("/\.jpg$|\.jpeg$/i", $filename)) $source = ImageCreateFromJPEG($filename);
    if (preg_match("/\.gif$/i", $filename)) $source = ImageCreateFromGIF($filename);
    if (preg_match("/\.png$/i", $filename)) $source = ImageCreateFromPNG($filename);
    imagecopyresampled($target,$source,0,0,$xoord,$yoord,$size,$size,$width,$height);
    imagedestroy($source);

    if (preg_match("/\.jpg$|\.jpeg$/i", $filename)) ImageJPEG($target,$outfile,90);
    if (preg_match("/\.gif$/i", $filename)) ImageGIF($target,$outfile,90);
    if (preg_match("/\.png$/i", $filename)) ImageJPEG($target,$outfile,90); // Using ImageJPEG on purpose
    imagedestroy($target);
}


$_GET['filename'] = "./" . $_GET['filename'];
$_GET['size']=filter_var($_GET['size'], FILTER_VALIDATE_INT);
if ($_GET['size'] == false) $_GET['size'] = 120;

// Display error image if file isn't found
if (preg_match("/\.\.\//i", $_GET['filename']) || !is_file($_GET['filename'])) {
    header('Content-type: image/jpeg');
    $errorimage = ImageCreateFromJPEG('images/questionmark.jpg');
    ImageJPEG($errorimage,null,90);
    imagedestroy($errorimg);
    exit;
}

// Display error image if file exists, but can't be opened
if (substr(decoct(fileperms($_GET['filename'])), -1, strlen(fileperms($_GET['filename']))) < 4 OR substr(decoct(fileperms($_GET['filename'])), -3,1) < 4) {
    header('Content-type: image/jpeg');
    $errorimage = ImageCreateFromJPEG('images/cannotopen.jpg');
    ImageJPEG($errorimage,null,90);
    imagedestroy($errorimg);
    exit;
}

// $extension = preg_replace('.*\.\w*$', '', $_GET['filename']);

if (preg_match("/.gif$/i", $_GET['filename'])) {
    header('Content-type: image/gif');
    $cleanext = 'gif';
} else if (preg_match("/\.jpg$|\.jpeg$|\.png$|\.mp4$|\.mts$|\.mov$|\.m4v$|\.m4a$|\.aiff$|\.avi$|\.caf$|\.dv$|\.qtz$|\.flv$/i", $_GET['filename'])) {
    header('Content-type: image/jpeg');
    $cleanext = 'jpeg';
} else {
    header('Content-type: image/jpeg');
    $errorimage = ImageCreateFromJPEG('images/cannotopen.jpg');
    ImageJPEG($errorimage,null,90);
    imagedestroy($errorimg);
    exit;
}

// Create paths for different picture versions
$md5sum = md5($_GET['filename']);
$thumbnail = "/tmp/thumbnails/" . $md5sum . "_" . $_GET['size'] . "." . $cleanext;
if(!file_exists("/tmp/thumbnails"))
    mkdir("/tmp/thumbnails");

if (!is_file($thumbnail)) {
    create_thumb($_GET['filename'], $thumbnail, $_GET['size']);
}

if ( $cleanext == 'gif') {
    $img = ImageCreateFromGIF($thumbnail);
    ImageGIF($img,null,90);
} else {
    $img = ImageCreateFromJPEG($thumbnail);
    ImageJPEG($img,null,90);
}
imagedestroy($img);
?>
