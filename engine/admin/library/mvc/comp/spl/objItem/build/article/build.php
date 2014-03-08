<?php

namespace admin\library\mvc\comp\spl\objItem\build\article;

// Orm
use ORM\comp\spl\objItem\article\article as articleOrm;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;
use buildsys\library\event\comp\spl\objItem\model as eventModelObjitem;
use admin\library\mvc\comp\spl\objItem\help\model\base\model as baseModel;

// Engine
use core\classes\admin\dirFunc;

// Conf
use \DIR;
use \site\conf\DIR as SITE_DIR;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class build implements \admin\library\mvc\comp\spl\objItem\help\builderAbs {

    public static function getTable(){
        return [articleOrm::TABLE];
        // func. getTable
    }

    public static function getOIListArray($objItemItem, $objItemCompId){
        $url = sprintf($objItemItem->urlTpl, $objItemItem->seoName, $objItemItem->seoUrl);
        $idSplit = baseModel::getPath($objItemCompId, $objItemItem->treeId, $objItemItem->id);
        return [
            'caption' => $objItemItem->caption,
            'id' => $objItemItem->id,
            'url' => $url,
            'idSplit' => $idSplit,
            // Название категории, к которой пренадлежит статья
            'category' => $objItemItem->category,
            // Сео название категории
            'seoName' => $objItemItem->seoName,
            'isPrivate' => $objItemItem->isPrivate,
            'dateAdd' => $objItemItem->date_add,
            'prevImgUrl' => $objItemItem->prevImgUrl/*,
			'divArticle' => $objItemItem->divArticle,*/
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

        $objItemDataDir = baseModel::getPath($objItemObj->compId, $objItemObj->treeId, $objItemObj->id);

        $url = sprintf($objItemObj->urlTpl, $objItemObj->seoName, $objItemObj->seoUrl);
        $listArr = [
            'caption' => $objItemObj->caption,
            'id' => $objItemObj->id,
            'url' => $url,
            'dateAdd' => $objItemObj->date_add,
            'prevImgUrl' => $objItemObj->prevImgUrl,
            'miniDesck' => '',
            'itemPath' => $objItemDataDir
        ];

        if ( $oiLasterItemProp['isAddMiniText']){
            $miniDescrFile = dirFunc::getSiteDataPath($objItemDataDir) . 'minidescr.txt';
            //echo $miniDescrFile, PHP_EOL;
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
            $objItemDataDir = baseModel::getPath($objItemObj->compId, $objItemObj->treeId, $objItemObj->id);
            $miniDescrFile = dirFunc::getSiteDataPath($objItemDataDir) . 'minidescr.txt';
            if (is_readable($miniDescrFile)) {
                $listArr['miniDesck'] = file_get_contents($miniDescrFile);
            }
        } // if ( isAddMiniText )
        return $listArr;

        // func. getOIPopularArray
    }

    public static function getOIRandomArray($objItemObj, $objItemCompId, $rndObjItemProp, $listCount, $fileNum){
        if ( $rndObjItemProp['isCreatePreview']){
            // Создаём превью
            $objItemObj = eventModelObjitem::createMiniPreview(
                $objItemObj,
                $rndObjItemProp['contId'],
                $rndObjItemProp['comp_id'],
                $rndObjItemProp['previewWidth'],
                $fileNum,
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
            $objItemDataDir = baseModel::getPath($objItemObj->compId, $objItemObj->treeId, $objItemObj->id);
            $miniDescrFile = dirFunc::getSiteDataPath($objItemDataDir) . 'minidescr.txt';
            if (is_readable($miniDescrFile)) {
                $listArr['miniDesck'] = file_get_contents($miniDescrFile);
            }
        } // if ( isAddMiniText )

        return $listArr;
        // func. getOIRandomArray
    }

    // class. build
}