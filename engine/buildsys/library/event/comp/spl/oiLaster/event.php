<?php

namespace buildsys\library\event\comp\spl\oiLaster;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\blockItem;
use ORM\comp\spl\objItem\objItem as objItemOrm;
use ORM\blockItemSettings;
use ORM\comp\spl\oiLaster\oiLaster as oiLasterOrm;
use ORM\comp\spl\oiLaster\oiLasterProp as oiLasterPropOrm;
use ORM\tree\compContTree as compContTreeOrm;
use ORM\tree\componentTree as componentTreeOrm;

// Event comp
use admin\library\mvc\comp\spl\oiLaster\event as eventoiLaster;

// Engine
use core\classes\filesystem;
use core\classes\comp as compCore;
use core\classes\admin\dirFunc;

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

        $objItemProp = (new componentTreeOrm())->selectFirst('id, ns', 'sysname="objItem"');

        // Получаем список всех oiLast, которые были созданы
        $contList = (new oiLasterPropOrm())
            ->select('alp.*, cc.comp_id as compId', 'alp')
            ->join(compContTree::TABLE.' cc', 'cc.id=alp.contId')
            ->fetchAll();

        // Бегаем по сохранённым группам oiLast
        foreach( $contList as $oiLasterItemProp ){
            $itemsCount = $oiLasterItemProp['itemsCount'];

            // Получаем список детей в выбранной группе
            // т.е. получаем всех выбранные ветки в дереве objItem, которые мы приозвели
            // при настройке oiList в админке
            $oiLasterOrm = new oiLasterOrm();
            $childList = $oiLasterOrm->selectList(
                'selContId as contId',
                'contId',
                'contId='.$oiLasterItemProp['contId']);

            // Теперь нужно проверить, а есть ли пересечения из выбранных веток в дереве и в
            // буффере event. Вдруг пересечений нет,
            // тогда не данный класс должен обрабатывать текущее событие
            $buffTreeIdList = eventModelObjitem::getBuffTreeIdList(
                    $pEventBuffer,
                    $childList,
                    $oiLasterItemProp['contId'],
                    eventoiLaster::ACTION_SAVE
            );

            $classFile = $oiLasterItemProp['classFile'];
            if ( !$classFile || $classFile == '/base/build.php' ){
                echo "\tioLaster[contId:".$oiLasterItemProp['contId']."] className is default. Abort".PHP_EOL;
                continue;
            }

            // Получаем подтип objItem и создаём его класс
            $className = compCore::fullNameBuildClassAdmin($classFile, $objItemProp['ns']);
            $objItemCatEvent = new $className();
				
            $handleObjitem = eventModelObjitem::objItemChange(
                $pEventBuffer,
                $objItemCatEvent::getTable(),
                $oiLasterOrm,
                new compContTreeOrm(),
                $childList,
				$buffTreeIdList,
                ['limit'=>$itemsCount]
            );
			
			// Выборка представляем ли смысл
            if ( !$handleObjitem ){
                continue;
            }
			// Больше ли данных нуля
			if ( !$handleObjitem->num_rows ){
                echo "\tioLaster[contId:".$oiLasterItemProp['contId']."] not data found. Error".PHP_EOL;
                continue;
            }

            // Директория к данным группы
            $saveDir = 'comp/' . $oiLasterItemProp['compId'] . '/' . $oiLasterItemProp['contId'] . '/';
            $saveDir = dirFunc::getSiteDataPath($saveDir);

            $numRows = $handleObjitem->num_rows;
            echo "\tioLaster[contId:".$oiLasterItemProp['contId']."] Row:$numRows itemC: $itemsCount".PHP_EOL;
            echo "\t$classFile".PHP_EOL;
            echo "\t$saveDir".PHP_EOL.PHP_EOL;

            $listArr = [];
            $listCount = 0;
            while($objItemObj = $handleObjitem->fetch_object()){
                $listArr[$listCount] = $objItemCatEvent::getOILasterArray($objItemObj, $objItemProp['id'], $oiLasterItemProp, $listCount);
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