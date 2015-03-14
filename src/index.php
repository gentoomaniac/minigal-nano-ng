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

// Do not edit below this section unless you know what you are doing!

if(!defined("MINIGAL_INTERNAL")) {
    define("MINIGAL_INTERNAL", true);
}
require("config.php");
if((include("i18n/".$config['i18n'].".php")) != "MINIGAL_INCLUDE_OK")
    die("Error: Could not include language file i18n/".$config['i18n'].".php");

//-----------------------
// Debug stuff
//-----------------------
if($config['debug']) {
    error_reporting(E_ALL);
    $mtime = microtime();
    $mtime = explode(" ",$mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;
} else {
    error_reporting(E_ERROR);
//  error_reporting(0);
}

ini_set("memory_limit",$config['memory_limit']);

//-----------------------
// DEFINE VARIABLES
//-----------------------
$page_navigation = "";
$breadcrumb_navigation = "";
$thumbnails = "";
$new = "";
$images = "";
$exif_data = "";
$messages = "";

//-----------------------
// PHP ENVIRONMENT CHECK
//-----------------------
if (!function_exists('exif_read_data') && $config['display_exif'] == 1) {
    $config['display_exif'] = 0;
    $messages = $i18n['error_no_exif_support'];
}

//-----------------------
// FUNCTIONS
//-----------------------
function padstring($name, $length) {
    global $config;
    if (!isset($length)) $length = $config['label_max_length'];
    if (mb_strlen($name) > $length) {
      return mb_substr($name,0,$length) . "...";
   } else return $name;
}
// ToDo: fix this function!
function getfirstImage($dirname) {
    global $config;
    $imageName = false;
    if($handle = opendir($dirname))
    {
        while(false !== ($file = readdir($handle)))
        {
            $extension = strtolower(preg_replace('/^.*\./', '', $file));
            if ($file[0] != '.' && in_array($extension, $config['supported_image_types'])) break;
        }
        $imageName = $file;
        closedir($handle);
    }
    return($imageName);
}
function readEXIF($file) {
        $exif_data = "";
        $exif_idf0 = exif_read_data ($file,'IFD0' ,0 );
        $emodel = $exif_idf0['Model'];

        $efocal = $exif_idf0['FocalLength'];
        list($x,$y) = split('/', $efocal);
        $efocal = round($x/$y,0);

        $exif_exif = exif_read_data ($file,'EXIF' ,0 );
        $eexposuretime = $exif_exif['ExposureTime'];

        $efnumber = $exif_exif['FNumber'];
        list($x,$y) = split('/', $efnumber);
        $efnumber = round($x/$y,0);

        $eiso = $exif_exif['ISOSpeedRatings'];

        $exif_date = exif_read_data ($file,'IFD0' ,0 );
        $edate = $exif_date['DateTime'];
        if (mb_strlen($emodel) > 0 OR mb_strlen($efocal) > 0 OR mb_strlen($eexposuretime) > 0 OR mb_strlen($efnumber) > 0 OR mb_strlen($eiso) > 0) $exif_data .= "::";
        if (mb_strlen($emodel) > 0) $exif_data .= "$emodel";
        if ($efocal > 0) $exif_data .= " | $efocal" . "mm";
        if (mb_strlen($eexposuretime) > 0) $exif_data .= " | $eexposuretime" . "s";
        if ($efnumber > 0) $exif_data .= " | f$efnumber";
        if (mb_strlen($eiso) > 0) $exif_data .= " | ISO $eiso";
        return($exif_data);
}
function checkpermissions($file) {
    global $messages;
    if (mb_substr(decoct(fileperms($file)), -1, mb_strlen(fileperms($file))) < 4 OR mb_substr(decoct(fileperms($file)), -3,1) < 4) $messages = $i18n['error_file_permissions'];
}

//-----------------------
// CHECK FOR NEW VERSION
//-----------------------
if (ini_get('allow_url_fopen') == "1" && $config['check_update']) {
    $file = @fopen ($config['check_update_url'], "r");
    $server_version = fgets ($file, 1024);
    if (mb_strlen($server_version) == 5 ) { //If string retrieved is exactly 5 chars then continue
        if (version_compare($server_version, $config['version'], '>')) $messages = sprintf($i18n['msg_update_available'], $server_version);
    }
    fclose($file);
}

mb_internal_encoding("UTF-8");
if (!defined("GALLERY_ROOT")) define("GALLERY_ROOT", "");
$thumbdir = rtrim('photos' . "/" .$_REQUEST["dir"],"/");
$thumbdir = str_replace("/..", "", $thumbdir); // Prevent looking at any up-level folders
$currentdir = GALLERY_ROOT . $thumbdir;

//-----------------------
// READ FILES AND FOLDERS
//-----------------------
$files = array();
$dirs = array();
if ($handle = opendir($currentdir))
{
    while (false !== ($file = readdir($handle)))
    {
        // 1. LOAD FOLDERS
        if (is_dir($currentdir . "/" . $file))
        {
            if ($file != "." && $file != ".." && mb_substr($file, 0, 1) != ".")
            {
                checkpermissions($currentdir . "/" . $file); // Check for correct file permission
                // Set thumbnail to folder.jpg if found:
                if (file_exists("$currentdir/" . $file . "/folder.jpg"))
                {
                    $dirs[] = array(
                        "name" => $file,
                        "date" => filemtime($currentdir . "/" . $file . "/folder.jpg"),
                        "html" => "<li><a href='?dir=" .ltrim($_GET['dir'] . "/" . $file, "/") . "'><em>" .
                                   padstring($file, $i18n['label_max_length']) . "</em><span></span><img src='" .
                                   GALLERY_ROOT . "getimage.php?filename=$currentdir/" . $file . "/folder.jpg&amp;size=" .
                                   $config['thumb_size']."&amp;format=square'  alt='" . $i18n['label_loading'] . "' /></a></li>");
                } else {
                    // Set thumbnail to first image found (if any):
                    $firstimage = getfirstImage("$currentdir/" . $file);
                    if ($firstimage != "") {
                        $dirs[] = array(
                            "name" => $file,
                            "date" => filemtime($currentdir . "/" . $file),
                            "html" => "<li><a href='?dir=" . ltrim($_GET['dir'] . "/" . $file, "/") . "'><em>" .
                                       padstring($file, $i18n['label_max_length']) . "</em><span></span><img src='" .
                                       GALLERY_ROOT . "getimage.php?filename=$thumbdir/" . $file . "/" . $firstimage .
                                       "&amp;size=".$config['thumb_size']."'  alt='".$i18n['label_loading']."' /></a></li>"
                        );
                    } else {
                    // If no folder.jpg or image is found, then display default icon:
                        $dirs[] = array(
                            "name" => $file,
                            "date" => filemtime($currentdir . "/" . $file),
                            "html" => "<li><a href='?dir=" . ltrim($_GET['dir'] . "/" . $file, "/") . "'><em>" .
                                       padstring($file) . "</em><span></span><img src='" . GALLERY_ROOT . "images/folder_" .
                                       mb_strtolower($config['folder_color']) . ".png' width='" . $config['thumb_size'] .
                                       "' height='" . $config['thumb_size'] . "' alt='" . $i18n['label_loading']."' /></a></li>"
                        );
                    }
                }
            }
        }

        // 2. LOAD CAPTIONS
        if (file_exists($currentdir ."/captions.txt"))
        {
            $file_handle = fopen($currentdir ."/captions.txt", "rb");
            while (!feof($file_handle) )
            {
                $line_of_text = fgets($file_handle);
                $parts = explode('/n', $line_of_text);
                foreach($parts as $img_capts)
                {
                    list($img_filename, $img_caption) = explode('|', $img_capts);
                    $img_captions[$img_filename] = $img_caption;
                }
            }
            fclose($file_handle);
        }

        // 3. LOAD FILES
        if ($file != "." && $file != ".." && $file != "folder.jpg" && mb_substr($file, 0, 1) != ".")
        {
            $extension = strtolower(preg_replace('/^.*\./', '', $file));
            if (in_array($extension, $config['supported_image_types']))
            {
                // JPG, GIF and PNG
                $img_captions[$file] .= "<a href=\"getimage.php?filename=" . $currentdir .
                                        "/" . $file . "&amp;size=".$config['small_size']."\">small</a>&nbsp;\n";
                $img_captions[$file] .= "<a href=\"" . $currentdir . "/" . $file . "\">original</a>\n";

                //Read EXIF
                if ($display_exif == 1)
                    $img_captions[$file] .= "<br />" .readEXIF($currentdir . "/" . $file);

                checkpermissions($currentdir . "/" . $file);
                $files[] = array (
                    "name" => $file,
                    "date" => filemtime($currentdir . "/" . $file),
                    "size" => filesize($currentdir . "/" . $file),
                    "html" => "<li><a href='getimage.php?filename=" . $currentdir . "/" . $file . "&amp;size=" .
                               $config['small_size'] . "' rel='lightbox[billeder]' title='" . $img_captions[$file] .
                               "'><span></span><img src='" . GALLERY_ROOT . "getimage.php?filename=" . $thumbdir .
                               "/" . $file . "&amp;size=" . $config['thumb_size'] . "&amp;format=square' alt='" .
                               $i18n['label_loading'] . "' /></a><em>" . padstring($file, $label_max_length) . "</em></li>"
                );

            } else if (in_array($extension, $config['supported_video_types'])) {
                // MP4
                $img_captions[$file] .= "<a href=\"" . $currentdir . "/" . $file . "\">original</a>\n";
                checkpermissions($currentdir . "/" . $file);
                $files[] = array (
                    "name" => $file,
                    "date" => filemtime($currentdir . "/" . $file),
                    "size" => filesize($currentdir . "/" . $file),
                    "html" => "<li><a href='" . $currentdir . "/" . $file . "' rel='lightbox[billeder]' title='" .
                               $img_captions[$file]."'><span></span><img src='" . GALLERY_ROOT . "getimage.php?filename=" .
                               $thumbdir . "/" . $file . "&amp;size=" . $config['thumb_size'] . "&amp;format=square' alt='" .
                               $i18n['label_loading'] . "' /></a><em>" . padstring($file, $label_max_length) . "</em></li>"
                );
            }
            // Other filetypes
            $extension = "";
            if (preg_match("/.pdf$/i", $file)) $extension = "PDF"; // PDF
            if (preg_match("/.zip$/i", $file)) $extension = "ZIP"; // ZIP archive
            if (preg_match("/.rar$|.r[0-9]{2,}/i", $file)) $extension = "RAR"; // RAR Archive
            if (preg_match("/.tar$/i", $file)) $extension = "TAR"; // TARball archive
            if (preg_match("/.gz$/i", $file)) $extension = "GZ"; // GZip archive
            if (preg_match("/.doc$|.docx$/i", $file)) $extension = "DOCX"; // Word
            if (preg_match("/.ppt$|.pptx$/i", $file)) $extension = "PPTX"; //Powerpoint
            if (preg_match("/.xls$|.xlsx$/i", $file)) $extension = "XLXS"; // Excel

            if ($extension != "") {
                $files[] = array (
                    "name" => $file,
                    "date" => filemtime($currentdir . "/" . $file),
                    "size" => filesize($currentdir . "/" . $file),
                    "html" => "<li><a href='" . $currentdir . "/" . $file . "' title='$file'><em-pdf>" .
                               padstring($file, 20) . "</em-pdf><span></span><img src='" . GALLERY_ROOT .
                               "images/filetype_" . $extension . ".png' width='" . $config['thumb_size'] .
                               "' height='$thumb_size' alt='$file' /></a></li>"
                );
            }
        }
    }
    closedir($handle);
} else die("ERROR: Could not open $currentdir for reading!");

//-----------------------
// SORT FILES AND FOLDERS
//-----------------------
if (sizeof($dirs) > 0)
{
    foreach ($dirs as $key => $row)
    {
        if($row["name"] == "") unset($dirs[$key]); //Delete empty array entries
        $name[$key] = mb_strtolower($row['name']);
        $date[$key] = mb_strtolower($row['date']);
    }
    if (mb_strtoupper($config['sortdir_folders']) == "DESC") array_multisort($$config['sorting_folders'], SORT_DESC, $name, SORT_DESC, $dirs);
    else array_multisort($$sorting_folders, SORT_ASC, $name, SORT_ASC, $dirs);
}
if (sizeof($files) > 0)
{
    foreach ($files as $key => $row)
    {
        if($row["name"] == "") unset($files[$key]); //Delete empty array entries
        $name[$key] = mb_strtolower($row['name']);
        $date[$key] = mb_strtolower($row['date']);
        $size[$key] = mb_strtolower($row['size']);
    }
    if (mb_strtoupper($config['sortdir_files']) == "DESC") array_multisort($$config['sorting_files'], SORT_DESC, $name, SORT_ASC, $files);
    else array_multisort($$config['sorting_files'], SORT_ASC, $name, SORT_ASC, $files);
}

//-----------------------
// OFFSET DETERMINATION
//-----------------------
    $offset_start = ($_GET["page"] * $config['thumbs_pr_page']) - $config['thumbs_pr_page'];
    if (!isset($_GET["page"])) $offset_start = 0;
    $offset_end = $offset_start + $config['thumbs_pr_page'];
    if ($offset_end > sizeof($dirs) + sizeof($files)) $offset_end = sizeof($dirs) + sizeof($files);

    if ($_GET["page"] == "all")
    {
        $offset_start = 0;
        $offset_end = sizeof($dirs) + sizeof($files);
    }

//-----------------------
// PAGE NAVIGATION
//-----------------------
if (!isset($_GET["page"])) $_GET["page"] = 1;
if (sizeof($dirs) + sizeof($files) > $config['thumbs_pr_page'])
{
    $page_navigation .= $i18n['label_page']." ";
    for ($i=1; $i <= ceil((sizeof($files) + sizeof($dirs)) / $config['thumbs_pr_page']); $i++)
    {
        if ($_GET["page"] == $i)
            $page_navigation .= "$i";
            else
                $page_navigation .= "<a href='?dir=" . $_GET["dir"] . "&amp;page=" . ($i) . "'>" . $i . "</a>";
        if ($i != ceil((sizeof($files) + sizeof($dirs)) / $config['thumbs_pr_page'])) $page_navigation .= " | ";
    }
    //Insert link to view all images
    if ($_GET["page"] == "all") $page_navigation .= " | " . $i18n['label_all'];
    else $page_navigation .= " | <a href='?dir=" . $_GET["dir"] . "&amp;page=all'>" . $i18n['label_all'] . "</a>";
}

//-----------------------
// BREADCRUMB NAVIGATION
//-----------------------
if ($_GET['dir'] != "")
{
    $breadcrumb_navigation .= "<a href='?dir='>" . $i18n['label_home'] . "</a> > ";
    $navitems = explode("/", $_REQUEST['dir']);
    for($i = 0; $i < sizeof($navitems); $i++)
    {
        if ($i == sizeof($navitems)-1) $breadcrumb_navigation .= $navitems[$i];
        else
        {
            $breadcrumb_navigation .= "<a href='?dir=";
            for ($x = 0; $x <= $i; $x++)
            {
                $breadcrumb_navigation .= $navitems[$x];
                if ($x < $i) $breadcrumb_navigation .= "/";
            }
            $breadcrumb_navigation .= "'>" . $navitems[$i] . "</a> > ";
        }
    }
} else $breadcrumb_navigation .= $i18n['label_home'];

//Include hidden links for all images BEFORE current page so lightbox is able to browse images on different pages
for ($y = 0; $y < $offset_start - sizeof($dirs); $y++)
{
    $breadcrumb_navigation .= "<a href='getimage.php?filename=" . $currentdir . "/" . $files[$y]["name"] . "&amp;size=" . $config['small_size'] . "' rel='lightbox[billeder]' class='hidden' title='" . $img_captions[$files[$y]["name"]] . "'></a>";
}

//-----------------------
// DISPLAY FOLDERS
//-----------------------
if (count($dirs) + count($files) == 0) {
    $thumbnails .= "<li>" . $i18n['label_noimages'] . "</li>"; //Display 'no images' text
    if($currentdir == "photos") $messages =  $i18n['msg_first_run'];
}
$offset_current = $offset_start;
for ($x = $offset_start; $x < sizeof($dirs) && $x < $offset_end; $x++)
{
    $offset_current++;
    $thumbnails .= $dirs[$x]["html"];
}

//-----------------------
// DISPLAY FILES
//-----------------------
for ($i = $offset_start - sizeof($dirs); $i < $offset_end && $offset_current < $offset_end; $i++)
{
    if ($i >= 0)
    {
        $offset_current++;
        $thumbnails .= $files[$i]["html"];
    }
}

//Include hidden links for all images AFTER current page so lightbox is able to browse images on different pages
for ($y = $i; $y < sizeof($files); $y++)
{
    $page_navigation .= "<a href='getimage.php?filename=" . $currentdir . "/" . $files[$y]["name"] . "&amp;size=" . $config['small_size'] . "' rel='lightbox[billeder]'  class='hidden' title='" . $img_captions[$files[$y]["name"]] . "'></a>";
}

//-----------------------
// OUTPUT MESSAGES
//-----------------------
if ($messages != "") {
$messages = "<div id=\"topbar\">" . $messages . " <a href=\"#\" onclick=\"document.getElementById('topbar').style.display = 'none';\";><img src=\"images/close.png\" /></a></div>";
}

//PROCESS TEMPLATE FILE
    if(GALLERY_ROOT != "") $templ = GALLERY_ROOT . "templates/integrate.html";
    else $templ = "templates/" . $config['templatefile'] . ".html";
    if(!$fd = fopen($templ, "r"))
    {
        echo "Template $templ not found!";
        exit();
    }
    else
    {
        $template = fread ($fd, filesize ($templ));
        fclose ($fd);
        $template = stripslashes($template);
        $template = preg_replace("/<% title %>/", $config['title'], $template);
        $template = preg_replace("/<% messages %>/", $messages, $template);
        $template = preg_replace("/<% author %>/", $config['author'], $template);
        $template = preg_replace("/<% gallery_root %>/", GALLERY_ROOT, $template);
        $template = preg_replace("/<% images %>/", "$images", $template);
        $template = preg_replace("/<% thumbnails %>/", "$thumbnails", $template);
        $template = preg_replace("/<% breadcrumb_navigation %>/", "$breadcrumb_navigation", $template);
        $template = preg_replace("/<% page_navigation %>/", "$page_navigation", $template);
        $template = preg_replace("/<% bgcolor %>/", $config['backgroundcolor'], $template);
        $template = preg_replace("/<% gallery_width %>/", $config['gallery_width'], $template);
        $template = preg_replace("/<% version %>/", $config['version'], $template);
        echo "$template";
    }

//-----------------------
//Debug stuff
//-----------------------
if($config['debug']) {
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
   $totaltime = ($endtime - $starttime);
   echo "This page was created in ".$totaltime." seconds";
}
?>
