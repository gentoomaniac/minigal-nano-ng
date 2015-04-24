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

//LANGUAGE STRINGS
$i18n['label_home']             = "Home"; //Name of home link in breadcrumb navigation
$i18n['label_new']              = "New"; //Text to display for new images. Use with $display_new variable
$i18n['label_page']             = "Page"; //Text used for page navigation
$i18n['label_all']              = "All"; //Text used for link to display all images in one page
$i18n['label_noimages']         = "No images"; //Empty folder text
$i18n['label_loading']          = "Loading..."; //Thumbnail loading text

$i18n['msg_update_available']   = "MiniGal Nano NG %s is available!";
$i18n['msg_first_run']          = "It looks like you are on a fresh installation. Please run the <a href='system_check.php'>system check</a>";

$i18n['error_loading_internal'] = "This file should not be loaded directly!";
$i18n['error_no_exif_support']  = "Error: PHP EXIF is not available. Set &#36;display_exif = 0; in config.php to remove this message";
$i18n['error_file_permissions'] = "At least one file or folder has wrong permissions";

$i18n['syscheck_page_title']    = "MiniGal Nano NG system check";
$i18n['syscheck_php_title']     = "PHP version";
$i18n['syscheck_php_desc']      = '<a href="http://www.php.net/" target="_blank">PHP</a> scripting language version 4.0 or greater is needed';
$i18n['syscheck_gd_title']      = 'GD library support';
$i18n['syscheck_gd_desc']       = '<a href="http://www.boutell.com/gd/" target="_blank">GD image manipulation</a> library is used to create thumbnails. Bundled since PHP 4.3';
$i18n['syscheck_exif_title']    = 'EXIF support';
$i18n['syscheck_exif_desc']     = 'Ability to extract and display <a href="http://en.wikipedia.org/wiki/Exif" target="_blank">EXIF information</a>. The script will work without it, but not display image information';
$i18n['syscheck_videothumb_title'] = 'Video thumbnail support';
$i18n['syscheck_videothumb_desc']  = '<a href="https://code.google.com/p/ffmpegthumbnailer/" target="_blank">ffmpgthumbnailer</a> is used to create thumbnails of supported video files.';
$i18n['syscheck_conf_title']    = 'Configuration';
$i18n['syscheck_conf_desc']     = 'Check if configuration file config.php exists and the config file is includable.';
$i18n['syscheck_mem_title']     = 'PHP memory limit';
$i18n['syscheck_mem_desc']      = 'Memory is needed to create thumbnails. Bigger images uses more memory';
$i18n['syscheck_versioncheck_title'] = 'New version check';
$i18n['syscheck_versioncheck_desc']  = 'The ability to check for new version and display this automatically. The script will work without it';

return "MINIGAL_INCLUDE_OK";
?>
