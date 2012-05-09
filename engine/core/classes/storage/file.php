<?php
namespace core\classes\storage;

use core\classes\valid;
use core\classes\filesystem;

/**
 * Сохранение и загрузка переменных из файлов.
 * @samples
 * controller::initStorage('file');
 * storage::saveVar(DIR::SOME_DIR, 'name', $varData);
 */
class storage{
    const EXT = '.txt';

    /**
     * Загрузка переменной из файла
     * @param string $pPath путь к диретории, где хранятся переменные
     * @param string $pName имя переменной
     * @param mixed $pDefault значение по умолчанию, если переменной не существует
     * @param boolean $pUnserialize расселизировать ли данные, после загрузки из файла
     * @return mixed  
     * @samples
     * controller::initStorage('file');
     * storage::loadVar('C:\\', 'number', -1);
     */
    public static function loadVar(string $pPath, $pName, $pDefault=array(), $pUnserialize=true) {
        $file = $pPath.$pName.self::EXT;
        if (!is_file($file)){
            return $pDefault;
        }
        if ( !is_readable($file))
            throw new \Exception('Не возможно произвести чтение: ' . $file, 24);
        $str = file_get_contents($file);
        return $pUnserialize ? unserialize($str) : $str;
    }

    /**
     * Сохранение переменной в файл<br/>
     * Расширение файла см. storage::EXT
     * @param string $pPath путь к диретории, где хранятся переменные
     * @param string $pName имя переменной
     * @param mixed $pData данные для сохранения
     * @param boolean $pSerialize сериализовать ли данные, TRUE по умолчанию
     * @samples
     * $data = array('two' => 2 );
     * storage::saveVar('c:\\', 'number', $data);
     */
    public static function saveVar(string $pPath, $pName, $pData, $pSerialize=true){
        $data = $pData;
        if ( $pSerialize ){
            $data = serialize($data);
        }
        filesystem::saveFile($pPath, $pName.self::EXT, $data);
    }
    
    /**
     * Удаляем переменную. Удаление файла
     * @param string $pPath путь к директории, где хранятся переменные
     * @param string $pName имя переменной
     * @samples
     * controller::initStorage('file');
     * storage::deleteVar('c:\\', 'number');
     */
    public static function deleteVar(string $pPath, $pName){
        return filesystem::unlink($pPath.$pName.self::EXT);
    }
    
    /**
     * Проверка существования переменной.<br/>
     * TRUE если переменная существует, FALSE в ином 
     * Проверка существования и прав на чтение файла
     * @param string $pPath путь к директории, где хранятся переменные
     * @param string $pName имя переменной
     * @return boolean
     * @samples
     * controller::initStorage('file');
     * echo (int)storage::isExists('C:\\', 'number');
     */
    public static function isExists(string $pPath, $pName){
        $pPath = filesystem::andEndSlash($pPath);
        return is_readable($pPath.$pName.self::EXT);
    }

}

?>