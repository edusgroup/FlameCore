<?php

namespace buildsys\library\event\comp\spl\oiPopular;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\compContTree;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\comp\spl\oiPopular\oiPopular as oiPopularOrm;
use ORM\comp\spl\oiPopular\oiPopularProp as oiPopularPropOrm;
use ORM\tree\componentTree as componentTreeOrm;
use ORM\tree\compContTree as compContTreeOrm;

// Event comp
use admin\library\mvc\comp\spl\oiPopular\event as eventoiPopular;

// Engine
use core\classes\filesystem;
use core\classes\comp as compCore;
use core\classes\admin\dirFunc;

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

    public static function createObjItemPopular($pUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        $objItemProp = (new componentTreeOrm())->selectFirst('id, ns', 'sysname="objItem"');

        $contList = (new oiPopularPropOrm())
            ->select('alp.*, cc.comp_id', 'alp')
            ->join(compContTree::TABLE . ' cc', 'cc.id=alp.contId')
            ->fetchAll();

        // Бегаем по сохранённым группам
        foreach ($contList as $oiPopularItemProp) {
            $oiPopularItemContId = (int)$oiPopularItemProp['contId'];
            // Получаем список детей в выбранной группе
            // т.е. получаем всех выбранные ветки в дереве objItem, которые мы приозвели
            // при настройке oiList в админке
            $oiPopularOrm = new oiPopularOrm();
            $childList = $oiPopularOrm->selectList(
                'selContId as contId',
                'contId',
                'contId=' . $oiPopularItemContId
            );

            // Теперь нужно проверить, а есть ли пересечения из выбранных веток в дереве и в
            // буффере event. Вдруг пересечений нет,
            // тогда не данный класс должен обрабатывать текущее событие
            $buffTreeIdList = eventModelObjitem::getBuffTreeIdList(
                $pEventBuffer,
                $childList,
                $oiPopularItemContId,
                eventoiPopular::ACTION_SAVE
            );

            $classFile = $oiPopularItemProp['classFile'];
            if ( !$classFile || $classFile == '/base/build.php' ){
                echo "\tioPopular[contId:$oiPopularItemContId] className is default. Abort".PHP_EOL;
                continue;
            }

            // Получаем подтип objItem и создаём его класс
            $className = compCore::fullNameBuildClassAdmin($classFile, $objItemProp['ns']);
            $objItemCatEvent = new $className();

            $itemsCount = $oiPopularItemProp['itemsCount'];

            $advField = ['order' => 'dayCount desc, RAND()', 'limit' => $itemsCount];
            if ( method_exists($objItemCatEvent, 'setAdvField')){
                $advField = $objItemCatEvent->setAdvField($advField);
            } // if

            $handleObjitem = eventModelObjitem::objItemChange(
                $pEventBuffer,
                $objItemCatEvent::getTable(),
                $oiPopularOrm,
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
                echo "\tioPopular[contId:$oiPopularItemContId] not data found. Abort".PHP_EOL;
                continue;
            }

            // Директория к данным группы
            $saveDir = 'comp/' . $oiPopularItemProp['comp_id'] . '/' . $oiPopularItemContId . '/';
            $saveDir = dirFunc::getSiteDataPath($saveDir);

            $numRows = $handleObjitem->num_rows;
            echo "\tioPopular[contId:$oiPopularItemContId] Row:$numRows itemC: $itemsCount".PHP_EOL;
            echo "\t$classFile".PHP_EOL;
            echo "\t$saveDir".PHP_EOL.PHP_EOL;

            $listArr = [];
            $listCount = 0;
            while ($objItemObj = $handleObjitem->fetch_object()) {
                $listArr[$listCount] = $objItemCatEvent::getOIPopularArray($objItemObj, $objItemProp['id'], $oiPopularItemProp, $listCount);
                ++$listCount;
            } // while

            $data = serialize($listArr);
            filesystem::saveFile($saveDir, 'data.txt', $data);
        } // foreach

        // func. createoiPopular
    }

    // class event
}