<?php

namespace core\classes;

/**
 * Получение переменных и работы с массивами GET или POST<br/>
 * @author Козленко В.Л.
 */
class request {
    //const POST = 1;
    //const GET = 2;
    /**
     * Получает GET переменную
     * @param string $p_name название переменной
     * @param mixed $p_default значение по умолчанию, если переменной нет
     * @return string значение переменной
     */
    public static function get(string $p_name, $p_default = '') {
        return isset($_GET[$p_name]) ? $_GET[$p_name] : $p_default;
    }

    //    public static function session(string $p_name, $p_default='') {
    //        return isset($_SESSION[$p_name]) ? $_SESSION[$p_name] : $p_default;
    //    }

    /*public static function getPost($name, $default='') {
        return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? (int) $_POST[$name] : $default);
    }*/

    /**
     * Получает значение переменной из GET массива
     * @param string $pName название переменной
     * @param integer $pDefault значение по умолчанию, если переменной нет
     * или опеределена как пустая
     * @return integer значение переменной
     */
    public static function getInt($pName, $pDefault = 0) {
        if (!isset($_GET[$pName])) {
            return $pDefault;
        }
        return $_GET[$pName] == '' ? $pDefault : (int)$_GET[$pName];
    }

    /**
     * Получает значение переменной из POST массива
     * @param string $pName название переменной
     * @param integer $pDefault значение по умолчанию, если переменной нет
     * или опеределена как пустая
     * @return integer значение переменной
     */
    public static function postInt($pName, $pDefault = 0) {
        if (!isset($_POST[$pName]))
            return $pDefault;
        return $_POST[$pName] == '' ? $pDefault : (int)$_POST[$pName];
    }

    /**
     * Получает POST переменную
     * @param string $pName название переменной
     * @param string $pDefault значение по умолчанию, если переменной нет
     * @return mixed значение переменной
     */
    public static function post(string $pName, $pDefault = '') {
        if (!isset($_POST[$pName]))
            return $pDefault;
        $val = $_POST[$pName];
        //if (get_magic_quotes_gpc($val) && !is_array($val))
        //    return stripslashes($val);
        return $val;
    }

    /**
     * Возвращает целочисленное значение переменной из GET или POST<br/>
     * С начала проверяет GET а потом POST
     * @param string $pVarName имя переменной
     * @param string $pValueDefault значение по умолчанию, если переменная не задана
     * @return integer
     */
    public static function getVarInt(string $pVarName, $pValueDefault = 0) {
        if (isset($_GET[$pVarName]))
            return (int)($_GET[$pVarName]);
        else
            return isset($_POST[$pVarName]) ? (int)$_POST[$pVarName] : $pValueDefault;
    }

    /**
     * Возвращает значение переменной из GET или POST<br/>
     * С начала проверяет GET а потом POST
     * @param string $pVarName имя переменной
     * @param string $pValueDefault значение по умолчанию, если переменная не задана
     * @return mixed
     */
    public static function getVar(string $pVarName, $pValueDefault = '') {
        if (isset($_GET[$pVarName]))
            return ($_GET[$pVarName]);
        else
            return isset($_POST[$pVarName]) ? $_POST[$pVarName] : $pValueDefault;
    }

    /**
     * Получение безопастной строки из переменных POST.<br/>
     * переменная обрабатывается htmlspecialchars
     * @param string $pName имя переменной
     * @param mixed $pDefault значение по умолчанию, если переменная не передана
     * @param integer $pQuotes
     * @return string
     */
    public static function postSafe($pName, $pDefault = '', $pQuotes = ENT_COMPAT) {
        return htmlspecialchars(self::post($pName, $pDefault), $pQuotes);
    }

    /**
     * Возвращает TRUE, если запрос типа POST, иначе FALSE
     * @return type
     */
    public static function isPost() {
        //return $_SERVER['request_METHOD'] == 'POST';
        return strlen($_SERVER['REQUEST_METHOD']) == 4;
    }

    /**
     * Возвращает TRUE, если производится загрузка файла, иначе FALSE
     * @return boolean
     */
    public static function isFileUpload() {
        return count($_FILES) != 0;
    }

}