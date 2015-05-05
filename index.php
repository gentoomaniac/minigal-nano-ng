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
if (! ((include("config.php")) == 'MINIGAL_INCLUDE_OK') ) {
    header("Location: system_check.php"); /* Redirect browser */
    exit();
}
require("i18n/en_US.php");
if (array_key_exists('i18n', $config))
    if((include("i18n/".$config['i18n'].".php")) != "MINIGAL_INCLUDE_OK")
        echo "Error: Could not include language file i18n/".$config['i18n'].".php";

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
    $server_version = preg_grep('/^\s*\$config\s*\[\s*\'version\'\s*\]\s*=\s*".*";\s*$/', file($config['check_update_url']));
    if(count($server_version)==1)
    {
        $server_version=preg_replace('/^\s*\$config\s*\[\s*\'version\'\s*\]\s*=\s*"(.*)";\s*$/', '\\1', implode($server_version));
        if (mb_strlen($server_version) == 5 ) { //If string retrieved is exactly 5 chars then continue
            if (version_compare($server_version, $config['version'], '>')) $messages = sprintf($i18n['msg_update_available'], $server_version);
        }
    }
}

mb_internal_encoding("UTF-8");

if (!defined("GALLERY_ROOT"))
	define("GALLERY_ROOT", "");
else
	$integrate=true;

if($_REQUEST["rewrite"]) {
	if(substr($_SERVER['PHP_SELF'], -strlen("index.php"))==="index.php")
		$uri_prefix=substr($_SERVER['PHP_SELF'], 0, -strlen("index.php")) . GALLERY_ROOT;
	else
		$uri_prefix=$_SERVER['PHP_SELF'] . GALLERY_ROOT;
} else
	$uri_prefix=GALLERY_ROOT;

$reqdir = str_replace("../", "", rtrim($_REQUEST["dir"]) . "/"); // Prevent looking at any up-level folders
if (substr($reqdir, -2)=="//") $reqdir=substr($reqdir, 0, -1);
if ($reqdir == "/") $reqdir = "";
$currentdir = GALLERY_ROOT . "photos/" . $reqdir;

