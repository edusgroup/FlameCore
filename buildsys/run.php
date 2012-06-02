<?php

// Conf
use \SITE as CONF;
use \DB as DB;
use \DIR as DIR;
// Const
use admin\library\classes\constant;
// Core
use core\classes\console\request;
use core\classes\DB\DB as DBCore;

// Грузим конфиги админки
define('DIR_CONF', './../engine/admin/');

include(DIR_CONF . 'conf/DIR.php');
include(DIR_CONF . 'conf/SITE.php');
include(DIR_CONF . 'conf/CONSTANT.php');

include DIR::CORE . 'admin/library/function/autoload.php';
// Костыль для проверки скалярных типо данных в параметрах функции. В PHP 5.4 пофиксят
include DIR::CORE . 'core/function/errorHandler.php';
// Подгрузка драйвера БД
include DIR::CORE . 'core/classes/DB/adapter/' . CONF::DB_ADAPTER . '/adapter.php';
umask(0002);

request::init($argv);

define('SITE_CORE', './../../SiteCoreFlame/');

$siteName = request::get('siteName');;
$isDirNotExist = !$siteName || !is_dir(SITE_CORE.$siteName);
if ( $isDirNotExist ){
    // TODO: Сделать что бы нормально выдавало ошибку в браузере
    print 'Error: siteName not exists';
    exit;
}

include DIR::SITE_CORE.$siteName.'/conf/SITE.php';
include DIR::SITE_CORE.$siteName.'/conf/DIR.php';
include DIR::SITE_CORE.$siteName.'/conf/DB.php';

DBCore::addParam('site', \site\conf\DB::$conf);

$cmd = request::get('cmd');
$method = request::get('method');
if ( !$cmd || !$method ){
die("Use paramentr
cmd=event method=run
cmd=log method=run
");
}
try {
    $className = 'buildsys\library\mvc\\'.$cmd.'\\'.$cmd;
    if (!class_exists($className)){
        throw new \Exception('Controller: "'.$className.'" not found', 26);
    }
    $controller = new $className;
    $method = $method ?: 'run';
    if (!method_exists($controller, $method)){
        throw new \Exception('Method: "'.$method.'" not found', 26);
    }
    //echo "CMD: $className->$method()".PHP_EOL;
    $controller->{$method}();
}catch (\Exception $e) {
    print 'Exception: '. $e->getMessage().PHP_EOL;
    echo nl2br( $e->getTraceAsString() );
    
    exit;
     
}

//print "\n<br style='clear:both'/>Use:".''.memory_get_usage().'<br/>Max:'.memory_get_peak_usage().'<br/>';
?>