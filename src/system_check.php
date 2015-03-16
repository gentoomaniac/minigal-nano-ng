<?php

if(!defined("MINIGAL_INTERNAL")) {
    define("MINIGAL_INTERNAL", true);
}

$config_exists = ((include "config.php") == 'OK');

require("i18n/en_US.php");
if (array_key_exists('i18n', $config))
    if((include("i18n/".$config['i18n'].".php")) != "MINIGAL_INCLUDE_OK")
        echo "Error: Could not include language file i18n/".$config['i18n'].".php";

$exif = "No";
$gd = "No";
$update = "No";
if (array_key_exists("memory_limit", $config)) ini_set("memory_limit",$config['memory_limit']);
if (function_exists('exif_read_data')) $exif = "Yes";
if (extension_loaded('gd') && function_exists('gd_info')) $gd = "Yes";
if (ini_get("allow_url_fopen") == 1) $update = "Yes";

function check_ffmpegthumbnailer() {
    global $config;
    if(!file_exists($config['ffmpegthumbnailer'])) {
        return "No";
    }
    $output = exec(escapeshellarg($config['ffmpegthumbnailer']) . " -v");
    return explode(" ",$output)[2];
}
$thumbnailer_version = check_ffmpegthumbnailer();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex, nofollow">
<title><?php echo $i18n['syscheck_page_title']; ?></title>
<style type="text/css">
body {
    background-color: #daddd8;
    font: 12px Arial, Tahoma, "Times New Roman", serif;
}
h1 {
    font-size: 30px;
    margin: 20px 0 5px 0;
    letter-spacing: -2px;
}
div {
    line-height: 20px;
}
.left {
    width: 300px;
    display: inline-table;
    background-color: #fdffbe;
    padding: 2px;
}
.middle-neutral {
    font-weight: bold;
    text-align: center;
    width: 100px;
    display: inline-table;
    background-color: #fdffbe;
    padding: 2px;
}
.middle-no {
    font-weight: bold;
    text-align: center;
    width: 100px;
    display: inline-table;
    background-color: #ff8181;
    padding: 2px;
}
.middle-yes {
    font-weight: bold;
    text-align: center;
    width: 100px;
    display: inline-table;
    background-color: #98ffad;
    padding: 2px;
}
.right {
    width: 600px;
    display: inline-table;
    background-color: #eaf1ea;
    padding: 2px;
}
</style>
<body>
<h1><?php echo $i18n['syscheck_page_title']; ?></h1>
<div class="left">
<?php echo $i18n['syscheck_php_title']; ?>
</div>
<div class="<?php if(version_compare(phpversion(), "4.0", '>')) echo 'middle-yes'; else echo 'middle-no' ?>">
    <?php echo phpversion(); ?>
</div>
<div class="right">
    <?php echo $i18n['syscheck_php_desc']; ?>
</div>
<br />

<div class="left">
    <?php echo $i18n['syscheck_gd_title']; ?>
</div>
<div class="<?php if($gd == "Yes") echo 'middle-yes'; else echo 'middle-no' ?>">
    <?php echo $gd; ?>
</div>
<div class="right">
    <?php echo $i18n['syscheck_gd_desc']; ?>
</div>
<br />

<div class="left">
    <?php echo $i18n['syscheck_exif_title']; ?>
</div>
<div  class="<?php if($exif == "Yes") echo 'middle-yes'; else echo 'middle-neutral' ?>">
    <?php echo $exif; ?>
</div>
<div class="right">
    <?php echo $i18n['syscheck_exif_desc']; ?>
</div>
<br />

<div class="left">
<?php echo $i18n['syscheck_videothumb_title']; ?>
</div>
<div class="<?php if($thumbnailer_version == "No") echo 'middle-no'; else echo 'middle-yes' ?>">
    <?php echo $thumbnailer_version; ?>
</div>
<div class="right">
    <?php echo $i18n['syscheck_videothumb_desc']; ?>
</div>
<br />

<div class="left">
    <?php echo $i18n['syscheck_conf_title']; ?>
</div>
<div class="<?php if($config_exists) echo 'middle-yes'; else echo 'middle-no' ?>">
    <?php clearstatcache(null, "config.php");echo decoct( fileperms("config.php") & 0777 ); ?>
</div>
<div class="right">
    <?php echo $i18n['syscheck_conf_desc']; ?>
</div>
<br />

<div class="left">
    <?php echo $i18n['syscheck_mem_title']; ?>
</div>
<div class="middle-neutral">
    <?php echo ini_get("memory_limit"); ?>
</div>
<div class="right">
    <?php echo $i18n['syscheck_mem_desc']; ?>
</div>
<br />

<div class="left">
    <?php echo $i18n['syscheck_versioncheck_title']; ?>
</div>
<div class="middle-neutral">
    <?php echo $update ?>
</div>
<div class="right">
    <?php echo $i18n['syscheck_versioncheck_desc']; ?>
</div>
<br /><br />
<!--<a href="http://www.minigal.dk/minigal-nano.html" target="_blank">Support website</a>
| <a href="http://www.minigal.dk/forum" target="_blank">Support forum</a> --!>
</body>
</html>
