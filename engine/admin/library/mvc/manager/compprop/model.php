<?php

namespace admin\library\mvc\manager\compprop;

// Engine
use core\classes\validation\filesystem as filevalid;
use core\classes\filesystem;
use core\classes\html\element as htmlelem;
use core\classes\comp;

// ORM
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\compprop as compPropOrm;

// Model
use admin\library\mvc\manager\complist\model as complistModel;

// Conf
use \DIR;
use \CONSTANT;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * Description of action
 *
 * @author Козленко В.Л.
 */
class model {

    public static function loadData(integer $pContId) {
        return (new compPropOrm())->selectFirst('*', 'contId=' . $pContId);
        // func. loadData
    }

    /**
     * @static
     * @param $pNsPath - namespace компонента
     * @param $pCategoryDir - категория класса
     * @param $pType название подпапки: user, ext
     * @return array
     */
    /*public static function getTplList($pNsPath, $pCategoryDir, $pType, $pIsOnlyArr=false) {
        $tplPath = DIR::getTplPath('comp/' . $pNsPath . $pCategoryDir . $pType);
        // Получаем список файлов-шаблонов
        $tplArr = filesystem::dir2array($tplPath, filesystem::FILE);
        return $pIsOnlyArr ? $tplArr : htmlelem::dirList2Select($tplArr);
        // func. getUserTplList
    }

    public static function getClassList($pNsPath, $pCategoryDir, $pType, $pIsOnlyArr=false){
        $classPath = DIR::getCompClassPath();
        $classPath .= $pNsPath . $pCategoryDir . $pType;
        // Получаем список папок
        $classArr = filesystem::dir2array($classPath, filesystem::FILE);
        return $pIsOnlyArr ? $classArr : htmlelem::dirList2Select($classArr);
        // func. getClassList
    }*/

    /*public static function checkTplName($pNsPath, $pTplFile) {
        // ===== Проверка на существование шаблона
        filevalid::isSafe($pTplFile, new \Exception('Bad filename: ' . $pTplFile, 123));
        // Получаем директорию где храняться шаблоны компонентов
        $tplPath = DIR::getTplPath('comp');
        // Получаем директорию с пользовательскими шаблонами компонентов
        $tplPath .= $pNsPath . '/'; //.CONSTANT::USER_FOLDER;
        // Существует ли класс в ФС
        if (!is_file($tplPath . $pTplFile)) {
            throw new \Exception('tplStat - File: ' . $pTplFile . ' not found', 124);
        }
        // func. checkTplName
    }*/

    public static function getClassTree($pNsPath){
        // ==================== Преоопределённые классы компонента для сайта
        $classFilePath = comp::getCompClassPath(false, $pNsPath);
        $treeInner = dhtmlxTree::createTreeOfDir($classFilePath);
        $treeInner = array_merge($treeInner, ['id'=>'#in', 'text'=>'Встроеные', 'userdata'=>[['name'=>'type', 'content'=>dhtmlxTree::FOLDER]]]);
        // ==================== Кастомные классы компонента для сайта
        $classFilePath = comp::getCompClassPath(true, $pNsPath);
        // Добавляем префикс, что бы если встретятся одинаковый папки, были разные ID
        $treeOuter = dhtmlxTree::createTreeOfDir($classFilePath, '[o]');
        $treeOuter = array_merge($treeOuter,
                ['id'=>'#out',
                'text'=>'Внешние',
                'userdata'=>[['name'=>'type', 'content'=>dhtmlxTree::FOLDER]],
                'im0' => 'folderClosed.gif']);
        $treeClass = ['id' => 0, 'item' => [$treeInner, $treeOuter]];
        return $treeClass;
        // func. getClassTree
    }

    public static function getTplTree($pNsPath){
        // ==================== Преоопределённые шаблоны компонента для сайта
        $tplFilePath = comp::getCompTplPath(false, $pNsPath);
        $treeInner = dhtmlxTree::createTreeOfDir($tplFilePath);
        $treeInner = array_merge($treeInner, ['id'=>'#in', 'text'=>'Встроеные', 'userdata'=>[['name'=>'type', 'content'=>dhtmlxTree::FOLDER]]]);
        // ==================== Кастомные шаблоны компонента для сайта
        $tplFilePath = comp::getCompTplPath(true, $pNsPath);
        // Добавляем префикс, что бы если встретятся одинаковый папки, были разные ID
        $treeOuter = dhtmlxTree::createTreeOfDir($tplFilePath, '[o]');
        $treeOuter = array_merge($treeOuter,
                 ['id'=>'#out',
                 'text'=>'Внешние',
                 'userdata'=>[['name'=>'type', 'content'=>dhtmlxTree::FOLDER]],
                 'im0' => 'folderClosed.gif']);
        $treeClass = ['id' => 0, 'item' => [$treeInner, $treeOuter]];
        return $treeClass;

        return $treeInner;
        // func. getTplTree
    }

    /*public static function checkClassName($pNsPath, $pClassFile) {
        // ===== Проверка существования класса
        filevalid::isSafe($pClassFile, new \Exception('Bad filename: ' . $pClassFile, 128));
        // Получаем директорию где храняться классы компонентов
        $classPath = DIR::getCompClassPath();
        // Получаем директорию с пользовательскими классами компонентов
        $classPath .= $pNsPath . '/';
        // Существует ли класс в ФС
        if (!is_file($classPath . $pClassFile)) {
            throw new \Exception('$tplStat - Файл: ' . $pClassFile . ' не найден', 125);
        }
        // func. checkClassName
    }*/

    public static function saveData(integer $pContId, $pSaveData) {
        $compPropOrm = new compPropOrm();
        $compPropOrm->save('contId=' . $pContId, $pSaveData);
        // func. saveData
    }

    public static function isClassHasExtendsProp($pClassFile, $pNs){
        $classObj = comp::createClassAdminObj($pClassFile, $pNs);
        return (int)method_exists($classObj, 'compPropAction');
        // func. isClassHasExtendsProp
    }

    // class model
}