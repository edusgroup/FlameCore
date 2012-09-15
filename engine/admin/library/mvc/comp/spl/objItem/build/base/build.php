<?php

namespace admin\library\mvc\comp\spl\objItem\build\base;

// Orm
use ORM\comp\spl\objItem\review\review as reviewOrm;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;
use buildsys\library\event\comp\spl\objItem\model as eventModelObjitem;
use admin\library\mvc\comp\spl\objItem\help\model\base\model as baseModel;

// Conf
use \DIR;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class build implements \admin\library\mvc\comp\spl\objItem\help\builderAbs {
    public static function getTable(){
        return [];
    }

    public static function getOIListArray($objItemItem, $objItemCompId){
        return [];
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
// class build
}