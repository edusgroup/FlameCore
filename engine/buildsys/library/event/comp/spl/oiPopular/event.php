<?php

namespace buildsys\library\event\comp\spl\oiPopular;

// ORM
use ORM\event\eventBuffer;
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
                print "ERROR(" . __METHOD__ . "() | Not found Data" . PHP_EOL;
                continue;
            }

            $listArr = [];
            $fileNum = 1;
            $listCount = 0;
            while ($objItemObj = $handleObjitem->fetch_object()) {

                if ( $oiPopularObjItem['isCreatePreview']){
                    // Создаём превью
                    $objItemObj = eventModelObjitem::createMiniPreview(
                        $objItemObj,
                        $oiPopularObjItem['contId'],
                        $oiPopularObjItem['comp_id'],
                        $oiPopularObjItem['previewWidth'],
                        $fileNum,
                        $oiPopularObjItem['resizeType']
                    );
                } // if isCreatePreview

                // ----------------------------------------
                $url = sprintf($objItemObj->urlTpl, $objItemObj->seoName, $objItemObj->seoUrl);
                $listArr[] = [
                    'caption' => $objItemObj->caption,
                    'url' => $url,
                    'prevImgUrl' => $objItemObj->prevImgUrl,
					'miniDesck' => ''
                ];

                if ( $oiPopularObjItem['isAddMiniText']){
					$objItemDataDir = objItemModel::getPath($objItemObj->compId, $objItemObj->treeId, $objItemObj->id);
					$miniDescrFile = DIR::getSiteDataPath($objItemDataDir) . 'minidescr.txt';
					if (is_readable($miniDescrFile)) {
						$listArr[$listCount]['miniDesck'] = file_get_contents($miniDescrFile);
					}
                } // if ( isAddMiniText )
				
                ++$fileNum;
                ++$listCount;
            } // while

            $data = serialize($listArr);
            filesystem::saveFile($saveDir, 'data.txt', $data);
        } // foreach

        // func. createoiPopular
    }

    // class event
}