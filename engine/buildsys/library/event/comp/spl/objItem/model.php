<?php

namespace buildsys\library\event\comp\spl\objItem;
//ORM
use ORM\event\eventBuffer;
use ORM\comp\spl\objItem\objItem as objItemOrm;
use ORM\tree\compContTree as compContTreeOrm;
use ORM\tree\componentTree;
use ORM\event\eventBuffer as eventBufferOrm;

//Engine
use core\classes\DB\tree;
use core\classes\image\resize;
use core\classes\word;
use core\classes\filesystem;
use core\classes\DB\table as tableDb;

// Event
use admin\library\mvc\comp\spl\objItem\event as eventObjitem;

// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;

/**
 *
 *
 * @author Козленко В.Л.
 */
class model {

    /**
     * Создаёт минипревью для изображения. Используется в агригирующих компонентах:<br/>
     * oiLaster, oiPopular
     */
    public static function createMiniPreview($pObjItemObj, $pContId, $pCompId, $pImgPreviewWidth, $pFileNum, $pResizeType){
        // TODO: Надо как то реализовать через общее хранилище картинок
        // Что бы всё было через апи, что бы было межсерверно

        // Обработка превью картинок. Отсекаем http://{hostname}/
        // TODO: Тут костыль, надо переделать хранение картинок для статей, превью
        if ($pObjItemObj->prevImgUrl) {
            $imgFile = substr($pObjItemObj->prevImgUrl, 7 + 1 + strlen(SITE_CONF::NAME));
            // Формируем имя файла, в который будет сохранять картинку
            $resizeFile = 'comp/'.$pCompId.'/'.$pContId.'/'.$pObjItemObj->treeId.'/'.word::idToSplit($pObjItemObj->id).'/';

            $fileResizePath = DIR::getSiteImgResizePath();
            filesystem::mkdir($fileResizePath . $resizeFile);
            //print $fileResizePath . $resizeFile;
            $resizeFile .= $pFileNum . '.' . filesystem::getExt($imgFile);
            $resize = new resize();
            $resize->setWidth($pImgPreviewWidth);
            $resize->setType($pResizeType == 'prop' ? resize::PROPORTIONAL : resize::SQUARE);
            $resize->resize(DIR::getSiteRoot() . $imgFile, $fileResizePath . $resizeFile);
            //print $fileResizePath . $resizeFile." $pResizeType".PHP_EOL;
            $pObjItemObj->prevImgUrl = DIR::getSiteImgResizeUrl() . $resizeFile.'?'.time();
        } // if $pObjItemObj->prevImgUrl
        return $pObjItemObj;
        // func. createMiniPreview
    }

    /*public static function createBinaryMiniDesc($objItemObj, &$miniDescrHead, &$miniDescrData){
        // ----------------------------------------
        // Теперь нужно сгенерить файл со списком новостей и их мини описаниями
        // Будем всё упаковывать бинарно
        // Директория с данными статьи
        $objItemDataDir = objItemModel::getPath($objItemObj->compId, $objItemObj->treeId, $objItemObj->id);
        $miniDescrFile = DIR::getSiteDataPath($objItemDataDir) . 'minidescr.txt';
        if (is_readable($miniDescrFile)) {
            $data = file_get_contents($miniDescrFile);
            $miniDescrHead .= pack('i', strlen($data));
            $miniDescrData .= $data;
        } else {
            $miniDescrHead .= pack('i', 0);
        } // if
        // func. createBinaryMiniDesc
    }*/


    private static function _rGetChildList($sitemapOrm, compContTreeOrm $compContTreeOrm, &$pChildList, $pContId) {
        $childList = $compContTreeOrm->selectList('id', 'id', 'tree_id=' . $pContId);
        foreach ($childList as $contId) {
            self::_rGetChildList($sitemapOrm, $compContTreeOrm, $pChildList, $contId);
        }
        $pChildList = array_merge($pChildList, $childList);
        // func. _rGetChildList
    }

