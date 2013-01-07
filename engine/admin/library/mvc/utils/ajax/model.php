<?php

namespace admin\library\mvc\utils\ajax;

// Engine
use core\classes\comp as compCore;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

class model {

    public static function getAjaxTree($pNsPath) {
        // ==================== Преоопределённые классы компонента для сайта
        $classFilePath = compCore::getAjaxCompClassPath(false, $pNsPath);
        $treeInner = dhtmlxTree::createTreeOfDir($classFilePath);
        $treeInner = array_merge($treeInner,
                                 ['id' => '#in',
                                 'text' => 'Встроеные',
                                 'userdata' => [['name' => 'type', 'content' => dhtmlxTree::FOLDER]],
                                 'im0' => 'folderClosed.gif']);
        // ==================== Кастомные классы компонента для сайта
        $classFilePath = compCore::getAjaxCompClassPath(true, $pNsPath);
        // Добавляем префикс, что бы если встретятся одинаковый папки, были разные ID
        $treeOuter = dhtmlxTree::createTreeOfDir($classFilePath, '[o]');
        $treeOuter = array_merge($treeOuter,
                                 ['id' => '#out',
                                 'text' => 'Внешние',
                                 'userdata' => [['name' => 'type', 'content' => dhtmlxTree::FOLDER]],
                                 'im0' => 'folderClosed.gif']);
        return ['id' => 0, 'item' => [$treeInner, $treeOuter]];
        // func. getAjaxTree
    }

    public static function createClass($pClassName){

        // func. createClass
    }

    // class model
}
