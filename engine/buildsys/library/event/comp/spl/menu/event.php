<?php

namespace buildsys\library\event\comp\spl\menu;

// ORM
use ORM\event\eventClass;
use ORM\tree\comp\menu as menuOrm;
use ORM\tree\compContTree as compContTreeOrm;
// Event comp
use admin\library\mvc\comp\spl\menu\event as eventMenu;
// Plugin
//use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;
// Engine
use core\classes\tree;
use core\classes\filesystem;
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
            ->join(compContTreeOrm::TABLE.' cc', 'eb.userId=cc.id')
            ->where('eventName = "'.eventMenu::ACTION_SAVE.'" AND cc.isDel="no"')
            ->group('contId')
            ->fetchAll();

        // Если есть из данных, обрабатываем
        if ( $menuIdList ){
            foreach( $menuIdList as $item ){
                $contId = $item['contId'];
                //$userData = unserialize($item['userData']);

                // Получаем дерево меню
                tree::setField(array( 'name', 'tree_id', 'id', 'link', 'nofollow', 'class'));
                $compTree = tree::createTreeOfTable(
                        new menuOrm(), 
                        'contId='.$contId
                );
                // Сериализуем
                $data = \serialize($compTree);
                $saveDir = 'comp/'.$item['compId'] . '/' . $contId . '/';
                $saveDir = DIR::getSiteDataPath($saveDir);
                // Записываем в файл
                filesystem::saveFile($saveDir, 'menu.txt', $data);
            } // foreach
        }// if  
        // func. creatFileArray
    }
// class event
}

?>
