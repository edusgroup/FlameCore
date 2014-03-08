<?php
use core\classes\DB\DB as DBCore;

$autoload = function ($pClassName) {
            $className = str_replace('\\', '/', $pClassName);
            if ( substr($className, 0, 4) == 'site'){
                //print \site\conf\DIR::SITE_CORE.substr($className, 5)."<br/>";
                //print $pClassName."\r\n <br/>";
                //var_dump( debug_backtrace() );
                // debug_print_backtrace();
                $classFileName = \site\conf\DIR::SITE_CORE.substr($className, 5) . '.php';
            }else{
                $classFileName =  \site\conf\DIR::CORE . $className . '.php';
            }
            include($classFileName);
        };

spl_autoload_register($autoload);

$shutdown = function() {
            $handle = DBCore::getHandle('site');
            if ($handle) {
                // TODO продумать как сделать универсально
                $handle->close();
            }
        };

register_shutdown_function($shutdown);

function redirect(string $pURL){
        header("HTTP/1.1 301 Moved Permanently");
        header('Location: '.$pURL);
        exit;
}