<?php

namespace admin\library\mvc\manager\varible;

//ORM
use ORM\tree\routeTree;
use ORM\varTree;
use ORM\tree\compContTree;
use core\classes\comp;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * Description
 *
 * @author Козленко В.Л.
 */
class model {
    /**
     * Источник данных неопределён
     * @var integer
     */
    const VARR_TYPE_NONE = 'none';

    /**
     * Даные для переменные будут взяты из дерева
     * @var integer
     */
    const VAR_TYPE_TREE = 'tree';

    /**
     * Даные для переменные будут выданы компонентом
     * @var integer
     */
    const VAR_TYPE_COMP = 'comp';

    /**
     * Список типо источников данных переменных
     * @var array
     */
    public static $typeList = [
        self::VARR_TYPE_NONE => 'Выбирите тип',
        self::VAR_TYPE_COMP => 'Компонент',
        self::VAR_TYPE_TREE => 'Tree'
    ];

    /**
     * Возвращет список источников данных для переменных.<br/>
     * Формат массива:<br/>
     * array(0=>array('id'=>1, 'name'=>'{name}'))
     * @return array
     */
    public static function getTypeList() {
        $typeList = self::$typeList;
        array_walk($typeList, function (&$item, $key) {
            $item = ['id' => $key, 'name' => $item];
        });
        return $typeList;
        // func. getTypeList
    }

    public static function getVarList($pAcOrm, $pAcId) {
        $varList = [];
        $pathUrl = $pAcOrm->getActionUrlById($pAcId);
        foreach ($pathUrl as $item) {
            if ($item['propType'] == 1) {
                $varList[] = $item;
            } // if
        } // foreach
        return $varList;
        // func. getVarList
    }

    public static function makeActionUrl($pTreeUrl) {
        $return = '';
        foreach ($pTreeUrl as $act) {
            $return =  $act['name'] . '/' . $return;
        }
        return '/'.$return;
        // func. makeActionUrl
    }

    public static function showVarTypeTable($pController, integer $pActionId) {
        $pController->setVar('tableList', []);
        $pController->view->setMainTpl('block/vartype/table.tpl.php');
        // func. showVarTypeTable
    }

    public static function getVarClassTree($pNsPath) {
        // ==================== Преоопределённые классы компонента для сайта
        $classFilePath = comp::getSiteVarClassPath(false, $pNsPath);
        $treeInner = dhtmlxTree::createTreeOfDir($classFilePath);
        $treeInner = array_merge($treeInner,
                                 ['id' => '#in',
                                 'text' => 'Встроеные',
                                 'userdata' => [['name' => 'type', 'content' => dhtmlxTree::FOLDER]],
                                 'im0' => 'folderClosed.gif']);

        // ==================== Кастомные классы компонента для сайта
        $classFilePath = comp::getSiteVarClassPath(true, $pNsPath);
        // Добавляем префикс, что бы если встретятся одинаковый папки, были разные ID
        $treeOuter = dhtmlxTree::createTreeOfDir($classFilePath, '[o]');
        $treeOuter = array_merge($treeOuter,
                                 ['id' => '#out',
                                 'text' => 'Внешние',
                                 'userdata' => [['name' => 'type', 'content' => dhtmlxTree::FOLDER]],
                                 'im0' => 'folderClosed.gif']);

        $treeClass = ['id' => 0, 'item' => [$treeInner, $treeOuter]];
        return $treeClass;
        // func. getClassTree
    }

    public static function getVarDataByActionId(integer $pAcId) {
        return (new routeTree())->select('a.varType, v.comp_id', 'a')
            ->join(varTree::TABLE . ' v', 'v.action_id=a.id')
            ->where('a.id=' . $pAcId)
            ->comment(__METHOD__)
            ->fetchFirst();
        // func. getVarDataByActionId
    }

    // class model
}