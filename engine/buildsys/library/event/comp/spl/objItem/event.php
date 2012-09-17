<?php

namespace buildsys\library\event\comp\spl\objItem;

// ORM
use ORM\comp\spl\objItem\objItem as objItemOrm;
use ORM\tree\componentTree;
use ORM\comp\spl\oiComment\oiComment as oiCommentOrm;
use ORM\blockItemSettings as blockItemSettingsOrm;
use ORM\tree\compContTree as compContTreeOrm;

// Conf
use \DIR;

// Engine
use core\classes\filesystem;
use core\classes\userUtils;
use core\classes\admin\dirFunc;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;
use admin\library\mvc\comp\spl\objItem\help\model\base\model as baseModel;

class event{

    // Удаление данных из таблицы objItem
    public static function rmItem($pListerUserData, $pEventBuffer) {
        $objItemOrm = new objItemOrm();
        // Проверям есть ли что удалённого в таблице
        $delList = $objItemOrm->select('a.id, a.treeId, cc.comp_id', 'a')
            ->join(compContTreeOrm::TABLE . ' cc', 'cc.id = a.treeId')
            ->where('a.isDel=1')
            ->comment(__METHOD__)
            ->fetchAll();
        if (!$delList) {
            return;
        }
        // Буффер для ID которые удалились
        $whereIdList = '';

        // Получаем compId для статьи
        $componentTree = new componentTree();
        $objItemCompId = $componentTree->get('id', 'sysname="objItem"');
        $oiCommentCompId = $componentTree->get('id', 'sysname="oiComment"');

        $delListCount = count($delList);
        for ($i = 0; $i < $delListCount; $i++) {
            $delItem = $delList[$i];
            // Собираем в буффер ID objItem которые были удалены
            $whereIdList .= ',' . $delItem['id'];

            // Удаляем загруженные файлы
            $pathPrefix = baseModel::getPath($delItem['comp_id'], $delItem['treeId'], $delItem['id']);
            userUtils::rmFolder($pathPrefix);

            // Удаляем кешированные файлы комментариев
            $pPathPrefix = 'comp/'.$oiCommentCompId.'/objItem/'.$delItem['id'].'/';
            $path = dirFunc::getSiteDataPath($pPathPrefix);
            filesystem::rmdir($path);
        } // for($i)

        // Убираем первую лишнюю запятую, до этого список $whereIdList выглядил типо такого: ,32,56,65
        $whereIdList = substr($whereIdList, 1);

        // Если в блоках в WF есть привязки по tableId к статьям, их нужно выставить в NULL
        // Что бы при генерации страницы они были пропущены
        (new blockItemSettingsOrm())
            ->sql('UPDATE `'.blockItemSettingsOrm::TABLE.'` eb
                   JOIN '.objItemOrm::TABLE.' a
                   ON a.id = eb.tableId
                   JOIN '.compContTreeOrm::TABLE.' cc
                   ON cc.id = a.treeId
                   SET
                     eb.tableId = NULL
                   WHERE
                     a.id in (' . $whereIdList . ')
                     AND cc.comp_id='.$objItemCompId)->comment(__METHOD__)->query();

        (new oiCommentOrm())->sql('DELETE ac
                FROM '.oiCommentOrm::TABLE.' ac
                INNER JOIN '.compContTreeOrm::TABLE.' cc ON ac.objId = cc.id
                WHERE
                 cc.isDel = 1
                     AND ac.objId in (' . $whereIdList . ')')->comment(__METHOD__)->query();

        // Удаляем по буфферу $whereIdList все ID которые были помечены на удаление
        $objItemOrm->delete('id in (' . $whereIdList . ')');
        // func. rmItem
    }
// class event
}