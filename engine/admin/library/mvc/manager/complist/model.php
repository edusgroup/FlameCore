<?php

namespace admin\library\mvc\manager\complist;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;
// ORM
use ORM\compprop as compPropOrm;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\compprop;
// Model
use admin\library\classes\mvc\model\compprop as compPropModel;
// Conf
use \DIR;
use \CONSTANT;
use \SITE as ADMIN_CONF;
// Engine
use core\classes\validation\word;
use core\classes\DB\tree;
use core\classes\event;
use core\classes\component\abstr\admin\comp as compAbs;

/**
 * Description of action
 *
 * @author Козленко В.Л.
 */
class model {

    public static function getCompIdBySysname(string $pName){
        $componentTree = new componentTree();
        return (int)$componentTree->get('id', 
                array('sysname' => $pName), 
                new \Exception('Component sysname: '.$pName.' не найден', 23));
        // func. getClassDataByCompIds
    }

    /**
     * Получаение всего контента по компоненту
     * @param integer $pCompId ID компонента. см. табл. component_tree
     * @return array
     * Возвращает данные в формате для DHTMLX Tree. см. класс dhtmlx
     */
    public static function getOnlyContTreeByCompId(integer $pCompId) {
        return dhtmlxTree::createTreeOfTable(new compContTree(), array('comp_id' => $pCompId/*, 'item_type' => 0*/));
        // func. getOnlyContTreeByCompId
    }

    // Возвращает дерево с форматированными картинками
    public static function getTreeCompCont(integer $pCompId){
        $compContTree = new compContTree();
        $data = $compContTree->select('cc.id, cc.tree_id, cc.name, cc.item_type, cp.contId', 'cc')
                ->joinLeftOuter(compprop::TABLE . ' cp', 'cc.id = cp.contId AND cp.parentLoad != 1')
                ->where('cc.comp_id=' . $pCompId.' AND isDel="no"')
                ->order('cc.tree_id, cc.item_type, cc.id desc')
                ->comment(__METHOD__)
                ->fetchAll();

        dhtmlxTree::$endBrunch = function(&$pDist, $pType, $pSource, $pNum, $pParam) {
                    dhtmlxTree::endBrunch($pDist, $pType, $pSource, $pNum, $pParam);
                    $isProp = $pSource[$pNum]['contId'];
                    if ($isProp) {
                        $pDist['im0'] = 'folderEmpty.gif';
                        $pDist['im1'] = 'folderEmpty.gif';
                        $pDist['im2'] = 'folderEmpty.gif';
                    }
                    // funct. endBrunch
                };
        $tree = dhtmlxTree::all($data, 0);
        dhtmlxTree::clear();
        return $tree;
        // func. getTreeCompCont
    }
// class model
}
?>