//-----------------------
// READ FILES AND FOLDERS
//-----------------------
$files = array();
$dirs = array();
if ($handle = opendir($currentdir))
{
    date_default_timezone_set("UTC");
    $dirtimestamp=max(filemtime($currentdir), filemtime("./index.php"), filemtime("./config.php"), filemtime($integrate ? GALLERY_ROOT . "templates/integrate.html" : "./templates/" . $config['templatefile'] . ".html"));
    $lastmodified=gmdate("D, d M Y H:i:s \G\M\T", $dirtimestamp);
    $IfModifiedSince = 0;
    if (isset($_ENV['HTTP_IF_MODIFIED_SINCE']))
        $IfModifiedSince = strtotime(substr($_ENV['HTTP_IF_MODIFIED_SINCE'], 5));
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
        $IfModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
    if ($IfModifiedSince && $IfModifiedSince >= $dirtimestamp) {
        header($_SERVER['SERVER_PROTOCOL'] . " 304 Not Modified");
        header("Last-Modified: " . $lastmodified);
        exit;
    }
    header("Cache-Control: public, must-revalidate");
    header("Vary: Last-Modified");
    header("Last-Modified: " . $lastmodified);

    while (false !== ($file = readdir($handle)))
    {
        if (mb_substr($file, 0, 1) == "." && $file != ".captions.txt")
            continue;

        // 1. LOAD FOLDERS
        if (is_dir($currentdir . "/" . $file))
        {

            checkpermissions($currentdir . "/" . $file); // Check for correct file permission

            if ($_REQUEST["rewrite"]) {
                $thumburl = $uri_prefix . "thumb/" . str_replace("%2F", "/", rawurlencode($reqdir . $file));
                $origurl = rawurlencode($file) . "/";
            } else {
                $thumburl = $uri_prefix . "getimage.php?filename=" . str_replace("%2F", "/", rawurlencode($currentdir . $file)) . "&amp;mode=thumb";
                $origurl = $uri_prefix . "?dir=" . str_replace("%2F", "/", rawurlencode($reqdir . $file)) . "/";
            }

            $dirs[] = array(
                "name" => $file,
                "date" => filemtime($currentdir . "/" . $file),
                "html" => "<li><a href='" . $origurl . "'><em>" . padstring($file, $i18n['label_max_length']) .
                           "</em><span></span><img src='" . $thumburl . "'  alt='" . $i18n['label_loading'] . "' /></a></li>\n");

            continue;
        }

        // 2. LOAD CAPTIONS
        if ($file == ".captions.txt")
        {
            $file_handle = fopen($currentdir . ".captions.txt", "rb");
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
            continue;
        }

        // 3. LOAD FILES
        $extension = strtolower(preg_replace('/^.*\./', '', $file));
        if ($_REQUEST["rewrite"]) {
            $smallurl = $uri_prefix . "small/" . str_replace("%2F", "/", rawurlencode($reqdir . $file));
            $thumburl = $uri_prefix . "thumb/" . str_replace("%2F", "/", rawurlencode($reqdir . $file));
            $origurl = rawurlencode($file);
        } else {
            $smallurl = $uri_prefix . "getimage.php?filename=" . str_replace("%2F", "/", rawurlencode($currentdir . $file)) . "&amp;mode=small";
            $thumburl = $uri_prefix . "getimage.php?filename=" . str_replace("%2F", "/", rawurlencode($currentdir . $file)) . "&amp;mode=thumb";
            $origurl = str_replace("%2F", "/", rawurlencode($currentdir . $file));
        }

        if (in_array($extension, $config['supported_image_types']))
        {
            // JPG, GIF and PNG
            $img_captions[$file] .= "<a href=\"" . $smallurl . "\">small</a>&nbsp;\n";
            $img_captions[$file] .= "<a href=\"" . $origurl . "\">original</a>\n";

            //Read EXIF
            if ($config['display_exif'] == 1)
                $img_captions[$file] .= readEXIF($currentdir . "/" . $file);

            checkpermissions($currentdir . "/" . $file);
            $files[] = array (
                "name" => $file,
                "date" => filemtime($currentdir . "/" . $file),
                "size" => filesize($currentdir . "/" . $file),
                "html" => "<li><a href='" . $smallurl . "' rel='lightbox[billeder]' title='" . $img_captions[$file] .
                           "'><span></span><img src='" . $thumburl . "' alt='" . $i18n['label_loading'] . "' /></a><em>" .
                           padstring($file, $label_max_length) . "</em></li>\n"
            );
            continue;
        }

        if (in_array($extension, $config['supported_video_types'])) {
            // MP4
            $img_captions[$file] .= "<a href=\"" . $origurl . "\">original</a>\n";
            checkpermissions($currentdir . "/" . $file);
            $files[] = array (
                "name" => $file,
                "date" => filemtime($currentdir . "/" . $file),
                "size" => filesize($currentdir . "/" . $file),
                "html" => "<li><a href='" . $origurl . "' rel='lightbox[billeder]' title='" . $img_captions[$file] .
                           "'><span></span><img src='" . $thumburl . "' alt='" . $i18n['label_loading'] . "' /></a><em>" .
                           padstring($file, $label_max_length) . "</em></li>\n"
            );
            continue;
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
                "html" => "<li><a href='" . $origurl . "' title='$file'><em-pdf>" .
                           padstring($file, 20) . "</em-pdf><span></span><img src='" . $uri_prefix .
                           "images/filetype_" . $extension . ".png' width='" . $config['thumb_size'] .
                           "' height='$thumb_size' alt='$file' /></a></li>\n"
            );
        }
    }
    closedir($handle);
} else die("ERROR: Could not open $currentdir for reading!\n$php_errormsg");

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
    else array_multisort($$config['sorting_folders'], SORT_ASC, $name, SORT_ASC, $dirs);
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
        else if ($_REQUEST["rewrite"])
            $page_navigation .= "<a href='?page=" . ($i) . "'>" . $i . "</a>";
        else
            $page_navigation .= "<a href='?dir=" . $_GET["dir"] . "&amp;page=" . ($i) . "'>" . $i . "</a>";
        if ($i != ceil((sizeof($files) + sizeof($dirs)) / $config['thumbs_pr_page'])) $page_navigation .= " | ";
    }
    //Insert link to view all images
    if ($_GET["page"] == "all")
        $page_navigation .= " | " . $i18n['label_all'];
    else if($_REQUEST["rewrite"])
        $page_navigation .= " | <a href='?page=all'>" . $i18n['label_all'] . "</a>";
    else
        $page_navigation .= " | <a href='?dir=" . $_GET["dir"] . "&amp;page=all'>" . $i18n['label_all'] . "</a>";
}

