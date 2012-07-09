<?php

namespace admin\library\mvc\manager\wareframe;

// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;

// Engine
use core\classes\valid;
use core\classes\tplParser\tplBlockParser;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// ORM
use ORM\blockfile;
use ORM\blockItem;
use ORM\blockItemSettings;

/**
 * Description of
 *
 * @author Козленко В.Л.
 */
class model {

    const FOLDER_FREE = 0, FOLDER_COMP = 1, FOLDER_TPL = 2, FOLDER_EMPTY = 3;

    /**
     * Получаем полный путь к таблице-макету
     * @param type $pTplFile имя файла маета
     * @return string полный путь
     */
    public static function getTableTpl(string $pTplFile) {
        // Валидация имени файла
        if (!$pTplFile || !valid::isFilenameSafe($pTplFile)) {
            throw new \Exception('Неверное имя файла', 69);
        }
        // Путь в теме сайта к шаблону макета
        $file = DIR::THEME_PATH . SITE_CONF::THEME_NAME . '/block/table/' . $pTplFile;
        if (!is_readable($file)) {
            throw new \Exception('Файл не найден', 70);
        }
        return $file;
        // func. getTableTpl
    }

    public static function saveBlock(integer $pWfId, $pActionId, string $pFile, string $pRmList) {
        $blockfile = new blockfile();

        $file = json_decode($pFile, true);
        if ($file === NULL) {
            throw new \Exception('Bad JSON: File', 102);
        } // if

        if ($pRmList == '"all"') {
            $blockfile->delete(['wf_id' => $pWfId, 'action_id' => $pActionId]);
        } else {
            $rmList = json_decode($pRmList, true);
            if ($rmList === NULL) {
                throw new \Exception('Bad JSON: rmList', 103);
            } // if
            foreach ($rmList as $rmId) {
                $blockfile->delete(['id' => $rmId]);
            } // foreach
        } // if

        $file2id = array();

        foreach ($file as $fileId => $data) {
            if (ctype_digit($fileId)) {
                $fileId = (int)$fileId;
            } // if
            //TODO: проверка на безопастность
            $fileDist['file'] = $data['file'];
            // TODO: кажется brunchId тут не нужен
            //$fileDist['brunch_id'] = (int)$data['id'];
            $fileDist['wf_id'] = $pWfId;
            $fileDist['file_id'] = isset($file2id[$data['fileId']]) ? $file2id[$data['fileId']] : $data['fileId'];
            $fileDist['block'] = $data['block'];
            $fileDist['action_id'] = $pActionId;
            //$fileDist['fileId'] = $fileId;
            $newId = $blockfile->save(
                ['id' => $fileId],
                $fileDist
            );
            if ($newId) {
                $file2id[$fileId] = $newId;
            } // if
        } // foreach
        ksort($file2id);
        return $file2id;
        // func. saveBlock
    }

    /**
     *
     * @param string $pFId
     * @param \Exception $ex
     * @return null
     * @throws \Exception
     */
    public static function tplBlockParser(string $pFId, \Exception $ex = null) {
        $path = DIR::getSiteTplPath();
        // Убераем первый слэшь у имени файла
        $file = substr($pFId, 1);
        $file = $path . str_replace('..', '', $file);
        if (!is_readable($file)) {
            if ($ex) {
                throw $ex;
            } else {
                return null;
            }
        }
        $tplBlockParser = new tplBlockParser($file);
        return $tplBlockParser->getBlockList();
    }

