<?php

use core\classes\DB\DB as DBCore;

spl_autoload_register(function ($pClassName) {
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

    if ( !is_file($classFileName)){
        return false;
    }

    include($classFileName);
});

/*register_shutdown_function(function() {
    $handle = DBCore::getHandle('site');
    if ($handle) {
        // TODO продумать как сделать универсально
        $handle->close();
    }

    $error = error_get_last();
    if ( $error !== NULL ){
        errorNotify($error);
    }
});*/

function redirect(string $pURL){
    header("HTTP/1.1 301 Moved Permanently");
    header('Location: '.$pURL);
    exit;
}

function errorNotify($error){
	echo json_encode([
        'type'=>'err',
        'msgp'=>$error['message'],
        'line' => $error['line'],
        'file' => $error['file'],
        'etype' => $error['etype'],
        'msg'=>'Fatal error in site']);
	// func. 
}
