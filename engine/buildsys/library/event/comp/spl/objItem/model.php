<?php

namespace buildsys\library\event\comp\spl\objItem;
//ORM
use ORM\event\eventBuffer;
use ORM\comp\spl\objItem\objItem as objItemOrm;
use ORM\tree\compContTree as compContTreeOrm;
use ORM\tree\componentTree;

//Engine
use core\classes\DB\tree;
use core\classes\image\resize;
use core\classes\word;
use core\classes\filesystem;

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
    public static function createMiniPreview($pObjItemObj, $pContId, $pCompId, $pImgPreviewWidth, $pFileNum, $pResizeType, $pFolderName){
        // TODO: Надо как то реализовать через общее хранилище картинок
        // Что бы всё было через апи, что бы было межсерверно

        // Обработка превью картинок. Отсекаем http://{hostname}/
        // TODO: Тут костыль, надо переделать хранение картинок для статей, превью
        if ($pObjItemObj->prevImgUrl) {
            $imgFile = substr($pObjItemObj->prevImgUrl, 7 + 1 + strlen(SITE_CONF::NAME));

            // Формируем имя файла, в который будет сохранять картинку
            $resizeFile = 'comp/' . $pCompId . '/'.$pObjItemObj->treeId.'/'.word::idToSplit($pObjItemObj->id).$pFolderName.'/';
            $resizeFile .= $pContId .'/';
            $fileResizePath = DIR::getSiteImgResizePath();
            filesystem::mkdir($fileResizePath . $resizeFile);
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

    public static function createBinaryMiniDesc($objItemObj, &$miniDescrHead, &$miniDescrData){
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
    }


    private static function _rGetChildList($sitemapOrm, $compContTreeOrm, &$pChildList, $pContId) {
        $childList = $compContTreeOrm->selectList('id', 'id', 'tree_id=' . $pContId);
        foreach ($childList as $contId) {
            self::_rGetChildList($sitemapOrm, $compContTreeOrm, $pChildList, $contId);
        }
        $pChildList = array_merge($pChildList, $childList);
        // func. _rGetChildList
    }

    public static function objItemChange($eventBufferOrm, $pTreeTableOrm, $compContTreeOrm, $childList, $pQuery = null) {
        $where = [];
        // Если не было изменений, в статьях,
        // может были изменений UrlTpl для статей
        // (см. кастом настройки в дереве контента статьи )

        // Для начала нужно получить список всех детей веток и ID родителей
        // которые пользователь отметил в дереве sitemap, после посмотреть
        $tree = new tree();
        foreach ($childList as $contId) {
            $parentList = $tree->getTreeUrlById(compContTreeOrm::TABLE, (int)$contId);
            $parentList = array_map(function($pItem) {
                return $pItem['id'];
            }, $parentList);
            self::_rGetChildList($pTreeTableOrm, $compContTreeOrm, $childList, $contId);

            $where = array_merge($childList, $parentList);
        } // foreach
        unset($parentList);

        // Когда все дети и родители получены можно просмотреть изменения Url Tpl
        $where = array_unique($where);
        if (!$where) {
            return false;
        }
        $where = implode(',', $where);
        $isCreate = $eventBufferOrm
            ->select('userId', 'eb')
            ->where('eventName = "' . eventObjitem::ACTOIN_CUSTOM_PROP_SAVE . '" AND userId in (' . $where . ')')
            ->comment(__METHOD__)
            ->fetchFirst();

        // Если изменений не было, может были измнения в статьях
        // К примеру Удаление статьи (eventObjitem::ACTION_DELETE)
        // или изменение названия статьи, сео названия, изменение публикации (eventObjitem::ACTION_TABLE_SAVE)

        if (!$isCreate) {
            // Делаем выборку из буффера Event на наличие измений по статьям
            $eventBufferOrm
                ->select('userId', 'eb')
                ->where('eventName in ("' . eventObjitem::ACTION_DELETE
                            . '","' . eventObjitem::ACTION_TABLE_SAVE . '") AND userId in (' . $where . ')')
                ->fetchFirst();

        } // if $isCreate

        $where = implode(',', $childList);
        // Делаем выборку всех статей по детям выбранным в дереве sitemap
        $select = isset($pQuery['select']) ? $pQuery['select'] : 'a.*, cc.seoName, cc.name category, cc.comp_id compId, DATE_FORMAT(a.date_add, "%Y-%m-%dT%h:%i+04:00") as dateISO8601';
        $where = isset($pQuery['where']) ? $pQuery['where'] : 'a.isPublic="yes" AND a.isDel=0 AND a.treeId in (' . $where . ')';
        $order = isset($pQuery['order']) ? $pQuery['order'] : 'date_add DESC, id desc';
        $limit = isset($pQuery['limit']) ? $pQuery['limit'] : null;
        $handleObjitem = (new objItemOrm())
            ->select($select, 'a')
            ->join(compContTreeOrm::TABLE . ' cc', 'cc.id=a.treeId')
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->comment(__METHOD__)
            ->query();
        return $handleObjitem;
        // func. isobjItemChange
    }
    // class. model
}