<?php

namespace buildsys\library\event\comp\spl\menu;

// ORM
use ORM\tree\comp\menu as menuOrm;
use ORM\tree\compContTree as compContTreeOrm;

// Event comp
use admin\library\mvc\comp\spl\menu\event as eventMenu;

// Engine
use core\classes\tree;
use core\classes\filesystem;
use core\classes\admin\dirFunc;

// Conf
use \DIR;

/**
 * Обработчик событий для меню
 *
 * @author Козленко В.Л.
 */
class event {
    public static function creatFileArray($pUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        // Вытаскием все contId из таблицы compContTreeOrm, по которым были сохранения
        $menuIdList = $pEventBuffer
            ->select('userId as contId, cc.comp_id compId', 'eb')
            ->join(compContTreeOrm::TABLE . ' cc', 'eb.userId=cc.id')
            ->where('eventName = "' . eventMenu::ACTION_SAVE . '" AND cc.isDel="no"')
            ->group('contId')
            ->comment(__METHOD__)
            ->fetchAll();

        // Если есть из данных, обрабатываем
        if ($menuIdList) {
            foreach ($menuIdList as $item) {
                $contId = $item['contId'];

                // Получаем дерево меню
                // sortValue - должен быть на первом месте, иначе сортировка будет неверной
                tree::setField(['sortValue', 'name', 'tree_id', 'id', 'link', 'nofollow', 'class']);
                $compTree = tree::createTreeOfTable(new menuOrm(), 'contId=' . $contId);

                // Сортируем по полю sortValue
                model::rSortTree($compTree);

                $saveDir = 'comp/' . $item['compId'] . '/' . $contId . '/';
                $saveDir = dirFunc::getSiteDataPath($saveDir);

                // Общие данные по меню: название меню и т.п.
                $compTree['public'] = filesystem::loadFileContentUnSerialize($saveDir.'public.txt');

                // Сериализуем
                $data = \serialize($compTree);
                //var_dump($compTree);
                //exit;

                // Записываем в файл
                filesystem::saveFile($saveDir, 'menu.txt', $data);
            } // foreach
        }
        // if

        // func. creatFileArray
    }
    // class event
}