    /**
     * @static
     * @param eventBufferOrm $eventBufferOrm
     * @param tableDb $pTreeTableOrm таблица, где храниться записи, какие категории были выбраны в дереве
     * @param compContTreeOrm $compContTreeOrm дерево контента
     * @param array $childList список выделенных веток в дереве контента
	 * @param array $buffTreeIdList список contId, которые попал в event буффер, т.е. нам нужны только те ветки, которые попадают в этот список
     * @param array $pQuery доп настройки
     * @return bool|\core\classes\DB\adapter\type
     */
    public static function objItemChange(eventBufferOrm  $eventBufferOrm, array $pTableJoinList, tableDb $pTreeTableOrm, compContTreeOrm $compContTreeOrm, array $childList, array $buffTreeIdList, $pQuery = null) { 
        $where = [];
        // Если не было изменений, в статьях,
        // может были изменений UrlTpl для статей
        // (см. кастом настройки в дереве контента статьи )

        // Для начала нужно получить список всех детей веток и ID родителей
        // которые пользователь отметил в дереве sitemap, после посмотреть
        $tree = new tree();
        foreach ($childList as $contId) {
            /*$parentList = $tree->getTreeUrlById(compContTreeOrm::TABLE, (int)$contId);
            $parentList = array_map(function($pItem) {
                return $pItem['id'];
            }, $parentList);*/
			// Получаем рекурсивно все папки входящие в выбранные пользователем при настройках компонента
            self::_rGetChildList($pTreeTableOrm, $compContTreeOrm, $childList, $contId);

            //$where = array_merge($childList, $parentList);
        } // foreach
        unset($parentList); 
		
		

        // Когда все дети и родители получены можно просмотреть изменения Url Tpl
        $where = array_unique($childList);
        if (!$where) {
            return;
        }
		
		// ВАЖНЫЙ МОМЕНТ. У нас есть список всех веток выбранных пользователем в настройках и дети этих веток ($where)
		// так же у нас есть список из event буффера ($buffTreeIdList) со списком contId по которым производились действия
		// нам нужно понять, вообще эти списки пересекаются, а то может быть сохранения произошли не по тому списки, по которому 
		// нам надо, для этого мы производим array_intersect, если пересечения есть, то в мы проверяем нужный нам список, если нет
		// то это этот список нам не нужен, так как мы не в его юрисдикции
		if ( !array_intersect($buffTreeIdList, $where) ){
			return;
		}
		
        /*$where = implode(',', $where);
        $isCreate = $eventBufferOrm
            ->select('userId', 'eb')
            ->where('eventName = "' . eventObjitem::ACTOIN_CUSTOM_PROP_SAVE . '" AND userId in (' . $where . ')')
            ->comment(__METHOD__)
            ->fetchFirst();*/
			
		

        // Если изменений не было, может были измнения в статьях
        // К примеру Удаление статьи (eventObjitem::ACTION_DELETE)
        // или изменение названия статьи, сео названия, изменение публикации (eventObjitem::ACTION_TABLE_SAVE)

        /*if (!$isCreate) {
            // Делаем выборку из буффера Event на наличие измений по статьям
            $eventBufferOrm
                ->select('userId', 'eb')
                ->where('eventName in ("' . eventObjitem::ACTION_DELETE
                            . '","' . eventObjitem::ACTION_TABLE_SAVE . '") AND userId in (' . $where . ')')
                ->fetchFirst();

        } // if $isCreate*/

        $where = implode(',', $where);
        // Делаем выборку всех статей по детям выбранным в дереве sitemap
        $selectDefault = 'i.*, cc.seoName, cc.name category, cc.comp_id compId, DATE_FORMAT(i.date_add, "%Y-%m-%dT%h:%i+04:00") as dateISO8601';

        foreach($pTableJoinList as $num=>$tableName){
           $selectDefault .= ',a'.$num.'.*';
        };

        $select = isset($pQuery['select']) ? $pQuery['select'] : $selectDefault;
        $where = isset($pQuery['where']) ? $pQuery['where'] : 'i.isPublic="yes" AND i.isDel=0 AND i.treeId in (' . $where . ')';
        $order = isset($pQuery['order']) ? $pQuery['order'] : 'date_add DESC, id desc';
        $limit = isset($pQuery['limit']) ? $pQuery['limit'] : null;
        $handleObjitem = (new objItemOrm())->select($select, 'i');

        foreach($pTableJoinList as $num=>$tableName){
            $handleObjitem->joinLeftOuter($tableName.' a'.$num, 'a'.$num.'.itemObjId=i.id');
        }

        return $handleObjitem->join(compContTreeOrm::TABLE . ' cc', 'cc.id=i.treeId')
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->comment(__METHOD__)
            ->query();
        // func. objItemChange
    }
    // class. model
}