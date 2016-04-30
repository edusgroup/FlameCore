<?php

namespace admin\library\mvc\comp\spl\objItem\build\photo;

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

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class build implements \admin\library\mvc\comp\spl\objItem\help\builderAbs {

    public static function getTable(){
        return [articleOrm::TABLE];
    }

    public static function getOIListArray($objItemItem, $objItemCompId){
        // Получаем путь до папки, где храняться данные превью
        $loadDir = baseModel::getPath($objItemCompId, $objItemItem->treeId, $objItemItem->id);
        $loadDir = dirFunc::getSiteDataPath($loadDir);

        $previewImgNum = null;
        $count = 0;
        $date = '';
		
		$idSplit = baseModel::getPath($objItemCompId, $objItemItem->treeId, $objItemItem->id);

        if (is_readable($loadDir . 'data.txt')) {
            $data = file_get_contents($loadDir . 'data.txt');
            $data = json_decode($data);
			
			if (isset($data->type) && $data->type === 'new'){
				$data->caption = $objItemItem->caption;
				$data->name = $objItemItem->seoUrl;
				$data->cat = $objItemItem->seoName;
				return $data;				
			} else {
				 return [
					'caption' => $objItemItem->caption,
					'id' => $objItemItem->id,
					'idSplit' => $idSplit,
					'prevImgUrl' => $data->preview,
					'cat' => $objItemItem->seoName,
					'name' => $objItemItem->seoUrl,
					'count' => count($data->photo),
					'date' => $data->date
				];
			}
        }
    }

    public static function getOILasterArray($objItemItem, $objItemCompId, $oiLasterItemProp, $listCount){
        // Получаем путь до папки, где храняться данные превью
        $loadDir = baseModel::getPath($objItemCompId, $objItemItem->treeId, $objItemItem->id);
        $loadDir = dirFunc::getSiteDataPath($loadDir);

        $previewImgNum = null;
        $count = 0;
		
		$idSplit = baseModel::getPath($objItemCompId, $objItemItem->treeId, $objItemItem->id);

        if (is_readable($loadDir . 'data.txt')) {
            $data = file_get_contents($loadDir . 'data.txt');
            $data = json_decode($data);

			if (isset($data->type) && $data->type === 'new'){
				return [
					'caption' => $objItemItem->caption,
					'prevImgUrl' => $data->preview,
					'cat' => $objItemItem->seoName,
					'name' => $objItemItem->seoUrl,
					'count' => $data->end,
					'type' => 'new'
				];
			} else {
				 return [
					'caption' => $objItemItem->caption,
					'prevImgUrl' => $data->preview,
					'cat' => $objItemItem->seoName,
					'name' => $objItemItem->seoUrl,
					'count' => count($data->photo)
				];
			}
        }
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