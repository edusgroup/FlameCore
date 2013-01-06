<?php

namespace admin\library\mvc\utils\seo;

// Conf
use \DIR;
use \SITE;

// Engine
use core\classes\render;
use core\classes\filesystem;
use core\classes\comp;

// ORM
use ORM\tree\routeTree;
use ORM\utils\seo as seoOrm;
use ORM\urlTreePropVar;
use ORM\blockItem as blockItemOrm;
use ORM\blockItemSettings as blockItemSettingsOrm;
use ORM\tree\componentTree as componentTreeOrm;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// Model
use admin\library\mvc\manager\blockItem\model as blockItemModel;

/**
 * Description of breadCrumbs
 *
 * @author Козленко В.Л.
 */
class model {

    public static function getLoadData($pAcId) {
        // Получаем сохранённые данные
        $seoData = (new seoOrm())->select(
            's.seoData, s.blItemId, s.method, bis.classFile'
                . ', s.linkNextTitle, s.linkNextUrl, ct.ns', 's')
            ->joinLeftOuter(blockItemSettingsOrm::TABLE . ' bis', 'bis.blockItemId = s.blItemId')
            ->joinLeftOuter(blockItemOrm::TABLE . ' bi', 'bi.id = s.blItemId')
            ->joinLeftOuter(componentTreeOrm::TABLE . ' ct', 'ct.id = bi.compId')
            ->where('s.acId=' . $pAcId)
            ->comment(__METHOD__)
            ->fetchFirst();
        return $seoData;
        // func. getloadData
    }

    /**
     * Загружаем Список компонентов в blockItem
     * @static
     * @param int $pActionId
     * @return array|mixed
     */
    public static function loadCompList(integer $pActionId) {
        $data = (new blockItemOrm())->select('bi.id, bi.sysname, concat(bi.name, " (", ct.name, ")") name, ct.ns, bis.classFile', 'bi')
            ->join(componentTreeOrm::TABLE . ' ct', 'ct.id = bi.compId')
            ->join(routeTree::TABLE . ' r', 'r.id = ' . $pActionId . ' AND r.propType IN (0, 1)')
            ->join(urlTreePropVar::TABLE . ' rtp', 'rtp.acId =' . $pActionId . ' AND rtp.wf_id = bi.wf_id')
            ->join(blockItemSettingsOrm::TABLE . ' bis', 'bis.blockItemId = bi.id')
            ->where('bi.acId is null or bi.acId = ' . $pActionId)
            ->comment(__METHOD__)
            ->fetchAll();

        array_unshift($data, ['id' => -1, 'name' => 'Отключить']);
        return $data;
        // func. loadCompList
    }


    public static function getMethodListByBlockItemId(integer $pBlockItemId){
        $blItemProp = blockItemModel::getCompData($pBlockItemId);
        if ( !$blItemProp){
            throw new \Exception('BlockItem '.$pBlockItemId.' not found');
        } // if
        if ( !$blItemProp['classFile'] ){
            throw new \Exception('ClassFile not set in blID: '.$pBlockItemId);
        } // if

        $className = comp::fullNameClassSite($blItemProp['classFile'], $blItemProp['ns']);
        $compObj = new $className();
        $methodList = get_class_methods($compObj);
        return array_filter($methodList, function($pItem) {
            return substr($pItem, -3) === 'Seo';
        });
        // func. getMethodListByBlockItemId
    }

    // class model
}