<?php

namespace admin\library\mvc\comp\spl\objItem\help\build\article;

// Orm
use ORM\comp\spl\objItem\article\article as articleOrm;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;
use buildsys\library\event\comp\spl\objItem\model as eventModelObjitem;

// Conf
use \DIR;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class build implements \admin\library\mvc\comp\spl\objItem\category\builderAbs {

    public static function getTable(){
        return [articleOrm::TABLE];
    }

    public static function getOIListArray($objItemItem, $objItemCompId){
        $url = sprintf($objItemItem->urlTpl, $objItemItem->seoName, $objItemItem->seoUrl);
        $idSplit = objItemModel::getPath($objItemCompId, $objItemItem->treeId, $objItemItem->id);
        return [
            'caption' => $objItemItem->caption,
            'id' => $objItemItem->id,
            'url' => $url,
            'idSplit' => $idSplit,
            // Название категории, к которой пренадлежит статья
            'category' => $objItemItem->category,
            // Сео название категории
            'seoName' => $objItemItem->seoName,
            'dateAdd' => $objItemItem->date_add,
            'prevImgUrl' => $objItemItem->prevImgUrl
        ];
        // func. getOIListArray
    }

    public static function getOILasterArray($objItemObj, $objItemCompId, $oiLasterItemProp, $listCount){
        if ( $oiLasterItemProp['isCreatePreview']){
            // Создаём превью
            $objItemObj = eventModelObjitem::createMiniPreview(
                $objItemObj,
                $oiLasterItemProp['contId'],
                $oiLasterItemProp['compId'],
                $oiLasterItemProp['previewWidth'],
                $listCount,
                $oiLasterItemProp['resizeType']
            );
        } // if isCreatePreview

        $url = sprintf($objItemObj->urlTpl, $objItemObj->seoName, $objItemObj->seoUrl);
        $listArr = [
            'caption' => $objItemObj->caption,
            'id' => $objItemObj->id,
            'url' => $url,
            'dateAdd' => $objItemObj->date_add,
            'prevImgUrl' => $objItemObj->prevImgUrl,
            'miniDesck' => ''
        ];

        if ( $oiLasterItemProp['isAddMiniText']){
            $objItemDataDir = objItemModel::getPath($objItemObj->compId, $objItemObj->treeId, $objItemObj->id);
            $miniDescrFile = DIR::getSiteDataPath($objItemDataDir) . 'minidescr.txt';
            if (is_readable($miniDescrFile)) {
                $listArr['miniDesck'] = file_get_contents($miniDescrFile);
            }
        } // if ( isAddMiniText )

        return $listArr;
        // func. getOILasterArray
    }

    public static function getOIPopularArray($objItemObj, $objItemCompId, $oiPopularItemProp, $listCount){
        if ( $oiPopularItemProp['isCreatePreview']){
            // Создаём превью
            $objItemObj = eventModelObjitem::createMiniPreview(
                $objItemObj,
                $oiPopularItemProp['contId'],
                $oiPopularItemProp['comp_id'],
                $oiPopularItemProp['previewWidth'],
                $listCount,
                $oiPopularItemProp['resizeType']
            );
        } // if isCreatePreview

        // ----------------------------------------
        $url = sprintf($objItemObj->urlTpl, $objItemObj->seoName, $objItemObj->seoUrl);
        $listArr = [
            'caption' => $objItemObj->caption,
            'url' => $url,
            'prevImgUrl' => $objItemObj->prevImgUrl,
            'miniDesck' => ''
        ];

        if ( $oiPopularItemProp['isAddMiniText']){
            $objItemDataDir = objItemModel::getPath($objItemObj->compId, $objItemObj->treeId, $objItemObj->id);
            $miniDescrFile = DIR::getSiteDataPath($objItemDataDir) . 'minidescr.txt';
            if (is_readable($miniDescrFile)) {
                $listArr['miniDesck'] = file_get_contents($miniDescrFile);
            }
        } // if ( isAddMiniText )
        return $listArr;

        // func. getOIPopularArray
    }

    public static function getOIRandomArray($objItemObj, $objItemCompId, $rndObjItemProp, $listCount, $arrCount){
        if ( $rndObjItemProp['isCreatePreview']){
            // Создаём превью
            $objItemObj = eventModelObjitem::createMiniPreview(
                $objItemObj,
                $rndObjItemProp['contId'],
                $rndObjItemProp['comp_id'],
                $rndObjItemProp['previewWidth'],
                $arrCount,
                $rndObjItemProp['resizeType']
            );
        } // if isCreatePreview

        // ----------------------------------------
        $url = sprintf($objItemObj->urlTpl, $objItemObj->seoName, $objItemObj->seoUrl);
        $listArr = [
            'caption' => $objItemObj->caption,
            'url' => $url,
            'prevImgUrl' => $objItemObj->prevImgUrl,
            'miniDesck' => ''
        ];

        if ( $rndObjItemProp['isAddMiniText']){
            $objItemDataDir = objItemModel::getPath($objItemObj->compId, $objItemObj->treeId, $objItemObj->id);
            $miniDescrFile = DIR::getSiteDataPath($objItemDataDir) . 'minidescr.txt';
            if (is_readable($miniDescrFile)) {
                $listArr['miniDesck'] = file_get_contents($miniDescrFile);
            }
        } // if ( isAddMiniText )

        return $listArr;
        // func. getOIRandomArray
    }

    // class. build
}