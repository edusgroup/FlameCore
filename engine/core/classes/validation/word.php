<?php

namespace core\classes\validation;

class word {

    /**
     * Проверяет слово на содержание только 0-9a-Z_
     * @param \string $pWord проверяемая строка
     * @param \Exception $pExc Поумол: null. Исключение в случае отсуствия объекта
     * @return \boolean
     * @sample
     * isLatin('test');// true<br/>
     * isLatin('t^st');// false
     */
    public static function isLatin(string $pWord, $pExc=null) {
        // Только цифры, лат. буквы и знак подчёркивания
        $result = preg_match('/^\w+$/', $pWord);
        if ( $pExc && !$result){
            throw $pExc;
        }
        return $result;
    }

    // TODO: Сделеть универсальным. отвязать от русского языка
    public static function isSafe(string $pWord, $pExc=null) {
        $result = preg_match('/^[\wа-яА-Я ]+$/u', $pWord);
        if ( $pExc && !$result){
            throw $pExc;
        }
        return $result;
    }
    
    /**
     * Проверяет, является ли название верным классом или namespace-ом<br/>
     * @param string $pName название объекта
     * @param Exception $pExc По умолчан: null. 
     * Какое генерить исключение в случае неверного значение имени
     * @return boolean
     * @sample
     * isNsClassName('ns/class');// true<br/>
     * isNsClassName('n..s/class');// false
     */
    public static function isNsClassName(string $pName, $pExc=null){
        $result = preg_match('/^(\w+(\\\\)?)+$/', $pName);
        if ( $pExc && !$result){
            throw $pExc;
        }
        return $result;
    }
    // lass word
}