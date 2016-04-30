<?
use core\comp\spl\form\action\subscribe;
use core\classes\request;

// Conf
use \DIR as DIR_ADMIN;
use \site\conf\DIR as DIR_SITE;
use core\classes\DB\DB as DBCore;

// Config DIR
include '../../../../../admin/conf/DIR.php';
include DIR_ADMIN::CORE.'site/function/autoload.php';
include DIR_ADMIN::CORE.'core/function/errorHandler.php';

$httpHost = str_replace('.lo', '.ru', $_SERVER['HTTP_HOST']);
if (!include DIR_ADMIN::SITE_CORE . $httpHost . '/conf/DIR.php') {
    die('Conf file ' . $_SERVER['HTTP_HOST'] . ' not found');
}
include DIR_ADMIN::CORE . 'core/classes/DB/adapter/mysql/adapter.php';
// Add DB conf param
DBCore::addParam('site', \site\conf\DB::$conf);


$subscribe = new subscribe();
$subscribe->run(null, true);

$redirectUrl = request::post('rurl');
header('Location: '.$redirectUrl);
