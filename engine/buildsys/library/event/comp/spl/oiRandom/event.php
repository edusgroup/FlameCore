<?php

namespace buildsys\library\event\comp\spl\oiRandom;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\comp\spl\oiRandom\oiRandom as oiRandomOrm;
use ORM\comp\spl\oiRandom\oiRandomProp as oiRandomPropOrm;

// Event comp
use admin\library\mvc\comp\spl\oiRandom\event as eventoiRandom;
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

        // Бегаем по сохранённым группам
        foreach ($contList as $rndObj) {
            // Получаем список детей в выбранной группе
            $oiRandomOrm = new oiRandomOrm();
            $childList = $oiRandomOrm->selectList('selContId as contId', 'contId', 'contId=' . $rndObj['contId']);
            $handleObjitem = eventModelObjitem::objItemChange(
                $pEventBuffer,
                $oiRandomOrm,
                new compContTreeOrm(),
                $childList,
                ['order'=>'rand()', 'limit'=>100]
            );
            if (!$handleObjitem || $handleObjitem->num_rows == 0) {
                return;
            }

            // Директория к данным группы
            $saveDir = 'comp/' . $rndObj['comp_id'] . '/' . $rndObj['contId'] . '/';
            $saveDir = DIR::getSiteDataPath($saveDir);


            $miniDescrHead = '';
            $miniDescrData = '';
            $listArr = [];
            $arrCount = 1;
            $fileNum = 1;
            while ($objItemObj = $handleObjitem->fetch_object()) {

                if ( $rndObj['isCreatePreview']){
                    // Создаём превью
                    $objItemObj = eventModelObjitem::createMiniPreview(
                        $objItemObj,
                        $rndObj['contId'],
                        $rndObj['comp_id'],
                        $rndObj['previewWidth'],
                        $arrCount,
                        $rndObj['resizeType']
                    );
                } // if isCreatePreview

                // ----------------------------------------
                $url = sprintf($objItemObj->urlTpl, $objItemObj->seoName, $objItemObj->seoUrl);
                $listArr[] = [
                    'caption' => $objItemObj->caption,
                    'url' => $url,
                    'prevImgUrl' => $objItemObj->prevImgUrl
                ];

                if ( $rndObj['isAddMiniText']){
                    eventModelObjitem::createBinaryMiniDesc($objItemObj, $miniDescrHead, $miniDescrData);
                }

                if ( $rndObj['itemsCount'] == $arrCount ){
                    $miniDescrHead = pack('c', $arrCount - 1) . $miniDescrHead;
                    $data = $miniDescrHead . $miniDescrData . serialize($listArr);
                    filesystem::saveFile($saveDir, 'rnd'.$fileNum.'.txt', $data);
                    ++$fileNum;
                    $arrCount = 0;
                    $miniDescrHead = '';
                    $miniDescrData = '';
                    $listArr = [];
                } // if

                $arrCount++;

            }// while

            if ( $listArr ){
                $miniDescrHead = pack('c', $arrCount - 1) . $miniDescrHead;
                $data = $miniDescrHead . $miniDescrData . serialize($listArr);
                filesystem::saveFile($saveDir, 'rnd'.$fileNum.'.txt', $data);
            }

            $data = serialize(['fileNum' => $fileNum]);
            filesystem::saveFile($saveDir, 'data.txt', $data);
        } // foreach ($contList as $rndObj)

        exit;

        // func. createoiRandom
    }

    // class event
}