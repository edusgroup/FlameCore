<?php

namespace admin\library\mvc\comp\spl\oiList;

// Engine
use core\classes\filesystem;
use core\classes\html\element as htmlelem;
use core\classes\comp;
use core\classes\validation\filesystem as filevalid;

// Model
use admin\library\mvc\manager\complist\model as complistModel;

// Conf
use \DIR;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class model {

    public static function getBuildClassTree($pNsPath){
        // ==================== Преоопределённые классы компонента для сайта
        $classFilePath = comp::getCompBuildClassPath(false, $pNsPath);
        $treeInner = dhtmlxTree::createTreeOfDir($classFilePath);
        $treeInner = array_merge($treeInner, ['id'=>'#in', 'text'=>'Встроеные', 'userdata'=>[['name'=>'type', 'content'=>dhtmlxTree::FOLDER]]]);
        // ==================== Кастомные классы компонента для сайта
        $classFilePath = comp::getCompBuildClassPath(true, $pNsPath);
        // Добавляем префикс, что бы если встретятся одинаковый папки, были разные ID
        $treeOuter = dhtmlxTree::createTreeOfDir($classFilePath, '[o]');
        $treeOuter = array_merge($treeOuter,
                                 ['id'=>'#out',
                                 'text'=>'Внешние',
                                 'userdata'=>[['name'=>'type', 'content'=>dhtmlxTree::FOLDER]],
                                 'im0' => 'folderClosed.gif']);
        return ['id' => 0, 'item' => [$treeInner, $treeOuter]];
        // func. getBuildClassTree
    }

    public static function isClassFileExit($pClassFile, $pNsPath){
        $classFileData = comp::getFileType($pClassFile);
        // Правильно ли имя файла
        filevalid::isSafe($classFileData['file'], new \Exception('Неверное имя файла:' .$classFileData['file']));

        // Проверяем налачие файла
        $classFilePath = comp::getCompBuildClassPath($classFileData['isOut'], $pNsPath);
        if ( !is_file($classFilePath.$classFileData['file']) ){
            throw new \Exception('File : ' . $classFileData['file'] . ' not found', 235);
        } // if
        // func. isClassFileExit
    }

    // class. model
}