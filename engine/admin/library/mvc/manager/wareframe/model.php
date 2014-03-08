<?php

namespace admin\library\mvc\manager\wareframe;

// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;

// Engine
use core\classes\valid;
use core\classes\tplParser\tplBlockParser;
use core\classes\admin\dirFunc;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// ORM
use ORM\blockfile;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\block\blockLink as blockLinkOrm;
use ORM\urlTreePropVar;
use ORM\blockItem\order as blockItemOrderOrm;


/**
 * Description of
 *
 * @author Козленко В.Л.
 */
class model {

    const FOLDER_FREE = 0;
    const FOLDER_COMP = 1;
    const FOLDER_TPL = 2;
    const FOLDER_EMPTY = 3;
    const FOLDER_LINK = 4;

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

        $file2id = [];

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
        $path = dirFunc::getSiteTplPath();
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

    public static function saveBlockLink($pAcId, integer $pWfId, $pLinkBlockBuff){
        if ( !$pLinkBlockBuff ){
            return;
        }
        $linkBlockBuff = json_decode($pLinkBlockBuff);
        $blockLinkOrm = new blockLinkOrm();

        foreach( $linkBlockBuff as $blockId=>$val){
            if ( $val->type == 'add'){
                $blockLinkOrm->saveExt(
                    ['wfId' => $pWfId,
                    'blockId' => $blockId,
                    'acId'=>(int)$pAcId],
                    ['linkMainId'=>$val->linkMainId,
                    'linkBlockId'=>$val->linkBlockId]
                );
            }else{
                $blockLinkOrm->delete(['wfId' => $pWfId, 'blockId' => $blockId, 'acId'=>$pAcId]);
            }
        } // foreach
        // func. saveBlockLink
    }

    public static function makeTree(integer $pWfId, $pActionId) {
        // Получем по WfID, в какие блоки были добавлены другие шаблоны
        $blockLoadList = (new blockfile())->select('id, file, file_id, block, action_id', 't')
            ->where('wf_id=' . $pWfId . ' AND ( action_id = 0 OR action_id=' . $pActionId . ')')
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

        $blockItemList = (new blockItem())->select('block_id')
            ->where('wf_id=' . $pWfId . ' AND ( acId = 0 OR acId=' . $pActionId . ')')
            ->group('block_id')
            ->comment(__METHOD__)
            ->toList('block_id');

        // Буффер всех ссылок для блоков, если они есть
        $linkBlockBuff = (new blockLinkOrm())->selectAll(
            'blockId, linkMainId, linkBlockId, acId',
            'wfId='.$pWfId.' and (acId=0 or acId='.$pActionId.')');
        // Обрабатываем массив, для удобства пользования
        // делаем ключом blockId
        if ( $linkBlockBuff ){
            $tmpBuff = [];
            foreach($linkBlockBuff as $key => &$val){
                $blockName = $val['blockId'];
                unset($val['blockId']);
                $tmpBuff[$blockName] = $val;
            } // foreach
            $linkBlockBuff = $tmpBuff;
        } // if ( $linkBlockBuff )

        // Хранить значение: {'имя_файла: ['блок', 'блок']}
        $blockListCache = [];
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
                ]; // $treeData[$blockNum] = [

                // Если ветка есть в буффере ссылок, значит папка линкованная
                if ( isset($linkBlockBuff[$blockId])){
                    $treeData[$blockNum]['link'] = $linkBlockBuff[$blockId];
                    $treeData[$blockNum]['item_type'] = self::FOLDER_LINK;
                } // if

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

            $itemType = $pSource[$pNum]['item_type'];
            switch( $itemType){
                case model::FOLDER_COMP:
                    $pDist['im0'] = 'comp.png';
                    break;
                case model::FOLDER_EMPTY:
                    $pDist['im0'] = 'folderClosed.gif';
                    break;
                case model::FOLDER_LINK:
                    $pDist['im0'] = $pSource[$pNum]['link']['acId'] ? 'linka.gif' : 'linkw.gif';
                    $pDist['userdata'][] = ['name' => 'link','content' => $pSource[$pNum]['link']];
                    //$pDist['userdata'][] = ['name' => 'blockName','content' => $pSource[$pNum]['block']];
                    break;
                default:
                    $pDist['im0'] = 'folderEmpty.gif';
            }

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
            }// foreach
        } // if

        return $return;
        // funct.saveBlockItem
    }

    public static function changeBlockItemPosition($pPostion, $pNewIdList, $pAcId, $pBlockId) {
        if ( $pAcId ){
			if ( $pPostion ){
				(new blockItemOrderOrm())->saveExt(['acId'=>$pAcId, 'blockId'=>$pBlockId], ['position'=>$pPostion]);
			} // if
        }else{
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
            } // for
        } // if ($pAcId );
        // func. changeBlockItemPosition
    }

    public static function getBlockItemList($pAcId, string $bBlId, integer $pWfId) {
        $blockItem = new blockItem();
        $wfLinkId = $pWfId;
        $blockOrgId = $blockItem->addQuote($bBlId);
        $blockLinkId = $blockOrgId;
        $linkMainId = -1;

        // Получаем информацию по ссылкам
        $where = '(acId=0 or acId='.$pAcId.') and wfId='.$pWfId.' and blockId='.$blockLinkId;
        $linkData = (new blockLinkOrm())->selectFirst('linkMainId, linkBlockId, acId', $where );
        if ( $linkData ){
            // Нужно сделать подмены с инфомации по линкам
            if ( $linkData['acId'] ){
                $linkMainId = (int)$linkData['linkMainId'];
                $wfLinkId = (int)(new urlTreePropVar())->getWFId($linkMainId);
            }else{
                $wfLinkId = $linkData['linkMainId'];
            }
            $blockLinkId = $linkData['linkBlockId'];
            $blockLinkId = $blockItem->addQuote($blockLinkId);
        } // if

        $where = '(wf_id=' . $pWfId .' or wf_id='.$wfLinkId.') AND ( acId = 0 OR acId=' . $pAcId . ' OR acId=' . $linkMainId . ') AND ( block_id=' . $blockLinkId.' or block_id='.$blockOrgId.')';

        // Если мы щас находимся в окне редактирования(т.е. запрос пришёл от туда )
        // Тогда мы можем сделать персональную сортировку с помощью blockItemOrderOrm
        // см. функ. changeBlockItemPosition
        $orderPrefix = '';
        if ( $pAcId ){
            $position = (new blockItemOrderOrm())->get('position', ['acId'=>$pAcId, 'blockId'=>$bBlId]);
            if ( $position ){
                $orderPrefix = 'field(id, '.$position.'),';
            }
        } // if
        $blockItemList = $blockItem
            ->select('id, "" as ch, "" as img, name, sysname, compId, acId')
            ->where($where)
            ->order($orderPrefix.'position')
            ->comment(__METHOD__)
            ->fetchAll();

        return $blockItemList;
        // func. getBlockItemList
    }

    // class model
}