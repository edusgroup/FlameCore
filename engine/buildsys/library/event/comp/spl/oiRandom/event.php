<?php

namespace buildsys\library\event\comp\spl\oiRandom;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\compContTree;
use ORM\comp\spl\oiRandom\oiRandom as oiRandomOrm;
use ORM\comp\spl\oiRandom\oiRandomProp as oiRandomPropOrm;
use ORM\tree\componentTree as componentTreeOrm;
use ORM\tree\compContTree as compContTreeOrm;

// Event comp
use admin\library\mvc\comp\spl\oiRandom\event as eventoiRandom;

// Engine
use core\classes\filesystem;
use core\classes\comp as compCore;
use core\classes\admin\dirFunc;

// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;

// Model
use buildsys\library\event\comp\spl\objItem\model as eventModelObjitem;

/**
 * Обработчик событий для меню
 *
 * @author Козленко В.Л.
 */
class event {

    public static function createFile($pUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        $contList = (new oiRandomPropOrm())
            ->select('alp.*, cc.comp_id', 'alp')
            ->join(compContTree::TABLE . ' cc', 'cc.id=alp.contId')
            ->fetchAll();

        $objItemProp = (new componentTreeOrm())->selectFirst('id, ns', 'sysname="objItem"');

        // Бегаем по сохранённым группам
        foreach ($contList as $rndObjItemProp) {
            $oiRandomOrm = new oiRandomOrm();
            // Получаем список детей в выбранной группе
            // т.е. получаем всех выбранные ветки в дереве objItem, которые мы приозвели
            // при настройке oiList в админке
            $childList = $oiRandomOrm->selectList(
                'selContId as contId',
                'contId',
                'contId=' . $rndObjItemProp['contId']
            );

            // Теперь нужно проверить, а есть ли пересечения из выбранных веток в дереве и в
            // буффере event. Вдруг пересечений нет,
            // тогда не данный класс должен обрабатывать текущее событие
            $buffTreeIdList = eventModelObjitem::getBuffTreeIdList(
                $pEventBuffer,
                $childList,
                $rndObjItemProp['contId'],
                eventoiRandom::ACTION_SAVE
            );

            $classFile = $rndObjItemProp['classFile'];
            if ( !$classFile || $classFile == '/base/build.php' ){
                echo "\tioRandom[contId:".$rndObjItemProp['contId']."] className is default. Abort".PHP_EOL;
                continue;
            }

            // Получаем подтип objItem и создаём его класс
            $className = compCore::fullNameBuildClassAdmin($classFile, $objItemProp['ns']);
            $objItemCatEvent = new $className();

            $maxItemsCount = (int)$rndObjItemProp['itemsCount'];

            $advField = ['order' => 'rand()', 'limit' => 30 * $maxItemsCount];
            if ( method_exists($objItemCatEvent, 'setAdvField')){
                $advField = $objItemCatEvent->setAdvField($advField);
            } // if

            // число 30 взято набом, в целом нужно взять блок, побольше, его посортировать и записать
            $handleObjitem = eventModelObjitem::objItemChange(
                $pEventBuffer,
                $objItemCatEvent::getTable(),
                $oiRandomOrm,
                new compContTreeOrm(),
                $childList,
                $buffTreeIdList,
                $advField
            );

            // Выборка представляем ли смысл
            if (!$handleObjitem) {
                continue;
            }

            if ($handleObjitem->num_rows == 0) {
                echo "\tioRandom[contId:" . $rndObjItemProp['contId'] . "] not data found. Error" . PHP_EOL;
                continue;
            }

            // Директория к данным группы
            $saveDir = 'comp/' . $rndObjItemProp['comp_id'] . '/' . $rndObjItemProp['contId'] . '/';
            $saveDir = dirFunc::getSiteDataPath($saveDir);

            $numRows = $handleObjitem->num_rows;

            echo "\tioRandom[contId:" . $rndObjItemProp['contId'] . "] Row:$numRows itemC: $maxItemsCount" . PHP_EOL;
            echo "\t$classFile".PHP_EOL;
            echo "\t$saveDir" . PHP_EOL . PHP_EOL;

            //$listArr = [];
            //$arrCount = 1;
            $fileNum = 0;
			
			$listArticleData = [];
			// Бегаем по статьям и получаем их ID
            while ($objItemObj = $handleObjitem->fetch_object()) {
				// Получаем, какие поля необходимо записать в файлы
				$listArticleData[] = $objItemCatEvent::getOIRandomArray($objItemObj, $objItemProp['id'], $rndObjItemProp, null, $fileNum);
				if ( count($listArticleData) == $maxItemsCount ){
					$data = serialize($listArticleData);
                    filesystem::saveFile($saveDir, 'rnd' . $fileNum . '.txt', $data);
					$listArticleData = [];
					++$fileNum;
				} // if
            } // while

            if ($listArticleData) {
                $data = serialize($listArticleData);
                filesystem::saveFile($saveDir, 'rnd' . $fileNum . '.txt', $data);
            } // if

            $data = serialize(['fileNum' => $fileNum]);
            filesystem::saveFile($saveDir, 'data.txt', $data);
        } // foreach ($contList as $rndObj)

		//exit;
        // func. createoiRandom
    }

    // class event
}