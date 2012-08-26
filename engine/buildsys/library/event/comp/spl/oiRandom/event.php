<?php

namespace buildsys\library\event\comp\spl\oiRandom;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\compContTree;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\comp\spl\oiRandom\oiRandom as oiRandomOrm;
use ORM\comp\spl\oiRandom\oiRandomProp as oiRandomPropOrm;
use ORM\tree\componentTree as componentTreeOrm;
use ORM\tree\compContTree as compContTreeOrm;

// Event comp
use admin\library\mvc\comp\spl\oiRandom\event as eventoiRandom;

// Engine
use core\classes\filesystem;
use core\classes\image\resize;
use core\classes\word;
// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;

// Model
use buildsys\library\event\comp\spl\objItem\model as eventModelObjitem;
use admin\library\mvc\comp\spl\objItem\model as objItemModel;

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

        $objItemCompId = (new componentTreeOrm())->get('id', 'sysname="objItem"');

        // Бегаем по сохранённым группам
        foreach ($contList as $rndObjItemProp) {

            // Получаем подтип objItem и создаём его класс
            $categoryObjItem = $rndObjItemProp['category'];
            $objItemCategory = '\admin\library\mvc\comp\spl\objItem\category\\'.$categoryObjItem.'\builder';
            $objItemCatEvent = new $objItemCategory();

            $oiRandomOrm = new oiRandomOrm();
            // Получаем список детей в выбранной группе
            $childList = $oiRandomOrm->selectList('selContId as contId', 'contId', 'contId=' . $rndObjItemProp['contId']);

            $handleObjitem = eventModelObjitem::objItemChange(
                $pEventBuffer,
                $objItemCatEvent::getTable(),
                $oiRandomOrm,
                new compContTreeOrm(),
                $childList,
                ['order'=>'rand()', 'limit'=>30*$rndObjItemProp['itemsCount']]
            );
            if (!$handleObjitem || $handleObjitem->num_rows == 0) {
                print "ERROR(" . __METHOD__ . "() | Not found Data" . PHP_EOL;
                continue;
            }

            // Директория к данным группы
            $saveDir = 'comp/' . $rndObjItemProp['comp_id'] . '/' . $rndObjItemProp['contId'] . '/';
            $saveDir = DIR::getSiteDataPath($saveDir);

            $listArr = [];
            $arrCount = 1;
			$listCount = 0;
            while ($objItemObj = $handleObjitem->fetch_object()) {
                $listArr[$listCount] = $objItemCatEvent::getOIRandomArray($objItemObj, $objItemCompId, $rndObjItemProp, $listCount, $arrCount);

                if ( $rndObjItemProp['itemsCount'] == $arrCount ){
                    $data = serialize($listArr);
                    filesystem::saveFile($saveDir, 'rnd'.($listCount+1).'.txt', $data);
                    $arrCount = 0;
                    $listArr = [];
                } // if

                ++$arrCount;
				++$listCount;

            }// while

            if ( $listArr ){
				$data = serialize($listArr);
                filesystem::saveFile($saveDir, 'rnd'.($listCount+1).'.txt', $data);
            }

            $data = serialize(['fileNum' => ($listCount+1)]);
            filesystem::saveFile($saveDir, 'data.txt', $data);
        } // foreach ($contList as $rndObj)

        // func. createoiRandom
    }

    // class event
}