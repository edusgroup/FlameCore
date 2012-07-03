<?php

namespace buildsys\library\event\comp\spl\oiLaster;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\comp\spl\oiLaster\oiLaster as oiLasterOrm;
use ORM\comp\spl\oiLaster\oiLasterProp as oiLasterPropOrm;
use ORM\tree\compContTree as compContTreeOrm;
// Event comp
use admin\library\mvc\comp\spl\oiLaster\event as eventoiLaster;
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

    public static function createOIList($pUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        // Получаем список всех oiLast, по которым были сохранения
        $contList = (new oiLasterPropOrm())
            ->select('alp.*, cc.comp_id', 'alp')
            ->join(compContTree::TABLE.' cc', 'cc.id=alp.contId')
            ->fetchAll();

        // Бегаем по сохранённым группам oiLast
        foreach( $contList as $oiLasterItemProp ){

            // Директория к данным группы
            $saveDir = 'comp/' . $oiLasterItemProp['comp_id'] . '/' . $oiLasterItemProp['contId'] . '/';
            $saveDir = DIR::getSiteDataPath($saveDir);

            $itemsCount = $oiLasterItemProp['itemsCount'];

            // Получаем список детей в выбранной группе
            $oiLasterOrm = new oiLasterOrm();
            $childList = $oiLasterOrm->selectList(
                'selContId as contId',
                'contId',
                'contId='.$oiLasterItemProp['contId']);
            $handleObjitem = eventModelObjitem::objItemChange(
                $pEventBuffer,
                $oiLasterOrm,
                new compContTreeOrm(),
                $childList,
                ['limit'=>$itemsCount]);
            if ( $handleObjitem->num_rows == 0){
                return;
            }

            $miniDescrHead = '';
            $miniDescrData = '';
            $listArr = [];
            $fileNum=1;
            while($objItemObj = $handleObjitem->fetch_object()){

                if ( $oiLasterItemProp['isCreatePreview']){
                    // Создаём превью
                    $objItemObj = eventModelObjitem::createMiniPreview(
                        $objItemObj,
                        $oiLasterItemProp['contId'],
                        $oiLasterItemProp['comp_id'],
                        $oiLasterItemProp['previewWidth'],
                        $fileNum,
                        $oiLasterItemProp['resizeType']
                    );
                } // if isCreatePreview

                $url = sprintf($objItemObj->urlTpl, $objItemObj->seoName, $objItemObj->seoUrl);
                $listArr[] = [
                    'caption' => $objItemObj->caption,
                    'id' => $objItemObj->id,
                    'url' => $url,
                    'dateAdd' => $objItemObj->date_add,
                    'prevImgUrl' => $objItemObj->prevImgUrl
                ];

                if ( $oiLasterItemProp['isAddMiniText']){
                    eventModelObjitem::createBinaryMiniDesc($objItemObj, $miniDescrHead, $miniDescrData);
                }

                ++$fileNum;
            } // while

            $miniDescrHead = pack('c', $fileNum - 1) . $miniDescrHead;
            $data = $miniDescrHead . $miniDescrData . serialize($listArr);
            //print $saveDir."\n";
            filesystem::saveFile($saveDir, 'data.txt', $data);
            unset($data);
        } // foreach
        // func. createoiLaster
    }

// class event
}