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

if(!defined("MINIGAL_INTERNAL")) {
    define("MINIGAL_INTERNAL", true);
}

require("config.php");
ini_set("memory_limit",$config['memory_limit']);

function rotate_image($filename, $target) {
    // Rotate JPG pictures
    if (preg_match("/\.jpg$|\.jpeg$/i", $filename) && function_exists('exif_read_data') && function_exists('imagerotate')) {
        $exif = exif_read_data($filename);
        if (array_key_exists('IFD0', $exif)) {
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
            if ($degrees != 0)  return imagerotate($target, $degrees, 0);
        }
    }

return $target;
}

function create_thumb($filename, $extension, $outfile, $size = 1024, $keepratio = true) {
    global $config;
    // Define variables
    $target = rotate_image($filename);
    $xoord = 0;
    $yoord = 0;
    $height = $size;
    $width = $size;

    if ($config['caching'] && is_file($outfile)) {
        readfile($outfile);     //Use the cache
        return;
    }

    ob_start();

    if ( in_array($extension, $config['supported_video_types']) ) {
        // Video thumbnail
        passthru ("ffmpegthumbnailer -i " . escapeshellarg($filename) . " -o - -s " . escapeshellarg($size) . " -c jpeg -f" . ($keepratio? "" : " -a"));
    } else {
        // Image thumbnail
        list($width_orig, $height_orig) = GetImageSize($filename);

        if ($keepratio) {
            // Get new dimensions
            $ratio_orig = $width_orig/$height_orig;

            if ($width_orig > $height_orig) {
               $height = $width/$ratio_orig;
            } else {
               $width = $height*$ratio_orig;
            }
       } else {
            // square thumbnail
            if ($width_orig > $height_orig) { // If the width is greater than the height it’s a horizontal picture
                $xoord = ceil(($width_orig-$height_orig)/2);
                $width_orig = $height_orig;      // Then we read a square frame that  equals the width
            } else {
                $yoord = ceil(($height_orig-$width_orig)/2);
                $height_orig = $width_orig;
            }
        }

        if($keepratio && $size > $height_orig && $size > $width_orig) {
            readfile($filename);
            $outfile = null; //don't cache images that are equal to originals
        } else {
            // load source image
            if ($extension == "jpg" || $extension == "jpeg")
                $source = ImageCreateFromJPEG($filename);
            else if ($extension == "gif")
                $source = ImageCreateFromGIF($filename);
            else if ($extension == "png")
                $source = ImageCreateFromPNG($filename);

            $target = ImageCreatetruecolor($width,$height);
            imagecopyresampled($target,$source, 0,0, $xoord,$yoord, $width,$height, $width_orig,$height_orig);
            imagedestroy($source);

            if ($extension == "jpg" || $extension == "jpeg" || $extension == "png")
                ImageJPEG($target,null,90);
            else if ($extension == "gif")
                ImageGIF($target,null,90);
            imagedestroy($target);
        }
    }

    if($outfile)
        file_put_contents($outfile,ob_get_contents());

    ob_end_flush();
}


$_GET['filename'] = "./" . $_GET['filename'];
if($_GET['mode'] == 'thumb') {
  $size=$config['thumb_size'];
  $keepratio=false;
} else {
  $size=$config['small_size'];
  $keepratio=true;
}

// Display error image if file isn't found
if (preg_match("/\.\.\//i", $_GET['filename']) || !is_file($_GET['filename'])) {
    header('Content-type: image/jpeg');
    $errorimage = ImageCreateFromJPEG('images/questionmark.jpg');
    ImageJPEG($errorimage,null,90);
    imagedestroy($errorimage);
    exit;
}

// Display error image if file exists, but can't be opened
if (substr(decoct(fileperms($_GET['filename'])), -1, strlen(fileperms($_GET['filename']))) < 4 OR substr(decoct(fileperms($_GET['filename'])), -3,1) < 4) {
    header('Content-type: image/jpeg');
    $errorimage = ImageCreateFromJPEG('images/cannotopen.jpg');
    ImageJPEG($errorimage,null,90);
    imagedestroy($errorimage);
    exit;
}

$extension = strtolower(preg_replace('/^.*\./', '', $_GET['filename']));
if ( in_array($extension, $config['supported_image_types']) || in_array($extension, $config['supported_video_types']) ) {
    if ($extension == 'gif') {
        header('Content-type: image/gif');
        $cleanext = 'gif';
    } else {
        header('Content-type: image/jpeg');
        $cleanext = 'jpeg';
    }
} else {
    header('Content-type: image/jpeg');
    $errorimage = ImageCreateFromJPEG('images/cannotopen.jpg');
    ImageJPEG($errorimage,null,90);
    imagedestroy($errorimage);
    exit;
}

// Create paths for different picture versions
$thumbnail = null;

if($config['caching']) {
    $md5sum = md5($_GET['filename']);
    $thumbnail = $config['cache_path'] . "/" . $md5sum . "_" . $size . "_" . ($keepratio?"keepratio":"square") . "." . $extension;
    if(!file_exists($config['cache_path']))
        mkdir($config['cache_path']);
}

create_thumb($_GET['filename'], $extension, $thumbnail, $size, $keepratio);

?>