    public static function makeTree(integer $pWfId, $pActionId) {
        $actionId = $pActionId ? : 'null';
        $blockfile = new blockfile();
        // Получем по WfID, в какие блоки были добавлены другие шаблоны
        $blockLoadList = $blockfile->select('id, file, file_id, block, action_id', 't')
            ->where('wf_id=' . $pWfId . ' AND ( action_id IS NULL OR action_id=' . $actionId . ')')
            ->order('id')
            ->comment(__METHOD__)
            ->fetchAll();
        //var_dump($blockLoadList);
        /*
         * Пример $blockLoadList:
         * [
         *  {"id":"236", "file":"main.php", "file_id":"", "block":""},
         *  {"id":"237", "file":"top.php",  "file_id":"236", "block":"top"},
         * ]
         * Если id и file_id совпадают, то это главный шаблон родитель
         * id - это ID шаблона
         * file_id - это ID родителя шаблона
         * file - имя файла
         * block - имя блока в шаблоне родителя, куда прикреплён текущий шаблон
         * у корневого шаблона нет названия шаблона
         */

        $blockItem = new blockItem();
        $blockItemList = $blockItem->select('block_id')
            ->where('wf_id=' . $pWfId . ' AND ( acId IS NULL OR acId=' . $actionId . ')')
            ->group('block_id')
            ->comment(__METHOD__)
            ->toList('block_id');
        //var_dump($blockItemList);
        // Хранить значение: {'имя_файла: ['блок', 'блок']}
        $blockListCache = array();
        // Результирующий массив для dxhtml
        $treeData = [];

        // Текущий номер ветки. 
        // С добавлением каждой ветки(блока) это переменная увеличивается
        $blockNum = 0;
        $blockId2Num = [];

        $rootTreeId = isset($blockLoadList[0]) ? $blockLoadList[0]['id'] : -1;

        // Содержит значение: {'blockId'=>'blockIdParent'}
        $file2id = [];

        // Бегае по загруженным данным
        $listCount = count($blockLoadList);
        for ($i = 0; $i < $listCount; $i++) {
            // Имя в котором находятся блоки
            $loadFilename = $blockLoadList[$i]['file'];
            // Название блока из шаблона шаблона родителя, может быть пустым
            // если файла корневой
            $loadBlockName = $blockLoadList[$i]['block'];
            $loadFileId = $blockLoadList[$i]['id'];
            // ID файла родителя, при этом является ID ветки на которую прикреплён файл
            $loadFileParentId = $blockLoadList[$i]['file_id'];

            $loadAcId = $blockLoadList[$i]['action_id'];

            // ======= Если в массиве кэша нет данных о блоках по файлу =======
            if (!isset($blockListCache[$loadFilename])) {
                // Парсим шаблон и заносим в кэш
                // Если файл не найден, то self::tplBlockParser вернёт null
                // Формат хранения {'имя_файла: ['блок', 'блок']}
                $blockListCache[$loadFilename] = self::tplBlockParser($loadFilename);
            } // if
            // Получем список шаблонов
            $blockList = $blockListCache[$loadFilename];
            // Если null то файла нет, генерим ошибку
            if ($blockList === null) {
                //throw new exception\wareframe('File not found ' . $loadFilename . ' Id: ' . $loadFileId, 234);
				continue;
            } // if

            // TODO: сделать проверку на изменение название шаблона
            // Выставляем родителький ID блока
            // Только у корневого элемента нет названия блока, по этому ставим:0
            $fileTreeId = $loadBlockName == '' ? 0 : $loadFileId;

            // Если в шаблоне блоков нет, то это пустой шаблон
            $emptyBlock = $blockList === [];
            if ($emptyBlock) {
                $blockList[0] = $loadFilename;
            } // if

            // Бегаем по блокам шаблона
            foreach ($blockList as $bNameSys => $bNameFree) {
                // Если нет нормального названия(человека понятного ) то будем
                // выводить системно название блока
                $brunchName = $bNameFree ? : $bNameSys;
                $blockId2Num[$loadFileId . $bNameSys] = $blockNum;

                // Получаем новый ID для блока
                $blockId = $bNameSys . ':' . $loadFileId;

                $itemType = self::FOLDER_FREE;
                if (in_array($blockId, $blockItemList)) {
                    $itemType = self::FOLDER_COMP;
                }

                // Формируем ветку дерева
                $treeData[$blockNum] = [
                    'name' => $brunchName, // Название ветки
                    'item_type' => $itemType, // Тип дерева
                    'id' => $blockId, // ID ветки
                    'tree_id' => $fileTreeId, // ID родителя ветки из таблицы blokfile
                    'block' => $bNameSys, // системное имя
                    'acId' => $loadAcId, // Action Id,
                    'fileId' => $loadFileId
                ];
                //print $loadFileId."<br/>";
                // Запоминаем какой id блока к какому родителю(id блока) соотвествует
                $file2id[$blockId] = $fileTreeId ? : $rootTreeId;
                // Мы добавили новую ветку(блок), увеличиваем счётчик веток(блоков)
                ++$blockNum;
            } // foreach ( бегаем по блокам шаблона )

            // Если это пустой шаблон ( т.е. шаблон без блоков )
            if ($emptyBlock) {
                $num = $blockNum - 1;
                $treeData[$num]['id'] = 'empty:' . $loadFileId;
                $treeData[$num]['item_type'] = self::FOLDER_EMPTY;
            } // if

            // Важный блок. Содержит связку веток из файлов. т.е. сцепляем ветки
            if (isset($blockId2Num[$loadFileParentId . $loadBlockName])) {
                // Получаем позицию в дереве родительской ветки(блока)
                $num = $blockId2Num[$loadFileParentId . $loadBlockName];
                $file2id[$loadFileId] = $treeData[$num]['tree_id'] ? : $rootTreeId;
                unset($file2id[$treeData[$num]['id']]);
                $treeData[$num]['id'] = $loadFileId;
                $treeData[$num]['item_type'] = self::FOLDER_TPL;
                $treeData[$num]['file'] = $loadFilename;
                $treeData[$num]['acId'] = $treeData[$blockNum - 1]['acId'];
                $treeData[$num]['fileId'] = $loadFileParentId;

            } // if

        } // for ( бегаем по файлам )

        dhtmlxTree::$endBrunch = function(&$pDist, $pType, $pSource, $pNum, $pParam) {
            dhtmlxTree::endBrunch($pDist, $pType, $pSource, $pNum, $pParam);
            $pDist['im0'] = 'folderEmpty.gif';
            // TODO: переделать в switch
            $item_type = $pSource[$pNum]['item_type'];
            if ($item_type == model::FOLDER_COMP) {
                $pDist['im0'] = 'comp.png';
            } // if
            if ($item_type == model::FOLDER_EMPTY) {
                $pDist['im0'] = 'folderClosed.gif';
            } // if
            // TODO: Перевести на норм userData через настройки дерева
            $pDist['userdata'][] = [
				'name' => 'blockName', 
				'content' => $pSource[$pNum]['block']
			];
            $pDist['userdata'][] = [
				'name' => 'acId', 
				'content' => $pSource[$pNum]['acId']
			];
            $pDist['userdata'][] = [
				'name' => 'fileId', 
				'content' => $pSource[$pNum]['fileId']
			];

            if (isset($pSource[$pNum]['file'])) {
                $pDist['userdata'][] = [
					'name' => 'file', 
					'content' => $pSource[$pNum]['file']
				];
            } // if
            // funct. endBrunch
        };

        $tree = dhtmlxTree::all($treeData, 0);
        dhtmlxTree::clear();

        return ['tree' => $tree ];
        // func. makeTree
    }

