<?php

namespace admin\library\mvc\comp\spl\breadCrumbs;

// ORM
use ORM\tree\routeTree;
use ORM\urlTreePropVar;
use ORM\blockItem as blockItemOrm;
use ORM\comp\spl\breadCrumbs\breadCrumbs as breadCrumbsOrm;
use ORM\tree\componentTree as componentTreeOrm;

/**
 * Description of model breadCrumbs
 *
 * @author Козленко В.Л.
 */
class model {

    /*public static function loadCompList(integer $pActionId) {
        $return = (new blockItemOrm())
            ->select('bi.id, concat(bi.name, " (", ct.name, ")") name', 'bi')
            ->join(componentTreeOrm::TABLE . ' ct', 'ct.id = bi.compId')
            ->join(routeTree::TABLE . ' r', 'r.id = '.$pActionId.' AND r.propType IN (0, 1)')
            ->join(urlTreePropVar::TABLE . ' rtp', 'rtp.acId =' . $pActionId . ' AND rtp.wf_id = bi.wf_id')
            ->where('bi.acId is NULL or bi.acId=' . $pActionId)
            ->comment(__METHOD__)
            ->fetchAll();

        array_unshift($return, ['id' => -1, 'name' => 'Отключить']);
        return $return;
        // func. loadCompList
    }*/

    public static function createCrumbs(&$codeTmp, $pBlockItemId, $pAcId) {
        $pathUrl = (new routeTree())->getTreeUrlById(routeTree::TABLE, $pAcId);
        $pathUrl = array_map(function($pItem) {
            return $pItem['id'];
        }, $pathUrl);
        $pathUrl = array_reverse($pathUrl);
        $pathUrl = implode(',', $pathUrl);
        $data = (new breadCrumbsOrm())
            ->select('bc.name as caption, r.name', 'bc')
            ->join(routeTree::TABLE . ' r', 'r.id = bc.acId')
            ->where('bc.acId in (' . $pathUrl . ')')
            ->order('field(bc.acId, ' . $pathUrl . ')')
            ->fetchAll();
        foreach ($data as $item) {
            $codeTmp['breadcrumbs'] = ['caption' => $item['caption'], 'name' => $item['name']];
        }
        // func. createCrums
    }
    // class model breadCrumbs
}