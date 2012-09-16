<?php

namespace core\comp\spl\oiComment\func;

// Core
use core\classes\request;
use core\classes\render;
use core\classes\word;
use core\classes\filesystem;

// ORM
use ORM\comp\spl\oiComment\oiCommentBi as oiCommentBiOrm;
use ORM\comp\spl\oiComment\oiCommentProp as oiCommentpPropOrm;
use ORM\comp\spl\oiComment\oiComment as oiCommentOrm;
use ORM\tree\componentTree as componentOrm;
use ORM\tree\compContTree as compContTreeOrm;
use ORM\blockItemSettings;
use core\classes\site\dir as sitePath;

// Conf
use \site\conf\DIR;
use \site\conf\SITE;

/**
 * Description of create
 *
 * @author Козленко В.Л.
 */
class create {

    private function makeList($id, $comList, &$data) {

        $comListCount = count($comList);
        $flag = false;
        for ($i = 0; $i < $comListCount; $i++) {
            if ($comList[$i]['tree_id'] != $id && $flag) {
                break;
            } // if $flag
            if ($comList[$i]['tree_id'] == $id) {
                $itemId = $comList[$i]['id'];
                $data .= ',' . $itemId;
                self::makeList($itemId, $comList, $data);
                $flag = true;
            }
        } // for
        // func. makeList
    }

    public function save() {
        $blockItemId = request::getInt('blockItemId');
        $objItemId = request::getInt('objItemId');

        // Текст комментария
        $comment = request::postSafe('comment');
        $author = request::postSafe('author');
        // Если ни чего нет и
        if ( !$comment ){
            return;
        }

        //$commentPostId = request::postInt('comment_post_ID');
        // ID родителя комментария, т.е. к какому комментарию он относится
        $parentId = request::postInt('parentId');

        // Получаем настроечные данные
        $oiCommentBiOrm = new oiCommentBiOrm();
        $oiCommentData = $oiCommentBiOrm
            ->select('acb.tplListFile, acb.tplComFile, acp.type, c.ns, c.id as compId', 'acb')
            ->join(blockItemSettings::TABLE . ' bis', 'bis.blockItemId=acb.blockItemId')
            ->join(oiCommentpPropOrm::TABLE . ' acp', 'acp.contId=bis.custContId')
            ->join(compContTreeOrm::TABLE . ' cc', 'cc.id = bis.custContId')
            ->join(componentOrm::TABLE . ' c', 'c.id = cc.comp_id')
            ->where('acb.blockItemId=' . $blockItemId)
            ->comment(__METHOD__)
            ->fetchFirst();
        if (!$oiCommentData) {
            throw new \Exception('No data from blockItemId=' . $blockItemId);
        }
        // Название шаблона для комментариев
        // TODO: Сделать обработку внешних шаблонов. Убрать [o],
        // TODO: внизу заменить sitePath::getSiteCompTplPath(false, на sitePath::getSiteCompTplPath($isOut,
        $tplListFile = substr($oiCommentData['tplListFile'], 1);
        $tplComFile = substr($oiCommentData['tplComFile'], 1);
        // Тип комментариев
        $oiCommentType = $oiCommentData['type'];
		
        $oiCommentOrm = new oiCommentOrm();
        $comList = $oiCommentOrm->selectFirst(
            'id',
            ['type' => $oiCommentType, 'objId' => $objItemId]
        );
        $isFirst = !(boolean)$comList;
        unset($comList);

        $oiCommentOrm->insert(['userName' => $author,
                           'comment' => $comment,
                           'tree_id' => $parentId,
                           'type' => $oiCommentType,
                           'objId' => $objItemId
                           ]);
        $newId = $oiCommentOrm->insertId();

        // ======= Создаём код Комментария
        // Преобразуем NameSpace имя в путь папки
        $nsPath = filesystem::nsToPath($oiCommentData['ns']);
        $tplPath = sitePath::getSiteCompTplPath(false, $nsPath);
        // Создаём шаблон
        $render = new render($tplPath, '');
        $render->setMainTpl($tplComFile)
            ->setVar('author', $author)
            ->setVar('comment', $comment)
            ->setVar('id', $newId)
            ->setVar('isFirst', $isFirst)
            ->setVar('dateAdd', date("d-m-y H:i"))
            ->render();

        // Формируем правильное дерево 
        $comList = $oiCommentOrm->selectAll(
            'id, tree_id',
            ['type' => $oiCommentType, 'objId' => $objItemId],
            'tree_id, date_add');

        $idString = '';
        // Получаем правильную последовательность ID
        self::makeList(0, $comList, $idString);
        $idString = substr($idString, 1);
        unset($comList);

        // ======= Создаём код Списка комментариев и сохраняем в файл
        $comListHandle = $oiCommentOrm
            ->select('id, tree_id as treeId, userName, comment'
                         . ', DATE_FORMAT(date_add, "%d-%m-%y %H:%i") dateAdd'
                         . ', DATE_FORMAT(date_add, "%y-%m-%d") dateAddSys')
            ->where('id in (' . $idString . ')')
            ->order('field(id, ' . $idString . ')')
            ->query();

        ob_start();
        $render->setMainTpl($tplListFile)
            ->clear()
            ->setVar('comHandle', $comListHandle)
            ->setBlock('comment', $tplComFile)
            ->render();

        $data = ob_get_clean();
        $objItemId = word::idToSplit($objItemId);
        $folder = DIR::APP_DATA . 'comp/' . $oiCommentData['compId'] . '/' . $oiCommentType . '/' . $objItemId;
        filesystem::saveFile($folder, 'comm.html', $data);
        // func. save
    }

    // func. createArtCom
}