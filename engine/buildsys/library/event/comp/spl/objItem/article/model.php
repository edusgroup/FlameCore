<?php

namespace buildsys\library\event\comp\spl\objItem\article;

// ORM
use ORM\comp\spl\objItem\objItem as objItemOrm;
use ORM\comp\spl\objItem\article\article as articleOrm;
use core\classes\DB\tree;
use ORM\tree\compContTree;
use ORM\comp\spl\objItem\objItemProp;

// Conf
use \DIR;

// Engine
use core\classes\filesystem;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;

// Event
use admin\library\mvc\comp\spl\objItem\event as eventObjItem;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class model {
    public static function urlTplChange($pListerUserData, $pEventBuffer, $pEventList) {
        // Если были какие либо новые сохранениея, то у них пустой urlTpl
        $isArctileSave = $pEventBuffer->selectFirst(
            'id',
            ['eventName' => eventObjItem::ACTION_TABLE_SAVE]
        );
        $objItemOrm = new objItemOrm();
        // Если произошло сохранение
        if ($isArctileSave) {

            $idList = $objItemOrm->select('i.treeId', 'i')
                ->joinLeftOuter(articleOrm::TABLE.' a', 'a.itemObjId=i.id')
                ->where('a.urlTpl = ""')
                ->group('i.treeId')
                ->toList('i.treeId');

            foreach ($idList as $contId) {
                $urlList = (new tree())->getTreeUrlById(compContTree::TABLE, (int)$contId);
                $urlList = array_map(function($pItem) {
                    return $pItem['id'];
                }, $urlList);
                $urlList = implode('","', $urlList);
                // Находим ближайший для нас шаблон
                $data = (new objItemProp())->sql('SELECT ap.contId, ap.url FROM ' . objItemProp::TABLE . ' ap
                                JOIN ( SELECT max(contId) contId FROM ' . objItemProp::TABLE . ' WHERE contId IN ("' . $urlList . '")
                                        AND url != "" ) jn ON jn.contId = ap.contId#' . __METHOD__)
                    ->fetchFirst();
                $urlTpl = $data['url'];
                /*$objItemOrm->update(
                    ['urlTpl' => $urlTpl, 'urlTplContId' => $data['contId']],
                    'treeId=' . $contId
                );*/
                // Обновляем шаблоны ссылки и какой ветке настроек шаблон пренадлежит
                $objItemOrm->sql(
                    'UPDATE '.articleOrm::TABLE.' a '.
                    'JOIN '.$objItemOrm::TABLE.' i '.
                    'ON i.id=a.itemObjId '.
                    'SET a.urlTpl="'. $urlTpl.'", a.urlTplContId='.$data['contId'].' '.
                    'WHERE a.treeId='.$contId.' '.
                    '#'.__METHOD__
                )->query();

            } // foreach
            unset($idList, $data);
        } // if Если это сохранение

        // Изменение шаблона в кастом настройках
        $isSettChange = $pEventBuffer->selectFirst(
            'id',
            ['eventName' => eventObjItem::ACTOIN_CUSTOM_PROP_SAVE]
        );
        if ($isSettChange) {
            $compContTree = new compContTree();
            $idList = $pEventBuffer
                ->select('userId', 'eb')
                ->where(['eventName' => eventObjItem::ACTOIN_CUSTOM_PROP_SAVE])
                ->group('userId')
                ->toList('userId');
            foreach ($idList as $contId) {
                $urlList = (new tree())->getTreeUrlById(compContTree::TABLE, (int)$contId);
                $urlList = array_map(function($pItem) {
                    return $pItem['id'];
                }, $urlList);
                $urlList = implode('","', $urlList);
                $data = (new objItemProp())
                    ->sql('SELECT ap.contId, ap.url FROM ' . objItemProp::TABLE . ' ap
                           JOIN ( SELECT max(contId) contId FROM ' . objItemProp::TABLE . ' WHERE contId IN ("' . $urlList . '")
                               AND url != "" ) jn ON jn.contId = ap.contId#' . __METHOD__)
                    ->fetchFirst();
                $urlTpl = $data['url'];
                self::_rSetUrlTpl($compContTree, $objItemOrm, $contId, $data['contId'], $urlTpl);
            } // foreach
        } // if
        // func. urlTplChange
    }

    /**
     * Безопастное переименовываение статей
     * @static
     * @param $pListerUserData
     * @param $pEventBuffer
     * @param $pEventList
     * @return mixed
     */
    public static function renameSafe($pListerUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }
        // Переименовывание
        (new objItemOrm())->update(
            'seoUrl = seoUrlTmp, seoUrlTmp = ""',
            'seoUrlTmp != ""'
        );
        // func. renameSafe
    }


    private static function _modifyDataInfo($prevData, $pCompId, $pContId, &$objItemData, $direction, $nextData, $undirection) {
        // Если что обрабатывать с предыдущей ссылкой
        if ($prevData) {
            // Обработка предыщуей статьи
            $saveDir = objItemModel::getPath($pCompId, $pContId, $prevData['id']);
            $saveDir = DIR::getSiteDataPath($saveDir);

            $data = $prevData;
            if (is_file($saveDir . 'info.txt')) {
                $data = file_get_contents($saveDir . 'info.txt');
                $data = \unserialize($data);
            }

            if ($objItemData['isPublic'] == 'yes') {
                // Добавляем в данные текущей статьи
                $objItemData[$undirection] = [
                    'url' => sprintf($prevData['urlTpl'], $prevData['seoName'], $prevData['seoUrl']),
                    'caption' => $prevData['caption'],
                    'prevId' => $prevData['id']
                ];

                // Добавляем в данные предыдущей статьи
                $data[$direction] = [
                    'id' => $objItemData['id'],
                    'caption' => $objItemData['caption'],
                    'url' => $objItemData['canonical']
                ];
            } else {
                $data[$direction] = [
                    'id' => $nextData['id'],
                    'caption' => $nextData['caption'],
                    'url' => sprintf($nextData['urlTpl'], $nextData['seoName'], $nextData['seoUrl']),
                ];
            } // if $objItemData['isPublic']

            $data = serialize($data);
            filesystem::saveFile($saveDir, 'info.txt', $data);
        } // if $prevData
    }

    private static function _rSetUrlTpl($compContTree, $objItemOrm, $pContId, $purlTplContId, $pUrlTpl) {
        $objItemOrm->sql(
            'UPDATE '.articleOrm::TABLE.' a '.
                'JOIN '.$objItemOrm::TABLE.' i '.
                'ON i.id=a.itemObjId '.
                'SET a.urlTpl="'. $pUrlTpl.'", a.urlTplContId='.$purlTplContId.' '.
                'WHERE a.treeId='.$pContId.' '.
                '#'.__METHOD__
        )->query();

        $handleArt = $objItemOrm
            ->select('a.id, cc.id contId, cc.comp_id compId', 'a')
            ->join(compContTree::TABLE . ' cc', 'cc.id = a.treeId')
            ->query();
        while ($item = $handleArt->fetch_object()) {
            self::saveDataInfo($item->id, $objItemOrm, $item->compId, $item->contId);
        } // while

        // Получаем детей с пустым UrlTpl
        $childList = $compContTree
            ->select('cc.id', 'cc')
            ->joinLeftOuter(objItemProp::TABLE . ' ap', 'ap.contId = cc.id  and ap.url = ""')
            ->where('cc.tree_Id =' . $pContId)
            ->comment(__METHOD__)
            ->toList('id');
        // Бегаем по детям, проставляем UrlTpl
        foreach ($childList as $contId) {
            self::_rSetUrlTpl($compContTree, $objItemOrm, $contId, $purlTplContId, $pUrlTpl);
        }
        // func. setUrlTpl
    }

    public static function createObjItemInfo($pListerUserData, $pEventBuffer, $pEventList) {
        $objItemOrm = new objItemOrm();
        $dataList = $pEventBuffer
            ->select('a.id, cc.comp_Id compId, cc.id contId', 'eb')
            ->join(objItemOrm::TABLE . ' a', 'a.id=eb.userId')
            ->join(compContTree::TABLE . ' cc', 'cc.id = a.treeId')
            ->where('eb.eventName in (' . $pEventList . ')')
            ->group('eb.userId')
            ->order('eb.userId')
            ->comment(__METHOD__)
            ->fetchAll();
        foreach ($dataList as $item) {
            $itemId = (int)$item['id'];
            $itemCompId = (int)$item['compId'];
            $itemContId = (int)$item['contId'];
            self::saveDataInfo($itemId, $objItemOrm, $itemCompId, $itemContId);
        } // foreach
        // func. createObjitemInfo
    }

    public static function saveDataInfo(integer $pId, objItemOrm $pObjItemOrm, integer $pCompId, integer $pContId) {

        $objItemDirData = objItemModel::getPath($pCompId, $pContId, $pId);
        $objItemDirData = DIR::getSiteDataPath($objItemDirData);

        // Получаем все данные по статье
        $objItemData = $pObjItemOrm
            ->select('i.id, i.seoUrl, i.treeId, i.caption, a.prevImgUrl, i.isPublic'
                         . ',cc.seoName, cc.name category, a.seoKeywords, a.seoDescr, a.isCloaking'
                         . ',DATE_FORMAT(i.date_add, "%Y-%m-%dT%h:%i+04:00") as dateISO8601'
                         . ',DATE_FORMAT(i.date_add, "%d.%m.%y %H:%i") date_add, i.date_add dateunf, a.urlTpl', 'i')
            ->joinLeftOuter(articleOrm::TABLE. ' a', 'i.id=a.itemObjId')
            ->join(compContTree::TABLE . ' cc', 'i.treeId=cc.id')
            ->where('i.id=' . $pId)
            ->comment(__METHOD__)
            ->fetchFirst();

        // Данные предыдушей статьи
        $prevData = $pObjItemOrm
            ->select('i.id, i.seoUrl, i.caption, cc.seoName, a.urlTpl', 'i')
            ->joinLeftOuter(articleOrm::TABLE. ' a', 'i.id=a.itemObjId')
            ->join(compContTree::TABLE . ' cc', 'i.treeId=cc.id')
            ->where(
            'date("' . $objItemData['dateunf'] . ' ") >= date(i.date_add)
                AND i.isPublic = "yes"
                AND i.isDel = 0
                And i.treeId = ' . $objItemData['treeId'] . '
                AND i.id < ' . $objItemData['id'])
            ->order('i.date_add DESC, i.id desc')
            ->fetchFirst();

        // Данные следующей статьи
        $nextData = $pObjItemOrm
            ->select('i.id, i.seoUrl, i.caption, cc.seoName, a.urlTpl', 'i')
            ->joinLeftOuter(articleOrm::TABLE. ' a', 'i.id=a.itemObjId')
            ->join(compContTree::TABLE . ' cc', 'i.treeId=cc.id')
            ->where(
            'date("' . $objItemData['dateunf'] . ' ") <= date(i.date_add)
                AND i.isPublic = "yes"
                AND i.isDel = 0
                And i.treeId = ' . $objItemData['treeId'] . '
                AND i.id > ' . $objItemData['id'])
            ->order('i.date_add ASC')
            ->fetchFirst();

        $objItemData['canonical'] = sprintf($objItemData['urlTpl'], $objItemData['seoName'], $objItemData['seoUrl']);

        // Если что обрабатывать с предыдущей ссылкой
        self::_modifyDataInfo($prevData, $pCompId, $pContId, $objItemData, 'next', $nextData, 'prev');
        self::_modifyDataInfo($nextData, $pCompId, $pContId, $objItemData, 'prev', $prevData, 'next');

        unset($objItemData['seoUrl'], $objItemData['urlTpl'], $objItemData['treeId'], $objItemData['dateunf']);

        $objItemData = serialize($objItemData);
        filesystem::saveFile($objItemDirData, 'info.txt', $objItemData);

        // Выдача прав на директорию пользователю www
        // Обязательно в /etc/sudoers должна быть строка
        // vk ALL=NOPASSWD:/bin/chown -R www-data:www-data /home/www/SiteCoreFlame/[a-zA-Z0-9.]*/data/comp/*
        if (strToLower(substr(PHP_OS, 0, 3)) !== 'win') {
            exec('sudo chown -R www-data:www-data ' . $objItemDirData);
        }
        // func. saveDataInfo
    }

    // class. model
}