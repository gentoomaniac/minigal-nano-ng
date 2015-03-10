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
File: createsmall.php
Example: <img src="createsmall.php?filename=photo.jpg&amp;size=100">
*/
//  error_reporting(E_ALL);

require("config_default.php");
include("config.php");

function create_thumb($filename, $outfile, $size = 1024) {
    // Define variables
    $target = "";
    $height = $size;
    $width = $size;

    // Get new dimensions
    list($width_orig, $height_orig) = getimagesize($filename);

    $ratio_orig = $width_orig/$height_orig;

    if ($width/$height > $ratio_orig) {
       $width = $height*$ratio_orig;
    } else {
       $height = $width/$ratio_orig;
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

    $target = ImageCreatetruecolor($width,$height);
    if (preg_match("/\.jpg$|\.jpeg$/i", $filename)) $source = ImageCreateFromJPEG($filename);
    if (preg_match("/\.gif$/i", $filename)) $source = ImageCreateFromGIF($filename);
    if (preg_match("/\.png$/i", $filename)) $source = ImageCreateFromPNG($filename);
    imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
    imagedestroy($source);

    if (preg_match("/\.jpg$|\.jpeg$/i", $filename)) ImageJPEG($target,$outfile,90);
    if (preg_match("/\.gif$/i", $filename)) ImageGIF($target,$outfile,90);
    if (preg_match("/\.png$/i", $filename)) ImageJPEG($target,$outfile,90); // Using ImageJPEG on purpose
    imagedestroy($target);
}

$_GET['filename'] = "./" . $_GET['filename'];
$_GET['size']=filter_var($_GET['size'], FILTER_VALIDATE_INT);
if ($_GET['size'] == false) $_GET['size'] = 1024;

// Display error image if file isn't found
if (preg_match("/\.\.\//i", $_GET['filename']) || !is_file($_GET['filename'])) {
    header('Content-type: image/jpeg');
    $errorimage = ImageCreateFromJPEG('images/questionmark.jpg');
    ImageJPEG($errorimage,null,90);
}

// Display error image if file exists, but can't be opened
if (substr(decoct(fileperms($_GET['filename'])), -1, strlen(fileperms($_GET['filename']))) < 4 OR substr(decoct(fileperms($_GET['filename'])), -3,1) < 4) {
    header('Content-type: image/jpeg');
    $errorimage = ImageCreateFromJPEG('images/cannotopen.jpg');
    ImageJPEG($errorimage,null,90);
}

$extension = preg_replace('.*\.\w*$', '', $_GET['filename']);

if (preg_match("/\.jpg$|\.jpeg|\.png$$/i", $_GET['filename'])) {
    header('Content-type: image/jpeg');
    $cleanext = 'jpeg';
}
if (preg_match("/\.gif$/i", $_GET['filename'])) {
    header('Content-type: image/gif');
    $cleanext = 'gif';
}

// Create paths for different picture versions
$md5sum = md5($_GET['filename']);
$small = $small_path . "/" . $md5sum . "_" . $_GET['size'] . "." . $cleanext;
if(!file_exists($small_path))
    mkdir($small_path);


if (!is_file($small)) {
    create_thumb($_GET['filename'], $small, $_GET['size']);
}

if ( $cleanext == 'jpeg') {
    $img = ImageCreateFromJPEG($small);
    if(!$img) {
        create_thumb($_GET['filename'], null, $_GET['size']);
        exit;
    }
    ImageJPEG($img,null,90);
} else if ( $cleanext == 'gif') {
    $img = ImageCreateFromGIF($small);
    if(!$img) {
        create_thumb($_GET['filename'], null, $_GET['size']);
        exit;
    }
    ImageGIF($img,null,90);
} else {
    $img = ImageCreateFromJPEG('images/cannotopen.jpg');
    ImageJPEG($img,null,90);
}
imagedestroy($img);
?>
