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
$i18n['label_home']             = "Альбомы"; //Name of home link in breadcrumb navigation
$i18n['label_new']              = "New"; //Text to display for new images. Use with $display_new variable
$i18n['label_page']             = "Страница"; //Text used for page navigation
$i18n['label_all']              = "Всё"; //Text used for link to display all images in one page
$i18n['label_noimages']         = "В этом альбоме нет фотографий"; //Empty folder text
$i18n['label_loading']          = "Загрузка..."; //Thumbnail loading text

$i18n['msg_update_available']   = "Доступно обновление MiniGal Nano NG %s!";
$i18n['msg_first_run']          = "Судя по всему, Вы только что установили MiniGal Nano NG. Пожалуйста, запустите <a href='system_check.php'>проверку системной конфигурации</a>";

$i18n['error_loading_internal'] = "Этот файл не предполагается вызывать непосредственно!";
$i18n['error_no_exif_support']  = "Error: Расширение PHP EXIF не доступно. Установите &#36;display_exif = 0; в config.php, чтобы убрать это сообщение";
$i18n['error_file_permissions'] = "Как минимум у одного файла или директории недостаточные права доступа";

$i18n['syscheck_page_title']    = "MiniGal Nano NG - Проверка системы";
$i18n['syscheck_php_title']     = "Версия PHP";
$i18n['syscheck_php_desc']      = 'Необходим скриптовый язык программирования <a href="http://www.php.net/" target="_blank">PHP</a> версии 4.0 или выше';
$i18n['syscheck_gd_title']      = 'Поддержка библиотеки GD';
$i18n['syscheck_gd_desc']       = '<a href="http://www.boutell.com/gd/" target="_blank">Библиотека GD</a> используется для создания миниатюр. Встроена в PHP начиная с версии 4.3';
$i18n['syscheck_exif_title']    = 'Поддержка EXIF';
$i18n['syscheck_exif_desc']     = 'Возможность получения и отображения информации <a href="http://en.wikipedia.org/wiki/Exif" target="_blank">EXIF</a>. Не обязательно';
$i18n['syscheck_videothumb_title'] = 'Поддервка миниатюр видеофайлов';
$i18n['syscheck_videothumb_desc']  = '<a href="https://code.google.com/p/ffmpegthumbnailer/" target="_blank">ffmpgthumbnailer</a> используется для создания миниатюр видеофайлов';
$i18n['syscheck_conf_title']    = 'Конфигурация';
$i18n['syscheck_conf_desc']     = 'Проверка, что файл конфигурации config.php существует и доступен для подключения';
$i18n['syscheck_mem_title']     = 'Ограничение памяти PHP';
$i18n['syscheck_mem_desc']      = 'Память нужна для построения миниатюр. Чем больше изображение, тем больше памяти нужно';
$i18n['syscheck_versioncheck_title'] = 'Проверка обновлений';
$i18n['syscheck_versioncheck_desc']  = 'Возможность автоматической проверки новых версий MiniGal Nano NG отображения уведомления. Не обязательно';
return "MINIGAL_INCLUDE_OK";
?>
