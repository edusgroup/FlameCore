<?php

namespace buildsys\library\event\comp\spl\article;
//ORM
use ORM\event\eventBuffer;
use ORM\comp\spl\article\article as articleOrm;
use ORM\tree\compContTree as compContTreeOrm;
use ORM\tree\componentTree;

//Engine
use core\classes\DB\tree;

// Event
use admin\library\mvc\comp\spl\article\event as eventArticle;

/**
 *
 *
 * @author Козленко В.Л.
 */
class model {
    private static function _rGetChildList($sitemapOrm, $compContTreeOrm, &$pChildList, $pContId) {
        $childList = $compContTreeOrm->selectList('id', 'id', 'tree_id=' . $pContId);
        foreach ($childList as $contId) {
            self::_rGetChildList($sitemapOrm, $compContTreeOrm, $pChildList, $contId);
        }
        $pChildList = array_merge($pChildList, $childList);
        // func. _rGetChildList
    }

    public static function articleChange($eventBufferOrm, $pTreeTableOrm, $compContTreeOrm, $childList, $pQuery = null) {
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
            ->where('eventName = "' . eventArticle::ACTOIN_CUSTOM_PROP_SAVE . '" AND userId in (' . $where . ')')
            ->comment(__METHOD__)
            ->fetchFirst();

        // Если изменений не было, может были измнения в статьях
        // К примеру Удаление статьи (eventArticle::ACTION_DELETE)
        // или изменение названия статьи, сео названия, изменение публикации (eventArticle::ACTION_TABLE_SAVE)

        if (!$isCreate) {
            // Делаем выборку из буффера Event на наличие измений по статьям
            $eventBufferOrm
                ->select('userId', 'eb')
                ->where('eventName in ("' . eventArticle::ACTION_DELETE
                            . '","' . eventArticle::ACTION_TABLE_SAVE . '") AND userId in (' . $where . ')')
                ->fetchFirst();

        } // if $isCreate

        $where = implode(',', $childList);
        // Делаем выборку всех статей по детям выбранным в дереве sitemap
        $select = isset($pQuery['select']) ? $pQuery['select'] : 'a.*, cc.seoName, cc.name category, cc.comp_id compId, DATE_FORMAT(a.date_add, "%Y-%m-%dT%h:%i+04:00") as dateISO8601';
        $where = isset($pQuery['where']) ? $pQuery['where'] : 'a.isPublic="yes" AND a.isDel=0 AND a.treeId in (' . $where . ')';
        $order = isset($pQuery['order']) ? $pQuery['order'] : 'date_add DESC, id desc';
        $limit = isset($pQuery['limit']) ? $pQuery['limit'] : null;
        $handleArticle = (new articleOrm())
            ->select($select, 'a')
            ->join(compContTreeOrm::TABLE . ' cc', 'cc.id=a.treeId')
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->query();

        return $handleArticle;
        // func. isArticleChange
    }
    // class. model
}