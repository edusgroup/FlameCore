<?php

namespace admin\library\mvc\comp\spl\article;

// ORM
use ORM\comp\spl\article\article as articleOrm;
use core\classes\DB\tree;
use ORM\tree\compContTree;
use ORM\tree\componentTree;
use ORM\event\eventBuffer;
use ORM\comp\spl\article\compArticleProp;
use ORM\comp\spl\artCom\artCom as artCompOrm;
use ORM\blockItemSettings as blockItemSettingsOrm;

// Conf
use \DIR;

// Engine
use core\classes\filesystem;
use core\classes\userUtils;

// Model
use admin\library\mvc\comp\spl\article\model as artModel;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class event {
    const NAME = 'article';
    /**
     * Сохранение самой статьи
     */
    const ACTION_SAVE = 'article:save';
    /**
     * Сохранение названия, seo названия и доступности публикации
     */
    const ACTION_TABLE_SAVE = 'arttable:save';
    /**
     * Удаление статьи
     */
    const ACTION_DELETE = 'table:delete';
    /**
     * Изменение кастом параметров в дереве статьи
     * см. функ. compProp
     */
    const ACTOIN_CUSTOM_PROP_SAVE = 'article::propcustsave';


    public static function urlTplChange($pListerUserData, $pEventBuffer, $pEventList) {
        // Если были какие либо новые сохранениея, то у них пустой urlTpl
        $isArctileSave = $pEventBuffer->selectFirst(
            'id',
            ['eventName'=>self::ACTION_TABLE_SAVE]
        );
		$articleOrm = new articleOrm();
        // Если произошло сохранение
        if ( $isArctileSave ){
            
            $idList = $articleOrm->select('treeId')
                       ->where('urlTpl = ""')
                       ->group('treeId')
                       ->toList('treeId');
            foreach( $idList as $contId ){
                $urlList = (new tree())->getTreeUrlById(compContTree::TABLE, (int)$contId);
                $urlList = array_map(function($pItem){return $pItem['id'];}, $urlList);
                $urlList = implode('","', $urlList);
                // Находим ближайший для нас шаблон
                $data = (new compArticleProp())->sql('SELECT ap.contId, ap.url FROM '.compArticleProp::TABLE.' ap
                                JOIN ( SELECT max(contId) contId FROM '.compArticleProp::TABLE.' WHERE contId IN ("'.$urlList.'")
                                        AND url != "" ) jn ON jn.contId = ap.contId#'.__METHOD__)
                    ->fetchFirst();
                $urlTpl = $data['url'];
                $articleOrm->update(
                    ['urlTpl' => $urlTpl, 'urlTplContId' => $data['contId']],
                    'treeId='.$contId
                );
            } // foreach
            unset($idList, $data);
        } // if Если это сохранение

        // Изменение шаблона в кастом настройках
        $isSettChange = $pEventBuffer->selectFirst(
            'id',
            ['eventName'=>self::ACTOIN_CUSTOM_PROP_SAVE]
        );
        if ( $isSettChange ){
            $compContTree = new compContTree();
            $idList = $pEventBuffer
                ->select('userId', 'eb')
                ->where(['eventName' => self::ACTOIN_CUSTOM_PROP_SAVE])
                ->group('userId')
                ->toList('userId');
            foreach( $idList as $contId){
                $urlList = (new tree())->getTreeUrlById(compContTree::TABLE, (int)$contId);
                $urlList = array_map(function($pItem){return $pItem['id'];}, $urlList);
                $urlList = implode('","', $urlList);
                $data = (new compArticleProp())
                    ->sql('SELECT ap.contId, ap.url FROM '.compArticleProp::TABLE.' ap
                           JOIN ( SELECT max(contId) contId FROM '.compArticleProp::TABLE.' WHERE contId IN ("'.$urlList.'")
                               AND url != "" ) jn ON jn.contId = ap.contId#'.__METHOD__)
                    ->fetchFirst();
                $urlTpl = $data['url'];
                self::_rSetUrlTpl($compContTree, $articleOrm, $contId, $data['contId'], $urlTpl);
            } // foreach
        } // if
        // func. urlTplChange
    }

    public static function saveDataInfo($pId, $pArticleOrm, $pCompId, $pContId) {

        $saveDirArticle = artModel::getPath($pCompId, $pContId, $pId);
        $saveDirArticle = DIR::getSiteDataPath($saveDirArticle);

        // Получаем все данные по статье
        $artData = $pArticleOrm
            ->select('a.id, a.seoUrl, a.treeId, a.caption, a.prevImgUrl, isPublic'
                         . ',cc.seoName, cc.name category, a.seoKeywords, a.seoDescr, a.isCloaking'
                         . ',DATE_FORMAT(a.date_add, "%d.%m.%y %H:%i") date_add, a.date_add dateunf, a.urlTpl', 'a')
            ->join(compContTree::TABLE . ' cc', 'a.treeId=cc.id')
            ->where('a.id=' . $pId)
            ->comment(__METHOD__)
            ->fetchFirst();

        // Данные предыдушей статьи
        $prevData = $pArticleOrm
            ->select('t.id, t.seoUrl, t.caption, cc.seoName, t.urlTpl', 't')
            ->join(compContTree::TABLE . ' cc', 't.treeId=cc.id')
            ->where(
                'date("'.$artData['dateunf'].' ") >= date(t.date_add)
                AND t.isPublic = "yes"
                AND t.isDel = 0
                And t.treeId = '.$artData['treeId'].'
                AND t.id < '.$artData['id'])
            ->order('t.date_add DESC, t.id desc')
            ->fetchFirst();

        // Данные следующей статьи
        $nextData = $pArticleOrm
            ->select('t.id, t.seoUrl, t.caption, cc.seoName, t.urlTpl', 't')
            ->join(compContTree::TABLE . ' cc', 't.treeId=cc.id')
            ->where(
            'date("'.$artData['dateunf'].' ") <= date(t.date_add)
                AND t.isPublic = "yes"
                AND t.isDel = 0
                And t.treeId = '.$artData['treeId'].'
                AND t.id > '.$artData['id'])
            ->order('t.date_add ASC')
            ->fetchFirst();

        $artData['canonical'] = sprintf($artData['urlTpl'], $artData['seoName'], $artData['seoUrl']);
        // Если что обрабатывать с предыдущей ссылкой
        self::_modifyDataInfo($prevData, $pCompId, $pContId, $artData, 'next', $nextData, 'prev');
        self::_modifyDataInfo($nextData, $pCompId, $pContId, $artData, 'prev', $prevData, 'next');

        unset($artData['seoUrl'], $artData['urlTpl'], $artData['treeId'], $artData['dateunf']);

        $artData = serialize($artData);
        filesystem::saveFile($saveDirArticle, 'info.txt', $artData);
        // func. saveDataInfo
    }

    private static function _modifyDataInfo($prevData, $pCompId, $pContId, &$artData, $direction, $nextData, $undirection){
        // Если что обрабатывать с предыдущей ссылкой
        if ( $prevData ){
            // Обработка предыщуей статьи
            $saveDir = artModel::getPath($pCompId, $pContId, $prevData['id']);
            $saveDir = DIR::getSiteDataPath($saveDir);

            $data = $prevData;
            if ( is_file($saveDir.'info.txt')){
                $data = file_get_contents($saveDir.'info.txt');
                $data = \unserialize($data);
            }

            if ( $artData['isPublic'] == 'yes'){
                // Добавляем в данные текущей статьи
                $artData[$undirection] = [
                    'url' => sprintf($prevData['urlTpl'], $prevData['seoName'], $prevData['seoUrl']),
                    'caption' => $prevData['caption'],
                    'prevId' => $prevData['id']
                ];

                // Добавляем в данные предыдущей статьи
                $data[$direction] = [
                    'id' => $artData['id'],
                    'caption' => $artData['caption'],
                    'url' => $artData['canonical']
                ];
            }else{
                $data[$direction] = [
                    'id' => $nextData['id'],
                    'caption' => $nextData['caption'],
                    'url' => sprintf($nextData['urlTpl'], $nextData['seoName'], $nextData['seoUrl']),
                ];
            } // if $dataArticle['isPublic']

            $data = serialize($data);
            filesystem::saveFile($saveDir, 'info.txt', $data);
        } // if $prevData
    }

    private static function _rSetUrlTpl($compContTree, $articleOrm, $pContId, $purlTplContId, $pUrlTpl){
            $articleOrm->update(
                ['urlTpl' => $pUrlTpl, 'urlTplContId'=>$purlTplContId],
                'treeId='.$pContId
            );
            $handleArt = $articleOrm
                ->select('a.id, cc.id contId, cc.comp_id compId', 'a')
                ->join(compContTree::TABLE.' cc', 'cc.id = a.treeId')
                ->query();
            while($item = $handleArt->fetch_object()){
                self::saveDataInfo($item->id, $articleOrm, $item->compId, $item->contId);
            } // while

            // Получаем детей с пустым UrlTpl
            $childList = $compContTree
                ->select('cc.id', 'cc')
                ->joinLeftOuter(compArticleProp::TABLE.' ap', 'ap.contId = cc.id  and ap.url = ""')
                ->where('cc.tree_Id ='.$pContId)
                ->comment(__METHOD__)
                ->toList('id');
            // Бегаем по детям, проставляем UrlTpl
            foreach( $childList as $contId){
                self::_rSetUrlTpl($compContTree, $articleOrm, $contId, $purlTplContId, $pUrlTpl);
            }
        // func. setUrlTpl
    }

    // Удаление данных из таблицы
    public static function rmItem($pListerUserData, $pEventBuffer) {

        $articleOrm = new articleOrm();
        $delList = $articleOrm->select('a.id, a.treeId, cc.comp_id', 'a')
            ->join(compContTree::TABLE . ' cc', 'cc.id = a.treeId')
            ->where('a.isDel=1')
            ->fetchAll();
        if (!$delList) {
            return;
        }
        // Буффер для ID которые удалились
        $whereIdList = '';

        // Получаем compId для статьи
        $componentTree = new componentTree();
        $artCompId = $componentTree->get('id', 'sysname="article"');
        $artComCompId = $componentTree->get('id', 'sysname="artCom"');

        $delListCount = count($delList);
        for ($i = 0; $i < $delListCount; $i++) {
            $delItem = $delList[$i];
            $whereIdList .= ',' . $delItem['id'];

            // Удаляем загруженные файлы
            $pathPrefix = model::getPath($delItem['comp_id'], $delItem['treeId'], $delItem['id']);
            userUtils::rmFolder($pathPrefix);

            // Удаляем кешированные файлы комментариев
            $pPathPrefix = 'comp/'.$artComCompId.'/article/'.$delItem['id'].'/';
            $path = DIR::getSiteDataPath($pPathPrefix);
            filesystem::rmdir($path);
        } // for($i)

        $whereIdList = substr($whereIdList, 1);

        // Если в блоках в WF есть привязки по tableId к статьям, их нужно выставить в NULL
        // Что бы при генерации страницы они были пропущены
        (new blockItemSettingsOrm())
            ->sql('UPDATE `'.blockItemSettingsOrm::TABLE.'` eb
                   JOIN '.articleOrm::TABLE.' a
                   ON a.id = eb.tableId
                   JOIN '.compContTree::TABLE.' cc
                   ON cc.id = a.treeId
                   SET
                     eb.tableId = NULL
                   WHERE
                     a.id in (' . $whereIdList . ')
                     AND cc.comp_id='.$artCompId)->query();

        (new artCompOrm())->sql('DELETE ac
                FROM '.artCompOrm::TABLE.' ac
                INNER JOIN '.compContTree::TABLE.' cc ON ac.objId = cc.id
                WHERE
                 cc.isDel = 1
                     AND ac.objId in (' . $whereIdList . ')')->query();


        $articleOrm->delete('id in (' . $whereIdList . ')');
        // func. rmItem
    }

    public static function renameSafe($pListerUserData, $pEventBuffer, $pEventList){
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }
        // Переименовывание
        (new articleOrm())->update(
            'seoUrl = seoUrlTmp, seoUrlTmp = ""',
            'seoUrlTmp != ""'
        );
        // func. renameSafe
    }

    public static function createArticleInfo($pListerUserData, $pEventBuffer, $pEventList){
        $articleOrm = new articleOrm();
        $dataList = $pEventBuffer
            ->select('a.id, cc.comp_Id compId, cc.id contId', 'eb')
            ->join(articleOrm::TABLE.' a', 'a.id=eb.userId')
            ->join(compContTree::TABLE.' cc', 'cc.id = a.treeId')
            ->where('eb.eventName in ('.$pEventList.')')
            ->group('eb.userId')
            ->order('eb.userId')
            ->comment(__METHOD__)
            ->fetchAll();
        foreach( $dataList as $item ){
            self::saveDataInfo($item['id'], $articleOrm, $item['compId'], $item['contId']);
        } // foreach
        // func. createArticleInfo
    }

    // class. event
}