<?php

namespace admin\library\mvc\manager\varible;

//ORM
use ORM\tree\routeTree;
use ORM\varTree;
use ORM\tree\compContTree;
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
     * Проверка на существование переменной будет происходить через БД
     * @var integer 
     */
    const VAR_STORAGE_DB = 'db';
    /**
     * Проверка на существование переменной будет происходить чере memcache
     * @var integer 
     */
    const VAR_STORAGE_MEMCACHE = 'memcache';

    /**
     * Список типо источников данных переменных
     * @var array 
     */
    public static $typeList = array(
        self::VARR_TYPE_NONE => 'Выбирите тип',
        self::VAR_TYPE_COMP => 'Компонент',
        self::VAR_TYPE_TREE => 'Tree'
    );

    /**
     * Список видов источника хранения переменных
     * @var array 
     */
    public static $storageList = array(
        self::VAR_STORAGE_DB => 'БД',
        self::VAR_STORAGE_MEMCACHE => 'Memcache'
    );

    /**
     * Возвращет список источников данных для переменных.<br/>
     * Формат массива:<br/>
     * array(0=>array('id'=>1, 'name'=>'{name}'))
     * @return array
     */
    public static function getTypeList() {
        $typeList = self::$typeList;
        array_walk($typeList, function(&$item, $key) {
                    $item = array('id' => $key, 'name' => $item);
                });
        return $typeList;
        // func. getTypeList
    }
    
    /**
     * Возвращет список доступных хранилищ для переменных.<br/>
     * Формат массива:<br/>
     * array(0=>array('id'=>1, 'name'=>'{name}'))
     * @return array
     */
    public static function getStorageList() {
        $storageList = self::$storageList;
        array_walk($storageList, function(&$item, $key) {
                    $item = array('id' => $key, 'name' => $item);
                });
        return $storageList;
        // func. getStorageList
    }

    public static function getVarList($pAcOrm, $pTreeUrl) {
        // TODO: написать процедуру в БД, для получение переменных
        $idList = '';
        foreach ($pTreeUrl as $act) {
            $idList .= ',' . $act['id'];
        }
        $idList = substr($idList, 1);
        return $pAcOrm->selectAll('name, id', 'id in (' . $idList . ') and propType=1');
        // func. getVarList
    }

    public static function makeActionUrl($pTreeUrl) {
        $return = '';
        foreach ($pTreeUrl as $act) {
            $return .= '/' . $act['name'];
        }
        return $return;
        // func. makeActionUrl
    }

    public static function showVarTypeTable($pController, integer $pActionId) {
        $pController->setVar('tableList', array());
        $pController->view->setMainTpl('block/vartype/table.tpl.php');
        // func. showVarTypeTable
    }

    public static function getVarDataByActionId(integer $pAcId) {
        $actionTree = new routeTree();
        $data = $actionTree->select('a.varType, v.comp_id', 'a')
                ->join(varTree::TABLE . ' v', 'v.action_id=a.id')
                ->where('a.id=' . $pAcId)
                ->comment(__METHOD__)
                ->fetchFirst();
        return $data;
        // func. getVarDataByActionId
    }

// class model
}

?>