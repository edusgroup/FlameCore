<?php

namespace core\classes;

/**
 * Функция работы с массиами
 */
class arrays {

    /**
     * Поиск подстроки в массе
     * @param string $neele
     * @param mixed $haystack
     * @return bool
     *
     */
    public static function pregSearch($needle, $haystack) {
        $needle = (array) $needle;
        //$needle = array_map('preg_quote', $needle);
        foreach ($needle as $pattern) {
            if (count(preg_grep("/$pattern/", $haystack)) > 0){
                return true;
            } // if
        }
        return false;
        // func. searchPreg
    }
// class arrays
}
