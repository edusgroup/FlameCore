<?php

namespace core\classes;

/**
 * Description of cookie
 *
 * @author Козленко В.Л.
 */
class cookie {
    public static function getInt($pName, $pDefault = 0) {
        if (!isset($_COOKIE[$pName])) {
            return $pDefault;
        }

        return $_COOKIE[$pName] == '' ? $pDefault : (int)$_COOKIE[$pName];
    }

    public static function get($pName, $pDefault = '') {
        if (!isset($_COOKIE[$pName])) {
            return $pDefault;
        }
        return $_COOKIE[$pName];
    }

    public static function getListNum($pName, $pDelim = ',', $pDefault = []) {
        if (!isset($_COOKIE[$pName])) {
            return $pDefault;
        }
        $list = explode($pDelim, $_COOKIE[$pName]);
        $list = array_map('intVal', $list);
        return $list;
    }
}
