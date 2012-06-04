<?php

// Conf
use \DIR;
use site\conf\SITE as SITE_CONF;
use site\conf\DIR as DIR_CONF;

// Engine
use core\classes\DB\DB as DBCore;

/**
 * Возвращает путь к файлу где храниться класс $pClassName
 * @param string $pClassName имя класса
 * @return string
 */
function getClassPath($pClassName) {
    $className = str_replace('\\', '/', $pClassName);
    $codeNs = substr($className, 0, 4);
    if ( $codeNs == 'site') {
        return DIR_CONF::SITE_CORE . substr($className, 4) . '.php';
    }
    return DIR::CORE . $className . '.php';
}

/**
 * Проверяем существует ли класс в библиотеках системы. Без подгрузки класса.<br/>
 * <b>Внимание:</b> проверяет классы написанные в системе.<br/>
 * Для проверки существования используйте class_exists($className), но эта функция
 * автоматически стартуе autoload классов
 * @param string $pClassName имя класса
 * @return boolean
 */
function isClassExists($pClassName) {
    $classFileName = getClassPath($pClassName);
    return is_readable($classFileName);
}

$autoload = function ($pClassName) {
    $classFileName = getClassPath($pClassName);
    if ( !is_readable($classFileName)){
        throw new \Exception('Autoload: Not found '.$classFileName, 23);
    }
    include($classFileName);
    //print $p_class_name.'  Use: '. memory_get_usage().' Max:'.memory_get_peak_usage()."<br/>";
};
spl_autoload_register($autoload);

$shutdown = function() {
    $handle = DBCore::getHandle('site');
    if ($handle) {
        // TODO продумать как сделать универсально
        $handle->close();
    }
    $handle = DBCore::getHandle('admin');
    if ($handle) {
        // TODO продумать как сделать универсально
        $handle->close();
    }
};

register_shutdown_function($shutdown);
?>