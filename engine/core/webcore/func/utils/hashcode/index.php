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
use \site\conf\SITE as SITE_SITE;
use \core\classes\hashkey;


// Config DIR
include '../../../../../admin/conf/DIR.php';
$httpHost = str_replace('.lo', '.ru', $_SERVER['HTTP_HOST']);
if (!include DIR_ADMIN::SITE_CORE . $httpHost . '/conf/DIR.php') {
    die('Conf file dir ' . $_SERVER['HTTP_HOST'] . ' not found');
}

if (!include DIR_ADMIN::SITE_CORE . $httpHost . '/conf/SITE.php') {
    die('Conf file site ' . $_SERVER['HTTP_HOST'] . ' not found');
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

//include
include DIR::CORE . 'site/function/autoload.php';
include DIR::CORE . 'core/function/errorHandler.php';

header('Content-Type:application/json');
echo '{"key":"'.hashkey::checkHashKey(SITE_SITE::SHOP_SKEY).'"}';