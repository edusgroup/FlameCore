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

		// Получаем compId objItem
        $objItemCompId = (new componentTreeOrm())->get('id', 'sysname="objItem"');

        // Получаем список всех oiLast, которые были созданы
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
				
			// Получаем все TreeId которые есть в буффере, это нужно для того
			// что бы понять какие из oiList нужно перегенерить, без этого, генерилось бы 
			// все oiList
			$buffTreeIdList = $pEventBuffer->select('cc.treeId', 'eb')
						 ->join(objItemOrm::TABLE.' cc', 'cc.id=eb.userId')
						 ->group('cc.treeId')
						 ->toList('treeId');	
			// Если данных в $buffTreeIdList нет, то скорей всего было сохранение по настройкам компонента
			if ( !$buffTreeIdList ){
				// Проверяем были ли настройки компонента
				$isSaveProp = $pEventBuffer->selectFirst(
					'id', 
					['eventName'=>eventoiLaster::ACTION_SAVE, 'userId'=>$oiLasterItemProp['contId']]
				);
				if ( $isSaveProp ){
					$buffTreeIdList = $childList;
				} // if $isSaveProp
									
			} // if ( !$buffTreeIdList )
				
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
                //print "ERROR(" . __METHOD__ . "() | Not found Data" . PHP_EOL;
                continue;
            }
			// Больше ли данных нуля
			if ( !$handleObjitem->num_rows ){
                print "ERROR(" . __METHOD__ . "() | Num rows = 0" . PHP_EOL;
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