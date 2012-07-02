<?php

namespace buildsys\library\event\comp\spl\oiPopular;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\comp\spl\oiPopular\oiPopular as oiPopularOrm;
use ORM\comp\spl\oiPopular\oiPopularProp as oiPopularPropOrm;

// Event comp
use admin\library\mvc\comp\spl\oiPopular\event as eventoiPopular;
use ORM\tree\compContTree as compContTreeOrm;
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

        $objItemCompId = (new componentTree())->get('id', 'sysname="objItem"');

        $contList = (new oiPopularPropOrm())
            ->select('alp.*, cc.comp_id', 'alp')
            ->join(compContTree::TABLE . ' cc', 'cc.id=alp.contId')
            ->fetchAll();

        // Бегаем по сохранённым группам
        foreach ($contList as $oiPopularObjItem) {

            // Директория к данным группы
            $saveDir = 'comp/' . $oiPopularObjItem['comp_id'] . '/' . $oiPopularObjItem['contId'] . '/';
            $saveDir = DIR::getSiteDataPath($saveDir);

            $itemsCount = $oiPopularObjItem['itemsCount'];

            // Получаем список детей в выбранной группе
            $oiPopularOrm = new oiPopularOrm();
            $childList = $oiPopularOrm->selectList('selContId as contId', 'contId', 'contId=' . $oiPopularObjItem['contId']);
            $handleObjitem = eventModelObjitem::objItemChange(
                $pEventBuffer,
                $oiPopularOrm,
                new compContTreeOrm(),
                $childList,
                ['order' => 'dayCount desc, RAND()',
                'limit' => $itemsCount]
            );
            if ($handleObjitem && $handleObjitem->num_rows == 0) {
                return;
            }

            $miniDescrHead = '';
            $miniDescrData = '';
            $listArr = [];
            $fileNum = 1;
            while ($objItemObj = $handleObjitem->fetch_object()) {

                $objItemObj = eventModelObjitem::createMiniPreview(
                    $objItemObj,
                    $oiPopularObjItem['contId'],
                    $objItemCompId,
                    $oiPopularObjItem['previewWidth'],
                    $fileNum,
                    $oiPopularObjItem['resizeType'],
                    'oiPopular'
                );

                // ----------------------------------------
                $url = sprintf($objItemObj->urlTpl, $objItemObj->seoName, $objItemObj->seoUrl);
                $listArr[] = [
                    'caption' => $objItemObj->caption,
                    'url' => $url,
                    'prevImgUrl' => $objItemObj->prevImgUrl
                ];

                if ( $oiPopularObjItem['isAddMiniText']){
                    eventModelObjitem::createBinaryMiniDesc($objItemObj, $miniDescrHead, $miniDescrData);
                }
                $fileNum++;
            } // while

            $miniDescrHead = pack('c', $fileNum - 1) . $miniDescrHead;
            $data = $miniDescrHead . $miniDescrData . serialize($listArr);
            filesystem::saveFile($saveDir, 'data.txt', $data);
        } // foreach

        // func. createoiPopular
    }

    // class event
}