    /**
     * @static
     * @param $pData данные для сохранения, в формате JSON.<br/>
     * Формат данных $pData = [{id:val, data:{compId:val, name:val, sysname:val}], {...}]
     * @param $pAcId action Id
     * @param $pBlId
     * @param $pWfId wareframe ID
     * @return array
     * @throws \Exception
     */
    public static function saveBlockItem($pData, $pAcId, $pBlId, $pWfId) {
        $return = [];
        // Парсим данны
        $data = json_decode($pData, true);
        if ($data) {
            $blockItem = new blockItem();
            $blockItemSettings = new blockItemSettings();

            // Бегаем по данным
            foreach ($data as $item) {
                if (!isset($item['id'])) {
                    throw new \Exception('Неверный JSON', 234);
                }
                $id = (int)$item['id'];
                $saveData = [
                    'wf_id' => $pWfId,
                    'acId' => $pAcId,
                    'block_id' => $pBlId
                ];
                if (isset($item['data']['compId'])) {
                    $saveData['compid'] = (int)$item['data']['compId'];
                    $blockItemSettings->delete('blockItemId=' . $id);
                }
                if (isset($item['data']['name'])) {
                    $saveData['name'] = $item['data']['name'];
                }
                if (isset($item['data']['sysname'])) {
                    $saveData['sysname'] = $item['data']['sysname'];
                }

                $newId = $blockItem->save('id=' . $id, $saveData);
                $return[$id] = $newId ? : $id;
            }
            // foreach
        } // if
        return $return;
        // funct.saveBlockItem
    }

    public static function changeBlockItemPosition($pPostion, $pNewIdList) {
        $gridItemIdList = explode(',', $pPostion);

        $blockItem = new blockItem();

        $gridItemCount = count($gridItemIdList);
        // Защита от дурака, вдруг кто додумается прислать 1000 элементов
        // Врядли найдётся больше 30 компонентов в обдном блоке
        $gridItemCount = $gridItemCount > 30 ? 30 : $gridItemCount;
        for ($i = 0; $i < $gridItemCount; $i++) {
            $itemId = $gridItemIdList[$i];
            $itemId = isset($pNewIdList[$itemId]) ? $pNewIdList[$itemId] : (int)$itemId;
            $blockItem->update('position=' . $i, 'id=' . $itemId, false)
                ->comment(__METHOD__)
                ->query();
        }
        // func. changeBlockItemPosition
    }

    public static function getBlockItemList($pAcId, $bBlId, $pWfId) {
        $blockItem = new blockItem();
        $acId = $pAcId ? : 'null';
        $blockId = $blockItem->addQuote($bBlId);
        $blockItemList = $blockItem->select('id, "" as ch, "" as img, name, sysname, compId, acId')
            ->where('wf_id=' . $pWfId . ' AND ( acId IS NULL OR acId=' . $acId . ') AND block_id=' . $blockId)
            ->order('position')
            ->fetchAll();
        return $blockItemList;
        // func. getBlockItemList
    }

    // class model
}

?>