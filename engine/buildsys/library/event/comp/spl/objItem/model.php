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
use core\classes\admin\dirFunc;

// Event
use admin\library\mvc\comp\spl\objItem\event as eventObjitem;
use admin\library\mvc\comp\spl\objItem\help\event\article\event as eventArticle;

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
    public static function createMiniPreview($pObjItemObj, $pContId, $pCompId, $pImgPreviewWidth, $pFileNum, $pResizeType) {
        // TODO: Надо как то реализовать через общее хранилище картинок
        // Что бы всё было через апи, что бы было межсерверно

        // Обработка превью картинок. Отсекаем http://{hostname}/
        // TODO: Тут костыль, надо переделать хранение картинок для статей, превью
        
		if ($pObjItemObj->prevImgUrl) {
            $imgFile = substr($pObjItemObj->prevImgUrl, 7 + 1 + strlen(SITE_CONF::NAME));
            // Формируем имя файла, в который будет сохранять картинку
            $resizeFile = 'comp/' . $pCompId . '/' . $pContId . '/' . $pObjItemObj->treeId . '/' . word::idToSplit($pObjItemObj->id) . '/';

            $fileResizePath = dirFunc::getSiteImgResizePath();
            filesystem::mkdir($fileResizePath . $resizeFile);
            //print $fileResizePath . $resizeFile;
            $resizeFile .= $pFileNum . '.' . filesystem::getExt($imgFile);
            $resize = new resize();
            $resize->setWidth($pImgPreviewWidth);
            $resize->setType($pResizeType == 'prop' ? resize::PROPORTIONAL : resize::SQUARE);
			
			if ( !is_readable(dirFunc::getSiteRoot() . $imgFile)){
				return;
			}

            $resize->resize(dirFunc::getSiteRoot() . $imgFile, $fileResizePath . $resizeFile);
            //print $fileResizePath . $resizeFile." $pResizeType".PHP_EOL;
            $pObjItemObj->prevImgUrl = dirFunc::getSiteImgResizeUrl() . $resizeFile . '?' . time();
        } // if $pObjItemObj->prevImgUrl
        return $pObjItemObj;
        // func. createMiniPreview
    }

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
    public static function objItemChange(eventBufferOrm $eventBufferOrm, array $pTableJoinList, tableDb $pTreeTableOrm, compContTreeOrm $compContTreeOrm, array $childList, array $buffTreeIdList, $pQuery = null) {
        // Если не было изменений, в статьях,
        // может были изменений UrlTpl для статей
        // (см. кастом настройки в дереве контента статьи )

        // Для начала нужно получить список всех детей веток и ID родителей
        // которые пользователь отметил в дереве sitemap, после посмотреть
        foreach ($childList as $contId) {
            // Получаем рекурсивно все папки входящие в выбранные пользователем при настройках компонента
            self::_rGetChildList($pTreeTableOrm, $compContTreeOrm, $childList, $contId);
        } // foreach
        unset($parentList);

        // Когда все дети и родители получены можно просмотреть изменения Url Tpl
        $where = array_unique($childList);
        if (!$where) {
            echo 'Select brunch not found'.PHP_EOL;
            return;
        }

        // ВАЖНЫЙ МОМЕНТ. У нас есть список всех веток выбранных пользователем в настройках и дети этих веток ($where)
        // так же у нас есть список из event буффера ($buffTreeIdList) со списком contId по которым производились действия
        // нам нужно понять, вообще эти списки пересекаются, а то может быть сохранения произошли не по тому списки, по которому
        // нам надо, для этого мы производим array_intersect, если пересечения есть, то в мы проверяем нужный нам список, если нет
        // то это этот список нам не нужен, так как мы не в его юрисдикции
        if (!array_intersect($buffTreeIdList, $where)) {
            return;
        }

        $where = implode(',', $where);
        // Делаем выборку всех статей по детям выбранным в дереве sitemap
        $selectDefault = 'i.*, cc.seoName, cc.name category, cc.comp_id compId, DATE_FORMAT(i.date_add, "%Y-%m-%dT%h:%i+04:00") as dateISO8601';

        foreach ($pTableJoinList as $num => $tableName) {
            $selectDefault .= ',a' . $num . '.*';
        }

        $select = isset($pQuery['select']) ? str_replace('%select%', $selectDefault, $pQuery['select']) : $selectDefault;
        $where = isset($pQuery['where']) ? str_replace('%where%', $where, $pQuery['where']) : 'i.isPublic="yes" AND i.isDel=0 AND i.treeId in (' . $where . ')';
        $order = isset($pQuery['order']) ? $pQuery['order'] : 'date_add DESC, id desc';
        $limit = isset($pQuery['limit']) ? $pQuery['limit'] : null;
        $handleObjitem = (new objItemOrm())->select($select, 'i');

        foreach ($pTableJoinList as $num => $tableName) {
            $handleObjitem->joinLeftOuter($tableName . ' a' . $num, 'a' . $num . '.objItemId=i.id');
        }

        return $handleObjitem->join(compContTreeOrm::TABLE . ' cc', 'cc.id=i.treeId')
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->comment(__METHOD__)
            //->printSql()
            ->query();

        // func. objItemChange
    }

    public static function getBuffTreeIdList($pEventBuffer, $childList, $itemPropContId, $pSaveActionName) {
        // Получаем все TreeId которые есть в буффере, это нужно для того
        // что бы понять какие из oiList нужно перегенерить, без этого, генерилось бы
        // все oiList
        $buffTreeIdList = $pEventBuffer->select('cc.treeId', 'eb')
            ->join(objItemOrm::TABLE . ' cc', 'cc.id=eb.userId and eventName="'.eventArticle::ACTION_SAVE.'"')
            ->group('cc.treeId')
            ->toList('treeId');

        // Если данных в $buffTreeIdList нет, то скорей всего было сохранение по настройкам компонента
        if (!$buffTreeIdList) {
            // Проверяем были ли настройки компонента
            $isSaveProp = $pEventBuffer->selectFirst(
                'id',
                ['eventName' => $pSaveActionName,
                'userId' => $itemPropContId]
            );
            if ($isSaveProp) {
                $buffTreeIdList = $childList;
            } // if $isSaveProp

        } // if ( !$buffTreeIdList )
        return $buffTreeIdList;
    }
    // class. model
}