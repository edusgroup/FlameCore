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
use core\classes\admin\dirFunc;
use core\classes\comp as compCore;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;
use admin\library\mvc\comp\spl\objItem\help\model\base\model as baseModel;

// Event
use admin\library\mvc\comp\spl\objItem\help\event\base\event as eventBase;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class model {
    public static function urlTplChange($pListerUserData, $pEventBuffer, $pEventList) {

        $objItemPropOrm = new objItemProp();

        // Получаем список данных по событию сохранения в таблице objItem
        $eventBuffData = $pEventBuffer
            ->select('userId as objItemId, userData')
            ->where(['eventName' => eventBase::ACTION_TABLE_SAVE])
            ->group('userId')
            ->comment(__METHOD__)
            ->fetchAll();

        $objItemOrm = new objItemOrm();
        // Бегаем по событиям
        foreach ($eventBuffData as $eventBuffItem) {
            // Получаем ContId для объекта, по которому произошло событие
            $eventContId = \unserialize($eventBuffItem['userData'])['contId'];

            $contPropData = compCore::findCompPropBytContId((int)$eventContId);
            if ( !isset($contPropData['classname']) ){
                continue;
            }

            // Имя класса который задали в настройках
            $classFile = $contPropData['classFile'] ? : '/base/' . $contPropData['classname'] . '.php';
            $compNs = $contPropData['ns'];
            $className = compCore::fullNameClassAdmin($classFile, $compNs);

            $contrObj = new $className('', '');
            // Получаем имя таблицы, с котороым работает данных класс
            $ormTable = $contrObj->getTableCustom();
			if ( $ormTable == '' ){
				echo 'Warning: For contId[ '.$eventContId.'] not set urlTPl'.PHP_EOL;
				continue;
			}

            // Получаем все объекты, у которых не выставленно свойство urlTpl
            $eventBuffList = $objItemOrm->select('i.treeId', 'i')
                ->join($ormTable . ' a', 'a.objItemId=i.id')
                ->where('a.urlTpl = "" and i.treeId=' . $eventContId)
                ->group('i.treeId')
                ->toList('treeId');

            // Бегаем по объектам без urlTpl, ищем для них ближайщую настройку c urlTpl и в ставляем в поле urlTpl
            foreach ($eventBuffList as $contId) {

                $urlList = (new tree())->getTreeUrlById(compContTree::TABLE, (int)$contId);
                $urlList = array_map(function ($pItem) {
                    return $pItem['id'];
                }, $urlList);
                $urlList = implode('","', $urlList);
                // Находим ближайший для нас шаблон
                $data = $objItemPropOrm->sql(
                    'SELECT ap.contId, ap.url ' .
                        'FROM ' . objItemProp::TABLE . ' ap ' .
                        'JOIN ( SELECT max(contId) contId FROM ' . objItemProp::TABLE . ' WHERE contId IN ("' . $urlList . '") AND url != "" ) jn ' .
                        'ON jn.contId = ap.contId#' . __METHOD__
                )->fetchFirst();
                if (!$data) {
                    continue;
                }

                $urlTpl = $data['url'];
                // Обновляем шаблоны ссылки и какой ветке настроек шаблон пренадлежит
                $objItemOrm->sql(
                    'UPDATE ' . $ormTable . ' a ' .
                        'JOIN ' . objItemOrm::TABLE . ' i ' .
                        'ON i.id=a.objItemId ' .
                        'SET a.urlTpl="' . $urlTpl . '", a.urlTplContId=' . $data['contId'] . ' ' .
                        'WHERE i.treeId=' . $contId . ' #' . __METHOD__
                )->query();
            } // foreach
            unset($eventBuffList, $data);
        } // foreach

        $compContTree = new compContTree();
        $eventBuffList = $pEventBuffer
            ->select('userId contId')
            ->where(['eventName' => eventBase::ACTOIN_CUSTOM_PROP_SAVE])
            ->group('userId')
            ->toList('contId');
        foreach ($eventBuffList as $eventContId) {

            $contPropData = compCore::findCompPropBytContId((int)$eventContId);

            // Имя класса который задали в настройках
            $classFile = $contPropData['classFile'] ? : '/base/' . $contPropData['classname'] . '.php';
            $compNs = $contPropData['ns'];
            $className = compCore::fullNameClassAdmin($classFile, $compNs);

            $contrObj = new $className('', '');
            // Получаем имя таблицы, с котороым работает данных класс
            $ormTable = $contrObj->getTableCustom();

            $urlList = (new tree())->getTreeUrlById(compContTree::TABLE, (int)$eventContId);
            $urlList = array_map(function ($pItem) {
                return $pItem['id'];
            }, $urlList);
            $urlList = implode('","', $urlList);
            $data = $objItemPropOrm
                ->sql('SELECT ap.contId, ap.url FROM ' . objItemProp::TABLE . ' ap
                           JOIN ( SELECT max(contId) contId FROM ' . objItemProp::TABLE . ' WHERE contId IN ("' . $urlList . '")
                               AND url != "" ) jn ON jn.contId = ap.contId#' . __METHOD__)
                ->fetchFirst();
            $urlTpl = $data['url'];
            self::_rSetUrlTpl($compContTree, $objItemOrm, $eventContId, $data['contId'], $urlTpl, $ormTable);
        } // foreach

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
            $saveDir = baseModel::getPath($pCompId, $pContId, $prevData['id']);
            $saveDir = dirFunc::getSiteDataPath($saveDir);

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

    private static function _rSetUrlTpl($compContTree, $objItemOrm, $pContId, $pUrlTplContId, $pUrlTpl, $ormTable) {
        $objItemOrm->sql(
            'UPDATE ' . $ormTable . ' a ' .
                'JOIN ' . $objItemOrm::TABLE . ' i ON i.id=a.objItemId ' .
                'SET a.urlTpl="' . $pUrlTpl . '", a.urlTplContId=' . $pUrlTplContId . ' ' .
                'WHERE i.treeId=' . $pContId . ' ' .
                '#' . __METHOD__
        )->query();

        $handleArt = $objItemOrm
            ->select('a.id, cc.id contId, cc.comp_id compId', 'a')
            ->join(compContTree::TABLE . ' cc', 'cc.id = a.treeId')
            ->comment(__METHOD__)
            ->query();
        while ($item = $handleArt->fetch_object()) {
            self::saveDataInfo((int)$item->id, $objItemOrm, (int)$item->compId, (int)$item->contId);
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
            self::_rSetUrlTpl($compContTree, $objItemOrm, $contId, $pUrlTplContId, $pUrlTpl, $ormTable);
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

        $objItemDirData = baseModel::getPath($pCompId, $pContId, $pId);
        $objItemDirData = dirFunc::getSiteDataPath($objItemDirData);

        $contPropData = compCore::findCompPropBytContId($pContId);
        // Имя класса который задали в настройках
        $classFile = $contPropData['classFile'] ? : '/base/' . $contPropData['classname'] . '.php';
        $compNs = $contPropData['ns'];
        $className = compCore::fullNameClassAdmin($classFile, $compNs);
		
        $contrObj = new $className('', '');
		
        if (!method_exists($contrObj, 'saveDataInfo')) {
			echo 'Warning: '.$className.' does not have saveDataInfo'.PHP_EOL;
            return;
        }
        $saveDataInfo = $contrObj->saveDataInfo($pId, $pObjItemOrm);
        if (!$saveDataInfo['obj']) {
            return;
        }

        if ($saveDataInfo['prev']) {
            self::_modifyDataInfo($saveDataInfo['prev'], $pCompId, $pContId, $saveDataInfo['obj'], 'next', $saveDataInfo['next'], 'prev');
        }
        if ($saveDataInfo['next']) {
            self::_modifyDataInfo($saveDataInfo['next'], $pCompId, $pContId, $saveDataInfo['obj'], 'prev', $saveDataInfo['prev'], 'next');
        }

        $objItemData = serialize($saveDataInfo['obj']);
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