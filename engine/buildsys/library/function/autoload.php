<?php

// Conf
use \DIR;
// Engine
use core\classes\DB\DB as DBCore;

$autoload = function ($p_class_name) {

            $path_in_arr = explode('\\', $p_class_name);
            $dirroot = array_shift($path_in_arr);
            $class_path = '';
            $p_class_name = implode('/', $path_in_arr);
            switch ($dirroot) {
                case 'site': $p_class_name = DIR::CORE . $p_class_name;
                    break;
                case 'admin': $p_class_name = DIR::CORE . $p_class_name;
                    break;
                case 'buildsys': $p_class_name = DIR::CORE . $p_class_name;
                    break;
            }
            include($class_path . $p_class_name . '.php');
            //print $p_class_name.'  Use: '. memory_get_usage().' Max:'.memory_get_peak_usage().PHP_EOL;
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
?>