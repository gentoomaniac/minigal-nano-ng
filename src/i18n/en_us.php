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

?>