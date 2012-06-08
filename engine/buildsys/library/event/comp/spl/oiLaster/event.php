<?php

namespace buildsys\library\event\comp\spl\ioLaster;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\comp\spl\ioLaster\ioLaster as ioLasterOrm;
use ORM\comp\spl\ioLaster\ioLasterProp as ioLasterPropOrm;
use ORM\tree\compContTree as compContTreeOrm;
// Event comp
use admin\library\mvc\comp\spl\ioLaster\event as eventioLaster;
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

    public static function createArtLast($pUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }
        //$objItemCompId = (new componentTree())->get('id', 'sysname="objItem"');

        $contList = (new ioLasterPropOrm())
            ->select('alp.contId, alp.itemsCount, cc.comp_id', 'alp')
            ->join(compContTree::TABLE.' cc', 'cc.id=alp.contId')
            ->fetchAll();

        // Бегаем по сохранённым группам
        foreach( $contList as $item ){

            // Директория к данным группы
            $saveDir = 'comp/' . $item['comp_id'] . '/' . $item['contId'] . '/';
            $saveDir = DIR::getSiteDataPath($saveDir);

            $itemsCount = $item['itemsCount'];

            // Получаем список детей в выбранной группе
            $ioLasterOrm = new ioLasterOrm();
            $childList = $ioLasterOrm->selectList(
                'selContId as contId',
                'contId',
                'contId='.$item['contId']);
            $handleObjitem = eventModelObjitem::objItemChange(
                $pEventBuffer,
                $ioLasterOrm,
                new compContTreeOrm(),
                $childList,
                ['limit'=>$itemsCount]);
            if ( $handleObjitem->num_rows == 0){
                return;
            }

            $listArr = [];
            while($objItemItem = $handleObjitem->fetch_object()){
                $url = sprintf($objItemItem->urlTpl, $objItemItem->seoName, $objItemItem->seoUrl);
                $listArr[] = [
                    'caption' => $objItemItem->caption,
                    'id' => $objItemItem->id,
                    'url' => $url,
                    'dateAdd' => $objItemItem->date_add,
                    'prevImgUrl' => $objItemItem->prevImgUrl
                ];
            } // while
            $data = serialize($listArr);
            filesystem::saveFile($saveDir, 'list.txt', $data);
            unset($data);
        } // foreach
        // func. createioLaster
    }

// class event
}