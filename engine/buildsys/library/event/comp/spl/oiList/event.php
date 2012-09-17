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
use core\classes\comp as compCore;
use core\classes\admin\dirFunc;

// Conf
use \DIR;

// Event
use admin\library\mvc\comp\spl\oiList\event as eventOiList;

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

        $objItemProp = (new componentTreeOrm())->selectFirst('id, ns', 'sysname="objItem"');

		// Получаем все oiList, которые заведенены в системе
        $oiListPropList = (new oiListPropOrm())
            ->select('alp.*, cc.comp_id', 'alp')
            ->join(compContTreeOrm::TABLE . ' cc', 'cc.id=alp.contId')
            ->fetchAll();

        // Бегаем по сохранённым oiList
        foreach ($oiListPropList as $oiListPropItem){
            $oiListOrm = new oiListOrm();
			$oiListItemContId = (int)$oiListPropItem['contId'];
            // Получаем список детей в выбранной группе
            // т.е. получаем всех выбранные ветки в дереве objItem, которые мы приозвели
            // при настройке oiList в админке
            $childList = $oiListOrm->selectList(
                'selContId as contId',
                'contId',
                'contId=' . oiListItemContId
            );

            // Теперь нужно проверить, а есть ли пересечения из выбранных веток в дереве и в
            // буффере event. Вдруг пересечений нет,
            // тогда не данный класс должен обрабатывать текущее событие
            $buffTreeIdList = eventModelObjitem::getBuffTreeIdList(
                $pEventBuffer,
                $childList,
                $oiListPropItem['contId'],
                eventOiList::ACTION_SAVE
            );

            $classFile = $oiListPropItem['classFile'];
            if ( !$classFile || $classFile == '/base/build.php' ){
                echo "\tioList[acId:$pAcId contId:$oiListItemContId] className is default. Abort".PHP_EOL;
                continue;
            }

            // Получаем подтип objItem и создаём его класс
            $className = compCore::fullNameBuildClassAdmin($classFile, $objItemProp['ns']);
            $objItemCatEvent = new $className();

            // Тут, мы провереим пересечение, если оно есть, то выбирим все нужные элементы objItem
            $handleObjitem = eventModelObjitem::objItemChange(
                $pEventBuffer,
                $objItemCatEvent::getTable(),
                $oiListOrm,
                new compContTreeOrm(),
                $childList,
				$buffTreeIdList
            );

            // Если данное условие верно, то скорей всего, мы не в той ветке oiList, переходим на след ветку
            if ( !$handleObjitem ){
                continue;
            } // if

            // Если данных нет, то переходим к след обработке oiList
            if ($handleObjitem->num_rows == 0) {
                echo "\tioList[acId:$pAcId contId:$oiListItemContId] Not data found. Error".PHP_EOL;
                continue;
            } // if

            // Директория к данным группы
            $saveDir = 'comp/' . $oiListPropItem['comp_id'] . '/' . $oiListPropItem['contId'] . '/';
            $saveDir = dirFunc::getSiteDataPath($saveDir);

            // Получаем какое должно быть количество объектов в файле
            $itemsCount = $oiListPropItem['itemsCount'];
            $numRows = $handleObjitem->num_rows;
            echo "\tioList[acId:$pAcId contId:$oiListItemContId] Row:$numRows itemC: $itemsCount".PHP_EOL;
            echo "\t$classFile".PHP_EOL;
            echo "\t$saveDir".PHP_EOL.PHP_EOL;

            $categoryBuffer = [];
            $listArr = [];
            $fileNum = 0;
			// Бегаем по всех полученным ItemObj и сохраняем результаты
            while ($objItemItem = $handleObjitem->fetch_object()) {
				// Получаем массив данных, которые нужно сохранить
                $artData = $objItemCatEvent::getOIListArray($objItemItem, $objItemProp['id']);

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
                if ( $oiListPropItem['isCreateCategory'] && count($catBuff['data']) == $itemsCount) {
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
            if ( $oiListPropItem['isCreateCategory'] ){
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