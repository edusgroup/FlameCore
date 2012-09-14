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
        // func. getTplTree
    }


    public static function saveData(integer $pContId, $pSaveData) {
        (new compPropOrm())->save('contId=' . $pContId, $pSaveData);
        // func. saveData
    }

    public static function isClassHasExtendsProp($pClassFile, $pNs){
        $classObj = comp::createClassAdminObj($pClassFile, $pNs);
        return (int)method_exists($classObj, 'compPropAction');
        // func. isClassHasExtendsProp
    }

    // class model
}