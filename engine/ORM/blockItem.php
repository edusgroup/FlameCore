<?php

namespace ORM;

use admin\library\plugin\dhtmlxGrid\dhtmlxGrid;
use core\classes\filesystem;
use \DIR;

/**
 * Доступк к 
 *
 * @author Козленко В.Л.
 */
class blockItem extends \core\classes\DB\table {

    const TABLE = 'pr_blockItem';

    /**
     * Поля по которым разрешено делать условия
     * @var array 
     */
    //public $colWhere = array('wf_id', 'block_id', 'action_id');
    /**
     * Поля по которым разрешено делать выборку
     * @var array 
     */
    //public $colShow = array('name', 'sysname', 'comp_id', 'id');
    /**
     * Поля по которым разрешено сортировать
     * @var array 
     */
    //public $colOrder = array('id');
    /**
     * Поля по которые разрешено обновлять
     * @var array 
     */
    //public $colUpdate = array('name', 'sysname', 'comp_id');
    /**
     * Дополнительные поля для Insert-а
     * @var array 
     */
    //public $colInsert = array('wf_id', 'action_id', 'block_id');

    // TODO: Проанализировать и оптимизировать
//    public function getList($pVars) {
//        $blId = (int) $pVars['blid'];
//        $wfId = (int) $pVars['wfid'];
//        $acId = self::toNullInt($pVars['acid']);
//
//        $gridData = self::select('t.id, 0 as ch, "" as img, t.name, t.sysname, comp_id, classfile, action_id as acid, c.name as compname, c.ns', 't')
//                ->join(tree\componentTree::TABLE . ' c', 't.comp_id = c.id')
//                ->where(array('block_id' => $blId, 'wf_id' => $wfId, 'action_id' => array(null, $acId)))
//                ->fetchAll();
//
//        $iCount = count($gridData);
//        if ($acId) {
//            
//        } else {
//            for ($i = 0; $i < $iCount; $i++) {
//                $data = &$gridData[$i];
//                $data['classfile'] = array('val' => $data['classfile']);
//                $ns = str_replace('\\', '/', $data['ns']);
//                //$dir = DIR::CORE . DIR::SITE_COMPONENT_CLASSPATH . $ns . 'user/';
//                //$data['classfile']['options'] = dhtmlxGrid::list2options(filesystem::dir2array($dir));
//            }
//        }
//
//        $headData = array();
//
//        //$pName, $pId, $pType, $pWidth, $pAlign, $pColor, $pSort
//       /*$colunms[0] = dhtmlxGrid::createColumn('', 0, dhtmlxGrid::CELL_CHECKBOX, 32, 'center');
//        $colunms[1] = dhtmlxGrid::createColumn('', '', dhtmlxGrid::CELL_IMAGE, 32, 'center');
//        $colunms[2] = dhtmlxGrid::createColumn('Название', 'name', dhtmlxGrid::CELL_EDIT, '*', 'right');
//        $colunms[3] = dhtmlxGrid::createColumn('Сист. назв', 'sysname', dhtmlxGrid::CELL_EDIT, 125, 'right');
//        $colunms[4] = dhtmlxGrid::createColumn('Компонент', 'comp_id', dhtmlxGrid::CELL_STREE, 125, 'right');
//        $colunms[5] = dhtmlxGrid::createColumn('Класс', 'classfile', dhtmlxGrid::CELL_COMBOBOX, 125, 'right');
//
//        $headData['column'] = $colunms;*/
//
//        return array('body' => $gridData, 'head' => $headData);
//        // func. getList
//    }
// class blockItem
}

?>