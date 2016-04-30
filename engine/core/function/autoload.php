<?php
spl_autoload_register(function ($pClassName) {
    $className = str_replace('\\', '/', $pClassName);
    if ( substr($className, 0, 4) == 'site'){
        $classFileName = DIR::CORE . substr($className, 4) . '.php';
    }else{
        $classFileName = DIR::CORE . $className . '.php';
    }

    if ( !is_file($classFileName)){
        return false;
    }

    include($classFileName);
});
