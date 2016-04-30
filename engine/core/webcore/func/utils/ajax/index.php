<?php
// Core
use core\classes\request;
use core\classes\dbus;
use core\classes\DB\DB as DBCore;

// ORM
use ORM\tree\compContTree;

// Conf
use \DIR as DIR_ADMIN;
use \site\conf\DIR as DIR_SITE;

use core\classes\validation\word;

// Config DIR
include '../../../../../admin/conf/DIR.php';
$httpHost = str_replace('.lo', '.ru', $_SERVER['HTTP_HOST']);
if (!include DIR_ADMIN::SITE_CORE . $httpHost . '/conf/DIR.php') {
    die('Conf file ' . $_SERVER['HTTP_HOST'] . ' not found');
}

//include
include DIR::CORE . 'site/function/autoload.php';

include DIR::CORE . 'core/function/errorHandler.php';
include DIR::CORE . 'core/classes/DB/adapter/mysql/adapter.php';
// Add DB conf param
DBCore::addParam('site', \site\conf\DB::$conf);

session_start();

$userData = isset($_SESSION['userData']) ? $_SESSION['userData'] : null;

$fileName = request::get('name');
if (!word::isLatin($fileName)) {
    die('Bad ajax script name');
}


$file = DIR_SITE::APP_DATA . 'utils/ajax/' . $fileName . '.php';

if (!@include($file)) {
    die('Ajax script not found '.$file);
}
