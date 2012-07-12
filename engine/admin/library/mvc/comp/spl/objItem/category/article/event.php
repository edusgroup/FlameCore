<?php

namespace admin\library\mvc\comp\spl\objItem\category\article;

// Orm
use ORM\comp\spl\objItem\article\article as articleOrm;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class event {
    const NAME = 'article';
    /**
     * Сохранение самой статьи
     */
    const ACTION_SAVE = 'article:save';

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

    // class. event
}