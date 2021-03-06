<?php

namespace admin\library\mvc\comp\spl\objItem\build\review;

// Orm
use ORM\comp\spl\objItem\review\review as reviewOrm;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;
use buildsys\library\event\comp\spl\objItem\model as eventModelObjitem;
use admin\library\mvc\comp\spl\objItem\help\model\base\model as baseModel;

// Engine
use core\classes\admin\dirFunc;

// Conf
use \DIR;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class build implements \admin\library\mvc\comp\spl\objItem\help\builderAbs {

    public static function getTable(){
        return [reviewOrm::TABLE];
    }

    public static function getOIListArray($objItemItem, $objItemCompId){
        // Получаем путь до папки, где храняться данные превью
        $loadDir = baseModel::getPath($objItemCompId, $objItemItem->treeId, $objItemItem->id);
        $loadDir = dirFunc::getSiteDataPath($loadDir);
        $text = '';
        if (is_readable($loadDir . 'text.txt')) {
            $text = file_get_contents($loadDir . 'text.txt');
        } // if is_readable

        $idSplit = baseModel::getPath($objItemCompId, $objItemItem->treeId, $objItemItem->id);
        return [
            'caption' => $objItemItem->caption,
            'id' => $objItemItem->id,
            'idSplit' => $idSplit,
            // Сео название категории
            'videoUrl' => $objItemItem->videoUrl,
            'prevImgUrl' => $objItemItem->imgPrevUrl,
            'text' => $text
        ];
        // func. getOIListArray
    }

    public static function getOILasterArray($objItemObj, $objItemCompId, $oiLasterItemProp, $listCount){
        return [];
        // func. getOILasterArray
    }

    public static function getOIPopularArray($objItemObj, $objItemCompId, $oiPopularItemProp, $listCount){
        return [];
        // func. getOIPopularArray
    }

    public static function getOIRandomArray($objItemObj, $objItemCompId, $rndObjItemProp, $listCount, $arrCount){
        return [];
        // func. getOIRandomArray
    }

    // class. build
}