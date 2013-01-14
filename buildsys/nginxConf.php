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
    use core\classes\webserver\nginx;

    // ORM
    use ORM\tree\routeTree;

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

    $siteName = request::get('siteName');

    $isDirNotExist = !$siteName || !is_dir(SITE_CORE . $siteName);
    if ($isDirNotExist) {
        // TODO: Сделать что бы нормально выдавало ошибку в браузере
        //print 'Error: Site Name not found'.PHP_EOL.' OR Dir(' . htmlspecialchars(SITE_CORE . $siteName) . ') not found'.PHP_EOL;
        echo 'Use php nginxConf.php siteName={sitename}'.PHP_EOL;
        exit;
    }

    include DIR::SITE_CORE . $siteName . '/conf/SITE.php';
    include DIR::SITE_CORE . $siteName . '/conf/DIR.php';
    include DIR::SITE_CORE . $siteName . '/conf/DB.php';

    DBCore::addParam('site', \site\conf\DB::$conf);

    nginx::createConf(new routeTree());

//print "\n<br style='clear:both'/>Use:".''.memory_get_usage().'<br/>Max:'.memory_get_peak_usage().'<br/>';