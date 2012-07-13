<?php

namespace core\classes\console;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class request {
    public static $paramList;

    public static function get($pKey) {
        return isset(self::$paramList[$pKey]) ? self::$paramList[$pKey] : null;
    }

    public static function init($pArgv) {
        self::$paramList = $pArgv;
        unset(self::$paramList[0]);
        foreach (self::$paramList as $key => $param) {
            $list = explode('=', $param);
            if (count($list) != 2) {
                continue;
            }
            unset(self::$paramList[$key]);
            list($varName, $varVal ) = $list;
            self::$paramList[$varName] = $varVal;
        }
    }

}