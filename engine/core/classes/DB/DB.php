<?php

namespace core\classes\DB;

/**
 * Функция работы с массиами
 * 
 * @author Козленко В.Л.
 */
class DB {
    private static $list = array();
    private static $handleList = array();
    
    
    public static function addParam($pName, $pConnOpt){
        self::$list[$pName] = $pConnOpt;
        // func. add
    }
    
    public static function getParam($pName){
        return isset(self::$list[$pName])?self::$list[$pName]:null;
    }
    
    public static function getHandle($pName){
        return isset(self::$handleList[$pName])? self::$handleList[$pName] : null;
    }
    
    public static function addHandle($pName, $pConn) {
        self::$handleList[$pName] = $pConn;
       // func. addHandle
    }
    
// class DB
}

?>