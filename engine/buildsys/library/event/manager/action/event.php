<?php

namespace buildsys\library\event\manager\action;

// ORM
use ORM\tree\routeTree;
use ORM\event\eventBuffer;
//Engine
use core\classes\webserver\nginx;
use core\classes\filesystem;
// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;
//
use admin\library\mvc\manager\action\event as eventAction;
/**
 * Обработчик событий для каталога URL
 *
 * @author Козленко В.Л.
 */
class event {

    /**
     * Удаление ветки каталога в дереве URL
     * @param mixed $pListerUserData
     * @param array $pOwnUserDataList
     * @return void 
     */
    public static function rmBrunch($pListerUserData, $pOwnUserDataList) {
        // Получаем список контента на удаление
        $routeTree = new routeTree();
        $itemList = $routeTree->selectAll('id, propType', 'isDel=1');
        if ( !$itemList ){
            return;
        }
        
        // Буффер для ID которые удалились
        $whereIdList = '';
        
        $itemListCount = count($itemList);
        for ($i = 0; $i < $itemListCount; $i++) {
            $acId = (int)$itemList[$i]['id'];
            $path = eventModel::getActionPath($acId, $routeTree);
            $folder = DIR::getSiteRoot().$path;
            filesystem::rmdir($folder);
            
            $whereIdList .= ',' . $acId;
        }
        
        $whereIdList = substr($whereIdList, 1);
        
        // Удаление предыдуших событий, так как объекта уже нет
        $eventBuffer = new eventBuffer();
        $eventBuffer->delete(
                'eventName IN ("'.eventAction::PROP_SAVE.'","'.eventAction::ITEM_CRATE.'")'
                .'AND userId IN (' . $whereIdList . ')');
        
        $routeTree->delete('id IN (' . $whereIdList . ')');
        // func. rmBrunch
    }
    
    public static function createFolder($pListerUserData, $pOwnUserDataList) {
        $routeTree = new routeTree();
        $itemList = $routeTree->selectAll('id, propType', 'isCreate="yes" and isDel=0');
        if ( !$itemList ){
            return;
        }
        $itemListCount = count($itemList);
        for ($i = 0; $i < $itemListCount; $i++) {
            $acId = (int)$itemList[$i]['id'];
            // Создаём новые папки
            $path = eventModel::getActionPath($acId, $routeTree);
            if ( in_array($path, ['{index}/', '{404}/', '{500}/'])){
                continue;
            }
            $folder = DIR::getSiteRoot().$path;
            filesystem::mkdir($folder);
            // TODO: Убрать запись Debug only
            if ( !is_file($folder.'index.php')){
                filesystem::saveFile($folder, 'index.php', 'Debug only. Creating: '.$folder);
            }
        } // for $i

        nginx::createConf($routeTree);

        $idList = array_map(function($pItem){
            return $pItem['id'];
        }, $itemList);
        $idList = implode(',', $idList);
        $routeTree->update('isCreate="no"', 'id in ('.$idList.')');
        // func. createFolder
    }
    
    /**
     * Создание страницы
     * @param type $pListerUserData
     * @param type $pOwnUserDataList
     * @return type 
     */
    public static function createItems($pListerUserData, $pOwnUserDataList) {
        // Получаем список контента, те элемента у которых стоит флаг Сохранить и 
        // Которые не удалены
        $routeTree = new routeTree();
        $itemList = $routeTree->selectAll('id, propType', 'isSave="yes" and isDel=0');
        // Если таких нет, то выходим
        if ( !$itemList ){
            return;
        }
        // Бегаем по элементам
        $itemListCount = count($itemList);
        for ($i = 0; $i < $itemListCount; $i++) {
            // Получаем actionId
            $acId = (int)$itemList[$i]['id'];
            // Получаем URL папки по actionId
            $path = eventModel::getActionPath($acId, $routeTree);
            // TODO: сделать константой 2
            if ( $itemList[$i]['propType'] == 2 ){
                // Создание функционального класса
            }else{
                // Создание WF
                //f 'Func: createFileTpl() start'.PHP_EOL;
                $filename = 'index.php';
                if ( in_array($path, ['{index}/', '{404}/', '{500}/'])){
                    $filename = substr($path, 1, strlen($path) - 3).'.php';
                    $path = '';
                } // if in_array
                $folder = DIR::getSiteRoot() . $path;
                $codeBuffer = eventModel::createFileTpl($folder, $acId, $itemList[$i]['propType'], $routeTree);
                if ( $codeBuffer ){
                    filesystem::saveFile($folder, $filename, $codeBuffer);
                }
                //echo 'Func: createFileTpl() end'.PHP_EOL;
            }
        } // for $i

        $idList = array_map(function($pItem){
            return $pItem['id'];
        }, $itemList);
        $idList = implode(',', $idList);
        $routeTree->update('isSave="no"', 'id in ('.$idList.')');
        // func. createItems
    }

// class event
}