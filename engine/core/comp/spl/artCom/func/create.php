<?php

namespace core\comp\spl\artCom\func;

// Core
use core\classes\request;
use core\classes\render;
use core\classes\word;
use core\classes\filesystem;

// ORM
use ORM\comp\spl\artCom\artComBi as artComBiOrm;
use ORM\comp\spl\artCom\artComProp as artCompPropOrm;
use ORM\comp\spl\artCom\artCom as artComOrm;
use ORM\tree\componentTree as componentOrm;
use ORM\tree\compContTree as compContTreeOrm;
use ORM\blockItemSettings;

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
        $acticleId = request::getInt('acticleId');

        // Текст комментария
        $comment = request::postSafe('comment');
        $author = request::postSafe('author');

        //$commentPostId = request::postInt('comment_post_ID');
        // ID родителя комментария, т.е. к какому комментарию он относится
        $parentId = request::postInt('parentId');

        // Получаем настроечные данные
        $artComBiOrm = new artComBiOrm();
        $artComData = $artComBiOrm
            ->select('acb.tplListFile, acb.tplComFile, acp.type, c.ns, c.id as compId', 'acb')
            ->join(blockItemSettings::TABLE . ' bis', 'bis.blockItemId=acb.blockItemId')
            ->join(artCompPropOrm::TABLE . ' acp', 'acp.contId=bis.custContId')
            ->join(compContTreeOrm::TABLE . ' cc', 'cc.id = bis.custContId')
            ->join(componentOrm::TABLE . ' c', 'c.id = cc.comp_id')
            ->where('acb.blockItemId=' . $blockItemId)
            ->comment(__METHOD__)
            ->fetchFirst();
        if (!$artComData) {
            throw new \Exception('No data from blockItemId=' . $blockItemId);
        }
        // Название шаблона для комментариев
        $tplListFile = substr($artComData['tplListFile'], 1);
        $tplComFile = substr($artComData['tplComFile'], 1);
        // Тип комментариев
        $artComType = $artComData['type'];


        $artComOrm = new artComOrm();

        $comList = $artComOrm->selectFirst(
            'id',
            ['type' => $artComType, 'objId' => $acticleId]
        );
        $isFirst = !(boolean)$comList;
        unset($comList);


        $artComOrm->insert(['userName' => $author,
                           'comment' => $comment,
                           'tree_id' => $parentId,
                           'type' => $artComType,
                           'objId' => $acticleId
                           ]);
        $newId = $artComOrm->insertId();

        // ======= Создаём код Комментария
        // Преобразуем NameSpace имя в путь папки
        $nsPath = filesystem::nsToPath($artComData['ns']);
        $tplDir = DIR::SITE_CORE . 'tpl/' . SITE::THEME_NAME . '/comp/' . $nsPath;
        // Создаём шаблон
        $render = new render($tplDir, '');
        $render->setMainTpl($tplComFile)
            ->setVar('author', $author)
            ->setVar('comment', $comment)
            ->setVar('id', $newId)
            ->setVar('isFirst', $isFirst)
            ->setVar('dateAdd', date("d-m-y H:i"))
            ->render();

        // Формируем правильное дерево 
        $comList = $artComOrm->selectAll(
            'id, tree_id',
            ['type' => $artComType, 'objId' => $acticleId],
            'tree_id, date_add');

        $idString = '';
        // Получаем правильную последовательность ID
        self::makeList(0, $comList, $idString);
        $idString = substr($idString, 1);
        unset($comList);

        // ======= Создаём код Списка комментариев и сохраняем в файл
        $comListHandle = $artComOrm
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
        $acticleId = word::idToSplit($acticleId);
        $folder = DIR::APP_DATA . 'comp/' . $artComData['compId'] . '/' . $artComType . '/' . $acticleId;
        filesystem::saveFile($folder, 'comm.html', $data);
        // func. save
    }

    // func. createArtCom
}