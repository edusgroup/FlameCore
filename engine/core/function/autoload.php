<?php

$autoload = function ($pClassName) {
            $className = str_replace('\\', '/', $pClassName);
            if ( substr($className, 0, 4) == 'site'){
                $classFileName = DIR::CORE . substr($className, 4) . '.php';
            }else{
                $classFileName = DIR::CORE . $className . '.php';
            }
            include($classFileName);
        };

spl_autoload_register($autoload);
