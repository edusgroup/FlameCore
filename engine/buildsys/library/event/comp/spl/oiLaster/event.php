<?php

namespace buildsys\library\event\comp\spl\oiLaster;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\comp\spl\oiLaster\oiLaster as oiLasterOrm;
use ORM\comp\spl\oiLaster\oiLasterProp as oiLasterPropOrm;
use ORM\tree\compContTree as compContTreeOrm;
use ORM\tree\componentTree as componentTreeOrm;

// Event comp
use admin\library\mvc\comp\spl\oiLaster\event as eventoiLaster;
use core\classes\filesystem;

// Conf
use \DIR;

// Model
use buildsys\library\event\comp\spl\objItem\model as eventModelObjitem;

/**
 * Обработчик событий для меню
 *
 * @author Козленко В.Л.
 */
class event {

    public static function createOIList($pUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        $objItemCompId = (new componentTreeOrm())->get('id', 'sysname="objItem"');

        // Получаем список всех oiLast, по которым были сохранения
        $contList = (new oiLasterPropOrm())
            ->select('alp.*, cc.comp_id as compId', 'alp')
            ->join(compContTree::TABLE.' cc', 'cc.id=alp.contId')
            ->fetchAll();

        // Бегаем по сохранённым группам oiLast
        foreach( $contList as $oiLasterItemProp ){

            // Получаем подтип objItem и создаём его класс
            $categoryObjItem = $oiLasterItemProp['category'];
            $objItemCategory = '\admin\library\mvc\comp\spl\objItem\category\\'.$categoryObjItem.'\builder';
            $objItemCatEvent = new $objItemCategory();

            // Директория к данным группы
            $saveDir = 'comp/' . $oiLasterItemProp['compId'] . '/' . $oiLasterItemProp['contId'] . '/';
            $saveDir = DIR::getSiteDataPath($saveDir);

            $itemsCount = $oiLasterItemProp['itemsCount'];

            // Получаем список детей в выбранной группе
            $oiLasterOrm = new oiLasterOrm();
            $childList = $oiLasterOrm->selectList(
                'selContId as contId',
                'contId',
                'contId='.$oiLasterItemProp['contId']);
            $handleObjitem = eventModelObjitem::objItemChange(
                $pEventBuffer,
                $objItemCatEvent::getTable(),
                $oiLasterOrm,
                new compContTreeOrm(),
                $childList,
                ['limit'=>$itemsCount]
            );
			
            if ( !$handleObjitem || $handleObjitem->num_rows == 0){
                print "ERROR(" . __METHOD__ . "() | Not found Data" . PHP_EOL;
                continue;
            }

            $listArr = [];
            $listCount = 0;
            while($objItemObj = $handleObjitem->fetch_object()){
                $listArr[$listCount] = $objItemCatEvent::getOILasterArray($objItemObj, $objItemCompId, $oiLasterItemProp, $listCount);
                ++$listCount;
            } // while

            $data = serialize($listArr);
            filesystem::saveFile($saveDir, 'data.txt', $data);
            unset($data);
        } // foreach
        // func. createoiLaster

    }

// class event
}