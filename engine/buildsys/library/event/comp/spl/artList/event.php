<?php

namespace buildsys\library\event\comp\spl\artList;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\comp\spl\artList\artList as artListOrm;
use ORM\comp\spl\artList\artListProp as artListPropOrm;

// Event comp
use admin\library\mvc\comp\spl\artList\event as eventArtList;
use ORM\tree\compContTree as compContTreeOrm;
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

    public static function createArtList($pUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        $articleCompId = (new componentTree())->get('id', 'sysname="article"');

        $contList = (new artListPropOrm())
            ->select('alp.contId, alp.itemsCount, cc.comp_id', 'alp')
            ->join(compContTree::TABLE . ' cc', 'cc.id=alp.contId')
            ->fetchAll();

        // Бегаем по сохранённым группам
        foreach ($contList as $item) {

            // Директория к данным группы
            $saveDir = 'comp/' . $item['comp_id'] . '/' . $item['contId'] . '/';
            $saveDir = DIR::getSiteDataPath($saveDir);

            // Получаем список детей в выбранной группе
            $artListOrm = new artListOrm();
            $childList = $artListOrm->selectList('selContId as contId', 'contId', 'contId=' . $item['contId']);
            $handleArticle = eventModelArticle::articleChange($pEventBuffer, $artListOrm, new compContTreeOrm(), $childList);
            if (!$handleArticle || $handleArticle->num_rows == 0) {
                return;
            }

            $categoryBuffer = [];

            $itemsCount = $item['itemsCount'];
            //print $itemsCount;
            $listArr = [];
            $fileNum = 0;
            while ($artItem = $handleArticle->fetch_object()) {
                $url = sprintf($artItem->urlTpl, $artItem->seoName, $artItem->seoUrl);
                $artData = [
                    'caption' => $artItem->caption,
                    'id' => $artItem->id,
                    'url' => $url,
                    'idSplit' => articleModel::getPath($articleCompId, $artItem->treeId, $artItem->id),
                    // Название категории, к которой пренадлежит статья
                    'category' => $artItem->category,
                    // Сео название категории
                    'seoName' => $artItem->seoName,
                    'dateAdd' => $artItem->date_add,
                    'prevImgUrl' => $artItem->prevImgUrl
                ];
                $catBuff = &$categoryBuffer[$artItem->treeId];

                $catBuff['data'][] = $artData;
                $listArr[] = $artData;

                if (count($listArr) == $itemsCount) {
                    $data = serialize($listArr);
                    ++$fileNum;
                    $listArr = [];
                    filesystem::saveFile($saveDir, $fileNum . '.txt', $data);
                } // if

                //print $artItem->treeId."\n";
                //print_r($catBuff);

                // Если накопили достаточно, то сохраняем списки по категории
                if (count($catBuff['data']) == $itemsCount) {
                    $catBuff['fileNum'] = isset($catBuff['fileNum']) ? 1 + $catBuff['fileNum'] : 1;
                    $data = serialize($catBuff['data']);
                    filesystem::saveFile($saveDir . $artItem->treeId . '/', $catBuff['fileNum'] . '.txt', $data);
                    //var_dump($catBuff);
                    $catBuff['data'] = [];
                } // if
            } // while
            $data = serialize($listArr);
            filesystem::saveFile($saveDir, ++$fileNum . '.txt', $data);

            $saveData = ['fileCount' => $fileNum];
            $data = \serialize($saveData);
            filesystem::saveFile($saveDir, 'prop.txt', $data);

            // TODO: Слить настройки и списки в один файл, меньше будет обращений к файловой системе
            // Досохраняем данные по категориям и создаём настройки
            foreach ($categoryBuffer as $contId => $categoryData) {
                $fileNum = isset($categoryData['fileNum']) ? 1 + $categoryData['fileNum'] : 1;
                //print $contId.' '.$fileNum.'  '.((boolean)$categoryData['data']).PHP_EOL;
                if ($categoryData['data']) {
                    //++$fileNum;
                    $data = \serialize($categoryData['data']);
                    filesystem::saveFile($saveDir . $contId . '/', $fileNum . '.txt', $data);
                } // if
                $data = \serialize(['fileCount' => $fileNum]);
                filesystem::saveFile($saveDir . $contId . '/', 'prop.txt', $data);
            } // foreach
            unset($data, $categoryBuffer);
        } // foreach

        //echo __METHOD__.' END' . PHP_EOL;

        // func. createArtList
    }

    // class event
}