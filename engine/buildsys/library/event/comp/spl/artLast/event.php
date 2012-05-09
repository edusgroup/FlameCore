<?php

namespace buildsys\library\event\comp\spl\artLast;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\comp\spl\artLast\artLast as artLastOrm;
use ORM\comp\spl\artLast\artLastProp as artLastPropOrm;
use ORM\tree\compContTree as compContTreeOrm;
// Event comp
use admin\library\mvc\comp\spl\artLast\event as eventartLast;
use core\classes\filesystem;
// Conf
use \DIR;
// Model
use buildsys\library\event\comp\spl\article\model as eventModelArticle;
use admin\library\mvc\comp\spl\article\model as articleModel;
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
        //$articleCompId = (new componentTree())->get('id', 'sysname="article"');

        $contList = (new artLastPropOrm())
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
            $artLastOrm = new artLastOrm();
            $childList = $artLastOrm->selectList(
                'selContId as contId',
                'contId',
                'contId='.$item['contId']);
            $handleArticle = eventModelArticle::articleChange(
                $pEventBuffer,
                $artLastOrm,
                new compContTreeOrm(),
                $childList,
                ['limit'=>$itemsCount]);
            if ( $handleArticle->num_rows == 0){
                return;
            }

            $listArr = [];
            while($artItem = $handleArticle->fetch_object()){
                $url = sprintf($artItem->urlTpl, $artItem->seoName, $artItem->seoUrl);
                $listArr[] = [
                    'caption' => $artItem->caption,
                    'id' => $artItem->id,
                    'url' => $url,
                    'dateAdd' => $artItem->date_add,
                    'prevImgUrl' => $artItem->prevImgUrl
                ];
            } // while
            $data = serialize($listArr);
            filesystem::saveFile($saveDir, 'list.txt', $data);
            unset($data);
        } // foreach
        // func. createartLast
    }

// class event
}