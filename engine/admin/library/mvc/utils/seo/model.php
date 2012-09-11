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

/**
 * Description of breadCrumbs
 *
 * @author Козленко В.Л.
 */
class model {

    public static function getloadData($pAcId) {
        // Получаем сохранённые данные
        $seoData = (new seoOrm())->select(
            's.title, s.descr, s.keywords, s.blItemId, s.method, bis.classFile'
                . ', s.linkNextTitle, s.linkNextUrl, ct.ns', 's')
            ->joinLeftOuter(blockItemSettingsOrm::TABLE . ' bis', 'bis.blockItemId = s.blItemId')
            ->joinLeftOuter(blockItemOrm::TABLE . ' bi', 'bi.id = s.blItemId')
            ->joinLeftOuter(componentTreeOrm::TABLE . ' ct', 'ct.id = bi.compId')
            ->where('s.acId=' . $pAcId)
            ->comment(__METHOD__)
            ->fetchFirst();
        return $seoData;

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

    /**
     * Загружаем список методов класса
     * @static
     * @param $pBlockItemId
     * @return array
     */
    public static function getMethodListByBiId($pBlockItemId) {
        $seoData = (new blockItemOrm())->select('bis.classFile, ct.ns', 'bi')
            ->join(componentTreeOrm::TABLE . ' ct', 'ct.id = bi.compId')
            ->join(blockItemSettingsOrm::TABLE . ' bis', 'bis.blockItemId = bi.id')
            ->where('bi.id=' . $pBlockItemId)
            ->comment(__METHOD__)
            ->fetchFirst();

        $className = comp::getClassFullName($seoData['classFile'], $seoData['ns']);
        $methodList = get_class_methods(new $className());
        $methodList = array_filter($methodList, function($pItem) {
            return substr($pItem, -3) === 'Seo';
        });
        return $methodList;
        // func. getMethodSeoList
    }

    public static function getMethodSeoList($pNs, $pClassName, $pClassType) {
        if (!$pClassName) {
            return;
        }
        $className = substr($pClassName, 1, strlen($pClassName) - 5);
        $className = str_replace('/', '\\', $className);
        $className = comp::getFullCompClassName($pClassType, $pNs, 'logic', $className);

        $methodList = get_class_methods(new $className());
        $methodList = array_filter($methodList, function($pItem) {
            return substr($pItem, -3) === 'Seo';
        });
        return $methodList;
        // func. getMethodSeoList
    }
    // class model
}