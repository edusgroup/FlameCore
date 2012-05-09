<?php

namespace core\classes;

/**
 * Description of dbus
 *
 * @author Козленко В.Л.
 */
class dbus {
    public static $vars = [];
    public static $comp = [];
    
    /**
     * Параметры авторизированного пользователя
     */
    public static $user = array();
    
    /**
     * Содержит данные по какие CSS, JS подключать<br/>
     * $head['jsStatic'] = [];
     * $head['jsDyn'] = [];
     * $head['css'] = [];
     */
    public static $head = [
        'jsStatic' => [],
        'jsDyn' => [],
        'css' => []
    ];
    
    public static function addJsStatic($pJsName){
        if ( !in_array($pJsName, dbus::$head['jsStatic']) ){
            dbus::$head['jsStatic'][] = $pJsName;
        } // if
    }

    public static function addJsDyn($pJsName){
        if ( !in_array($pJsName, dbus::$head['jsDyn']) ){
            dbus::$head['jsDyn'][] = $pJsName;
        } // if
    }
    
    public static function init(){
        
    }
    
// class dbus
}