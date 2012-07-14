<?php

namespace buildsys\library\event\comp\spl\oiList;

// ORM
use ORM\event\eventBuffer;
use ORM\comp\spl\objItem\objItem as objItemOrm;
use ORM\comp\spl\oiList\oiList as oiListOrm;
use ORM\comp\spl\oiList\oiListProp as oiListPropOrm;
use ORM\tree\compContTree as compContTreeOrm;
use ORM\tree\componentTree as componentTreeOrm;

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

    public static function createOIList($pUserData, eventBuffer $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }
		echo 'oiList::createList'.PHP_EOL;
        $objItemCompId = (new componentTreeOrm())->get('id', 'sysname="objItem"');

		// Получаем все oiList, которые заведенены в системе
        $contList = (new oiListPropOrm())
            ->select('alp.*, cc.comp_id', 'alp')
            ->join(compContTreeOrm::TABLE . ' cc', 'cc.id=alp.contId')
            ->fetchAll();
			
		// Получаем все TreeId которые есть в буффере, это нужно для того
		// что бы понять какие из oiList нужно перегенерить, без этого, генерилось бы 
		// все oiList
		$buffTreeIdList = $pEventBuffer->select('cc.treeId', 'eb')
		  			 ->join(objItemOrm::TABLE.' cc', 'cc.id=eb.userId')
				     ->group('cc.treeId')
					 ->toList('treeId');

        // Бегаем по сохранённым oiList
        foreach ($contList as $oiListItemProp){

			// Получаем подтип objItem и создаём его класс
            $categoryObjItem = $oiListItemProp['category'];
            $objItemCategory = '\admin\library\mvc\comp\spl\objItem\category\\'.$categoryObjItem.'\builder';
            $objItemCatEvent = new $objItemCategory();
			
			echo "\tContId: {$oiListItemProp['contId']} $categoryObjItem".PHP_EOL;

            // Получаем список детей в выбранной группе
            $oiListOrm = new oiListOrm();
            $childList = $oiListOrm->selectList('selContId as contId', 'contId', 'contId=' . $oiListItemProp['contId']);
            $handleObjitem = eventModelObjitem::objItemChange(
                $pEventBuffer,
                $objItemCatEvent::getTable(),
                $oiListOrm,
                new compContTreeOrm(),
                $childList,
				$buffTreeIdList
            ); // eventModelObjitem::objItemChange

			// Директория к данным группы
            $saveDir = 'comp/' . $oiListItemProp['comp_id'] . '/' . $oiListItemProp['contId'] . '/';
            $saveDir = DIR::getSiteDataPath($saveDir);
			
			// Если данное условие верно, то скорей всего, мы не в той ветке oiList, переходим на след ветку
			if ( !$handleObjitem ){
				continue;
			}

            // Если данных нет, то переходим к след обработке oiList
            if ($handleObjitem->num_rows == 0) {
                print "ERROR(" . __METHOD__ . "() | Not found Data" . PHP_EOL;
                continue;
            } // if
			
			echo "\t\tNumRows: ".$handleObjitem->num_rows.PHP_EOL;

            $categoryBuffer = [];

            // Получаем какое должно быть количество объектов в файле
            $itemsCount = $oiListItemProp['itemsCount'];
			echo "\t\tItemsCount: ".$itemsCount.PHP_EOL;
            $listArr = [];
            $fileNum = 0;
			// Бегаем по всех полученным ItemObj и сохраняем результаты
            while ($objItemItem = $handleObjitem->fetch_object()) {
				// Получаем массив данных, которые нужно сохранить
                $artData = $objItemCatEvent::getOIListArray($objItemItem, $objItemCompId);

                $catBuff = &$categoryBuffer[$objItemItem->treeId];

                $catBuff['data'][] = $artData;
                $listArr[] = $artData;

				// Если накопилось нужно количество данных, сохраняем
                if (count($listArr) == $itemsCount) {
                    $data = serialize($listArr);
                    ++$fileNum;
                    $listArr = [];
                    filesystem::saveFile($saveDir, $fileNum . '.txt', $data);
                } // if

                // Если накопили достаточно, то сохраняем списки по категории
                if ( $oiListItemProp['isCreateCategory'] && count($catBuff['data']) == $itemsCount) {
                    $catBuff['fileNum'] = isset($catBuff['fileNum']) ? 1 + $catBuff['fileNum'] : 1;
                    $data = serialize($catBuff['data']);
                    filesystem::saveFile($saveDir . $objItemItem->treeId . '/', $catBuff['fileNum'] . '.txt', $data);
                    $catBuff['data'] = [];
                } // if
            } // while
			
			// Есть ли что сохранять
			if ( $listArr ){
				$data = serialize($listArr);
				++$fileNum;
				filesystem::saveFile($saveDir, $fileNum . '.txt', $data);
			}

            $saveData = ['fileCount' => $fileNum];
            $data = \serialize($saveData);
            filesystem::saveFile($saveDir, 'prop.txt', $data);

            // TODO: Слить настройки и списки в один файл, меньше будет обращений к файловой системе
            // Досохраняем данные по категориям и создаём настройки
            if ( $oiListItemProp['isCreateCategory'] ){
                foreach ($categoryBuffer as $contId => $categoryData) {
                    $fileNum = isset($categoryData['fileNum']) ? $categoryData['fileNum'] : 0;
                    if ($categoryData['data']) {
                        ++$fileNum;
                        $data = \serialize($categoryData['data']);
                        filesystem::saveFile($saveDir . $contId . '/', $fileNum . '.txt', $data);
                    } // if
                    $data = \serialize(['fileCount' => $fileNum]);
                    filesystem::saveFile($saveDir . $contId . '/', 'prop.txt', $data);
                } // foreach
            } // if $oiListItemProp['isCreateCategory']
            unset($data, $categoryBuffer);
        } // foreach

        // func. createOIList
    }

    // class event
}