//-----------------------
// BREADCRUMB NAVIGATION
//-----------------------
if ($_GET['dir'] != "")
{
    if($_REQUEST["rewrite"])
        $breadcrumb_navigation .= "<a href='" . $uri_prefix . "photos/'>" . $i18n['label_home'] . "</a> > ";
    else
        $breadcrumb_navigation .= "<a href='" . $uri_prefix . "?dir='>" . $i18n['label_home'] . "</a> > ";
    $navitems = explode("/", substr($reqdir, -1)=="/"? substr($reqdir, 0, -1) : $reqdir);
    for($i = 0; $i < sizeof($navitems); $i++)
    {
        if ($i == sizeof($navitems)-1) $breadcrumb_navigation .= $navitems[$i];
        else
        {
            if($_REQUEST["rewrite"])
                $breadcrumb_navigation .= "<a href='" . $uri_prefix . "photos/";
            else
                $breadcrumb_navigation .= "<a href='" . $uri_prefix . "?dir=";
            for ($x = 0; $x <= $i; $x++)
            {
                $breadcrumb_navigation .= rawurlencode($navitems[$x]);
                if ($x < $i || $_REQUEST["rewrite"]) $breadcrumb_navigation .= "/";
            }
            $breadcrumb_navigation .= "'>" . $navitems[$i] . "</a> > ";
        }
    }
} else $breadcrumb_navigation .= $i18n['label_home'];

//Include hidden links for all images BEFORE current page so lightbox is able to browse images on different pages
for ($y = 0; $y < $offset_start - sizeof($dirs); $y++)
{
    $extension = strtolower(preg_replace('/^.*\./', '', $files[$y]["name"]));
    if (in_array($extension, $config['supported_image_types']))
        if($_REQUEST["rewrite"])
            $smallurl = $uri_prefix . "small/" . str_replace("%2F", "/", rawurlencode($reqdir . $files[$y]["name"]));
        else
            $smallurl = $uri_prefix . "getimage.php?filename=" . str_replace("%2F", "/", rawurlencode($currentdir . $files[$y]["name"])) . "&amp;mode=small";
    else if (in_array($extension, $config['supported_video_types']))
        if($_REQUEST["rewrite"])
            $smallurl = rawurlencode($files[$y]["name"]);
        else
            $smallurl = str_replace("%2F", "/", rawurlencode($currentdir . $files[$y]["name"]));
    else
        continue;

    $breadcrumb_navigation .= "<a href='" . $smallurl . "' rel='lightbox[billeder]' class='hidden' title='" . $img_captions[$files[$y]["name"]] . "'></a>";
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
    $extension = strtolower(preg_replace('/^.*\./', '', $files[$y]["name"]));
    if (in_array($extension, $config['supported_image_types']))
        if($_REQUEST["rewrite"])
            $smallurl = $uri_prefix . "small/" . str_replace("%2F", "/", rawurlencode($reqdir . $files[$y]["name"]));
        else
            $smallurl = $uri_prefix . "getimage.php?filename=" . str_replace("%2F", "/", rawurlencode($currentdir . $files[$y]["name"])) . "&amp;mode=small";
    else if (in_array($extension, $config['supported_video_types']))
        if($_REQUEST["rewrite"])
            $smallurl = rawurlencode($files[$y]["name"]);
        else
            $smallurl = str_replace("%2F", "/", rawurlencode($currentdir . $files[$y]["name"]));
    else
        continue;

    $page_navigation .= "<a href='" . $smallurl . "' rel='lightbox[billeder]'  class='hidden' title='" . $img_captions[$files[$y]["name"]] . "'></a>";
}

//-----------------------
// OUTPUT MESSAGES
//-----------------------
if ($messages != "") {
$messages = "<div id=\"topbar\">" . $messages . " <a href=\"#\" onclick=\"document.getElementById('topbar').style.display = 'none';\";><img src=\"images/close.png\" /></a></div>";
}

//PROCESS TEMPLATE FILE
    if($integrate) $templ = GALLERY_ROOT . "templates/integrate.html";
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
        $template = preg_replace("/<% gallery_root %>/", $uri_prefix, $template);
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
