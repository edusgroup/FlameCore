<?php
namespace admin\library\mvc\comp\spl\objItem\category;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
interface builderAbs {
    public static function getTable();

    public static function getOIListArray($objItemItem, $objItemCompId);

    public static function getOILasterArray($objItemObj, $objItemCompId, $oiLasterItemProp, $listCount);

    public static function getOIPopularArray($objItemObj, $objItemCompId, $oiPopularItemProp, $listCount);

    public static function getOIRandomArray($objItemObj, $objItemCompId, $rndObjItemProp, $listCount, $arrCount);

    // interface builderAbs
}
