<?php

namespace admin\library\mvc\manager\compprop;

// Engine
use core\classes\validation\filesystem as filevalid;
// ORM
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\compprop as compPropOrm;
// Model
use admin\library\mvc\manager\complist\model as complistModel;
// Conf
use \DIR;
use \CONSTANT;

/**
 * Description of action
 *
 * @author Козленко В.Л.
 */
class model {

    public static function loadData(integer $pContId) {
        $compPropOrm = new compPropOrm();
        return $compPropOrm->selectFirst('*', 'contId=' . $pContId);
        // func. loadData
    }
    
   
    public static function checkTplName($pNsPath, $pTplFile){
        // ===== Проверка на существование шаблона
        filevalid::isSafe($pTplFile, new \Exception('Неверное имя файла: '.$pTplFile, 123));
        // Получаем директорию где храняться шаблоны компонентов
        $tplPath = DIR::getTplPath('comp');
        // Получаем директорию с пользовательскими шаблонами компонентов
        $tplPath .= $pNsPath;//.CONSTANT::USER_FOLDER;
        // Существует ли класс в ФС
        if ( !is_file($tplPath.$pTplFile)){
            throw new \Exception('$tplStat - Файл: ' . $pTplFile . ' не найден', 124);
        }
        // func. checkTplName
    }
    
    public static function checkClassName($pNsPath, $pClassFile){
        // ===== Проверка существования класса
        filevalid::isSafe($pClassFile, new \Exception('Неверное имя файла: '.$pClassFile, 123));
        // Получаем директорию где храняться классы компонентов
        $classPath = DIR::getCompClassPath();
        // Получаем директорию с пользовательскими классами компонентов
        $classPath .= $pNsPath;
        // Существует ли класс в ФС
        if ( !is_file($classPath.$pClassFile)){
            throw new \Exception('$tplStat - Файл: ' . $pClassFile . ' не найден', 124);
        }
        // func. checkClassName
    }

    public static function saveData(integer $pContId, $pSaveData){
        $compPropOrm = new compPropOrm();
        $compPropOrm->save('contId=' . $pContId, $pSaveData);
        // func. saveData
    }

// class model
}

?>