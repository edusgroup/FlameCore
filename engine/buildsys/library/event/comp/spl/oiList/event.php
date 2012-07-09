<?php

namespace buildsys\library\event\comp\spl\oiList;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\comp\spl\oiList\oiList as oiListOrm;
use ORM\comp\spl\oiList\oiListProp as oiListPropOrm;

// Event comp
use admin\library\mvc\comp\spl\oiList\event as eventArtList;
use ORM\tree\compContTree as compContTreeOrm;
use core\classes\filesystem;

// Conf
use \DIR;

// Model
use buildsys\library\event\comp\spl\objItem\model as eventModelObjitem;
use admin\library\mvc\comp\spl\objItem\model as objItemModel;

/**
 * Обработчик событий для меню
 *
 * @author Козленко В.Л.
 */
class event {

    public static function createArtList($pUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        $objItemCompId = (new componentTree())->get('id', 'sysname="objItem"');

        $contList = (new oiListPropOrm())
            ->select('alp.contId, alp.itemsCount, cc.comp_id', 'alp')
            ->join(compContTree::TABLE . ' cc', 'cc.id=alp.contId')
            ->fetchAll();

        // Бегаем по сохранённым группам
        foreach ($contList as $item) {

            // Директория к данным группы
            $saveDir = 'comp/' . $item['comp_id'] . '/' . $item['contId'] . '/';
            $saveDir = DIR::getSiteDataPath($saveDir);

            // Получаем список детей в выбранной группе
            $oiListOrm = new oiListOrm();
            $childList = $oiListOrm->selectList('selContId as contId', 'contId', 'contId=' . $item['contId']);
            $handleObjitem = eventModelObjitem::objItemChange($pEventBuffer, $oiListOrm, new compContTreeOrm(), $childList);
            if (!$handleObjitem || $handleObjitem->num_rows == 0) {
                return;
            }

            $categoryBuffer = [];

            $itemsCount = $item['itemsCount'];
            //print $itemsCount;
            $listArr = [];
            $fileNum = 0;
            while ($objItemItem = $handleObjitem->fetch_object()) {
                $url = sprintf($objItemItem->urlTpl, $objItemItem->seoName, $objItemItem->seoUrl);
                $artData = [
                    'caption' => $objItemItem->caption,
                    'id' => $objItemItem->id,
                    'url' => $url,
                    'idSplit' => objItemModel::getPath($objItemCompId, $objItemItem->treeId, $objItemItem->id),
                    // Название категории, к которой пренадлежит статья
                    'category' => $objItemItem->category,
                    // Сео название категории
                    'seoName' => $objItemItem->seoName,
                    'dateAdd' => $objItemItem->date_add,
                    'prevImgUrl' => $objItemItem->prevImgUrl
                ];
                $catBuff = &$categoryBuffer[$objItemItem->treeId];

                $catBuff['data'][] = $artData;
                $listArr[] = $artData;

                if (count($listArr) == $itemsCount) {
                    $data = serialize($listArr);
                    ++$fileNum;
                    $listArr = [];
                    filesystem::saveFile($saveDir, $fileNum . '.txt', $data);
                } // if

                //print $objItemItem->treeId."\n";
                //print_r($catBuff);

                // Если накопили достаточно, то сохраняем списки по категории
                if (count($catBuff['data']) == $itemsCount) {
                    $catBuff['fileNum'] = isset($catBuff['fileNum']) ? 1 + $catBuff['fileNum'] : 1;
                    $data = serialize($catBuff['data']);
                    filesystem::saveFile($saveDir . $objItemItem->treeId . '/', $catBuff['fileNum'] . '.txt', $data);
                    //var_dump($catBuff);
                    $catBuff['data'] = [];
                } // if
            } // while
			
			// Есть ли что сохранять
			if ( $listArr ){
				$data = serialize($listArr);
				filesystem::saveFile($saveDir, ++$fileNum . '.txt', $data);
			}

            $saveData = ['fileCount' => $fileNum];
            $data = \serialize($saveData);
            filesystem::saveFile($saveDir, 'prop.txt', $data);

            // TODO: Слить настройки и списки в один файл, меньше будет обращений к файловой системе
            // Досохраняем данные по категориям и создаём настройки
            foreach ($categoryBuffer as $contId => $categoryData) {
                $fileNum = isset($categoryData['fileNum']) ? $categoryData['fileNum'] : 0;
                if ($categoryData['data']) {
                    ++$fileNum;
                    $data = \serialize($categoryData['data']);
					//print $fileNum."\n";
                    filesystem::saveFile($saveDir . $contId . '/', $fileNum . '.txt', $data);
                } // if
                $data = \serialize(['fileCount' => $fileNum]);
                filesystem::saveFile($saveDir . $contId . '/', 'prop.txt', $data);
            } // foreach
            unset($data, $categoryBuffer);
        } // foreach

        //echo __METHOD__.' END' . PHP_EOL;

        // func. createArtList
    }

    // class event
}