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

    public static function dbQueryToAssoc($pListArr, $name='name', $val='value'){
        $return = [];
        foreach( $pListArr as $item){
            $return[$item[$name]] = $item[$val];
        } // foreach
        return $return;
        // func. dbQueryToAssoc
    }
	
	public static function dbQueryToAssocAll($pListArr, $pName='name'){
        $return = [];
        foreach( $pListArr as $item){
			$name = $item[$pName];
			unset($item[$pName]);
            $return[$name] = $item;
        } // foreach
        return $return;
        // func. dbQueryToAssocAll
    }
// class arrays
}
