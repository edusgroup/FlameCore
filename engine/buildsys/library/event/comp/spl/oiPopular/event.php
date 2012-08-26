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

    public static function createObjItemPopular($pUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        $contList = (new oiPopularPropOrm())
            ->select('alp.*, cc.comp_id', 'alp')
            ->join(compContTree::TABLE . ' cc', 'cc.id=alp.contId')
            ->fetchAll();

        $objItemCompId = (new componentTreeOrm())->get('id', 'sysname="objItem"');

        // Бегаем по сохранённым группам
        foreach ($contList as $oiPopularItemProp) {

            // Директория к данным группы
            $saveDir = 'comp/' . $oiPopularItemProp['comp_id'] . '/' . $oiPopularItemProp['contId'] . '/';
            $saveDir = DIR::getSiteDataPath($saveDir);

            $itemsCount = $oiPopularItemProp['itemsCount'];

            // Получаем подтип objItem и создаём его класс
            $categoryObjItem = $oiPopularItemProp['category'];
            $objItemCategory = '\admin\library\mvc\comp\spl\objItem\category\\'.$categoryObjItem.'\builder';
            $objItemCatEvent = new $objItemCategory();

            // Получаем список детей в выбранной группе
            $oiPopularOrm = new oiPopularOrm();
            $childList = $oiPopularOrm->selectList('selContId as contId', 'contId', 'contId=' . $oiPopularItemProp['contId']);
            $handleObjitem = eventModelObjitem::objItemChange(
                $pEventBuffer,
                $objItemCatEvent::getTable(),
                $oiPopularOrm,
                new compContTreeOrm(),
                $childList,
                ['order' => 'dayCount desc, RAND()',
                'limit' => $itemsCount]
            );
            if ($handleObjitem && $handleObjitem->num_rows == 0) {
                print "ERROR(" . __METHOD__ . "() | Not found Data" . PHP_EOL;
                continue;
            }

            $listArr = [];
            $listCount = 0;
            while ($objItemObj = $handleObjitem->fetch_object()) {
                $listArr[$listCount] = $objItemCatEvent::getOIPopularArray($objItemObj, $objItemCompId, $oiPopularItemProp, $listCount);
                ++$listCount;
            } // while

            $data = serialize($listArr);
            filesystem::saveFile($saveDir, 'data.txt', $data);
        } // foreach

        // func. createoiPopular
    }

    // class event
}