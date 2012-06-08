<?php

namespace buildsys\library\event\comp\utils;

//ORM
use ORM\tree\compContTree as compContTreeOrm;
use ORM\tree\componentTree;
use ORM\comp\spl\oiComment\oiComment as oiCommentOrm;
// Conf
use \DIR;
// Engine
use core\classes\filesystem;
use core\classes\userUtils;

/**
 * @author Козленко В.Л.
 */
class gc {
    public static function clearResurse($pUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName = "tree:delete"');
        if (!$isData) {
            return;
        }

        // Получаем список контента на удаление
        $compContTree = new compContTreeOrm();
        $contList = $compContTree->selectAll('id, comp_id', 'isDel="yes"');
        if (!$contList) {
            return;
        }

        // Буффер для ID которые удалились
        $whereIdList = [];
        $contListCount = count($contList);
        for ($i = 0; $i < $contListCount; $i++) {
            $contId = $contList[$i]['id'];
            $compId = $contList[$i]['comp_id'];
            $pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
            userUtils::rmFolder($pathPrefix);

            $whereIdList[] = $contId;
        } // if

        // Удаление комментариев, если они есть для удаления
        $whereIdList = implode(',', $whereIdList);
        $compContTree->delete('id in ('.$whereIdList.')');

        // func. clearResurse
    }

    // class. gc
}