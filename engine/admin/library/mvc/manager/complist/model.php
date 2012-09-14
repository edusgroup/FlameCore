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

// Engine
use core\classes\validation\word;
use core\classes\DB\tree;
use core\classes\event;

/**
 * Description of action
 *
 * @author Козленко В.Л.
 */
class model {

    public static function getCompIdBySysname(string $pName) {
        $ext = new \Exception('Component sysname: ' . $pName . ' не найден', 23);
        return (int)(new componentTree())->get('id', ['sysname' => $pName], $ext);
        // func. getClassDataByCompIds
    }

    // Возвращает дерево с форматированными картинками
    public static function getTreeCompCont(integer $pCompId) {
        $data = (new compContTree())->select('cc.id, cc.tree_id, cc.name, cc.item_type, cp.contId', 'cc')
            ->joinLeftOuter(compprop::TABLE . ' cp', 'cc.id = cp.contId AND cp.parentLoad != 1')
            ->where('cc.comp_id=' . $pCompId . ' AND isDel="no"')
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
            } // if
            // funct. endBrunch
        };
        $tree = dhtmlxTree::all($data, 0);
        dhtmlxTree::clear();
        return $tree;
        // func. getTreeCompCont
    }

    // class model complist
}