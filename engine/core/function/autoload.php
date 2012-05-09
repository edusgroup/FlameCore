<?php

$autoload = function ($pClassName) {
            $className = str_replace('\\', '/', $pClassName);
            if ( substr($className, 0, 4) == 'site'){
              return \site\conf\DIR::SITE_CORE.substr($className, 4) . '.php';
            } 
            $classFileName = DIR::CORE . $className . '.php';
            include($classFileName);
        };

spl_autoload_register($autoload);
?>
