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
*/
if(! defined("MINIGAL_INTERNAL")) {
    exit;
}

// EDIT SETTINGS BELOW TO CUSTOMIZE YOUR GALLERY
$config['thumbs_pr_page']         = "18"; //Number of thumbnails on a single page
$config['gallery_width']          = "900px"; //Gallery width. Eg: "500px" or "70%"
$config['backgroundcolor']        = "white"; //This provides a quick way to change your gallerys background to suit your website. Use either main colors like "black", "white", "yellow" etc. Or HEX colors, eg. "#AAAAAA"
$config['templatefile']           = "mano"; //Template filename (must be placed in 'templates' folder)
$config['title']                  = "My Gallery"; // Text to be displayed in browser titlebar
$config['author']                 = "Me :)";
$config['folder_color']           = "black"; // Color of folder icons: blue / black / vista / purple / green / grey
$config['sorting_folders']        = "name"; // Sort folders by: [name][date]
$config['sorting_files']          = "name"; // Sort files by: [name][date][size]
$config['sortdir_folders']        = "ASC"; // Sort direction of folders: [ASC][DESC]
$config['sortdir_files']          = "ASC"; // Sort direction of files: [ASC][DESC]
$config['i18n']                   = "en_US";

//ADVANCED SETTINGS
$config['thumb_size']             = 120; //Thumbnail height/width (square thumbs). Changing this will most likely require manual altering of the template file to make it look properly!
$config['cache_path']             = "/tmp/imagecache"; //Cache for resized images (thumbnails, previews). Must be writable by the httpd user.
$config['label_max_length']       = 30; //Maximum chars of a folder name that will be displayed on the folder thumbnail
$config['display_exif']           = 1;
$config['ffmpegthumbnailer']      = "/usr/bin/ffmpegthumbnailer";
$config['supported_image_types']  = array("jpg", "jpeg", "png", "gif"); //List of supported image extensions
$config['supported_video_types']  = array("mp4", "mts", "mov", "m4v", "m4a", "aiff", "avi", "caf", "dv", "qtz", "flv"); //List of supported video extensions
$config['memory_limit']           = "512M";
$config['check_update']           = true;
$config['check_update_url']       = "https://raw.githubusercontent.com/gentoomaniac/minigal-nano-ng/master/src/config.sample.php";
$config['debug']                  = false;

//THUMBNAIL CACHING
$config['caching']                = true;

//SMALL CACHE
$config['small_enabled']          = true;
$config['small_size']             = 1024; //Size (width) of the images displayed in the gallery. Images are scaled proportionally

$config['version']                = "0.3.5";

return 'MINIGAL_INCLUDE_OK';
?>
