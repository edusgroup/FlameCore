<?php

namespace admin\library\mvc\manager\blockItem;

// Orm
use ORM\blockItem as blockItemOrm;
use ORM\blockItemRegxUrl;
use ORM\tree\componentTree;
use ORM\block\blockLink as blockLinkOrm;

// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;
use site\conf\DIR as SITE_DIR;

// Engine
use core\classes\mvc\controllerAbstract;
use core\classes\arrays;
use core\classes\validation\word;
use core\classes\filesystem;
use core\classes\comp;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * Модель для контроллера blockItem
 *
 * @author Козленко В.Л.
 */
class model {

    /**
     * Возвращает данные по компоненту выбранного для blockItem
     * @param integer $pItemId block Item Id см. табл. blockItem
     * @return array
     * @throws \Exception если blockItem не был найден
     */
    public static function getCompData(integer $pItemId) {
        // Получаем данные по данному blockItem
        $blockItemOrm = new blockItemOrm();
        $itemData = $blockItemOrm->select('c.classname, c.ns, t.compId, c.onlyFolder, c.name as compname, t.*', 't')
            ->join(componentTree::TABLE . ' c', 't.compId = c.id')
            ->where('t.id=' . $pItemId)
            ->comment(__METHOD__)
            ->fetchFirst();

        // Если данных нет, то генерим исключение, потому что не найден blockItem
        if (!$itemData) {
            throw new \Exception('BlockItem not found [' . __METHOD__ . '(itemId=>' . $pItemId . ')]', 239);
        }

        return $itemData;
        // func. getCompData
    }

    /*public static function isBlockLink(integer $pId){
        $return = (new blockItemOrm())
            ->select('bl.*', 'bi')
            ->join(blockLinkOrm::TABLE.' bl', 'bi.block_id=bl.linkBlockId and bi.wf_id=bl.linkMainId')
            ->where('bi.id='.$pId)
            ->fetchAll();

        return (boolean)$return;
        // func. isBlockLink
    }*/

    public static function getClassTree($pNs, $pClassType){
        $siteClassPath = DIR::CORE;
        if ( $pClassType == 'user' ){
            $siteClassPath = SITE_DIR::SITE_CORE;
        } // if
        $siteClassPath .= comp::getFullCompClassName('', $pNs, 'logic', '');
        $siteClassPath = filesystem::nsToPath($siteClassPath);
        return dhtmlxTree::createTreeOfDir($siteClassPath);
        // func. getClassTree
    }

    /**
     * Возвращает методы класса сайта
     * @param string $pClassFile имя класса
     * @param integer $pBlockItemId block Item Id см. табл. blockItem
     * @return array
     */
    public static function getSiteClassData(string $pClassFile, integer $pBlockItemId, string $pClassType) {
        // Если Класс не был выбран, возвращаем пустой массив
        if (!$pClassFile) {
            return [];
        }
        // Убираем начальный слеш и окончание .php
        // TODO: Заменить на норм обработку из класса word
        $className = substr($pClassFile, 1, strlen($pClassFile) - 5);
        $className = str_replace('/', '\\', $className);
        word::isNsClassName(
            $className
            , new \Exception('Bad Ns name: [' . __METHOD__ . '(className=>' . $className . ')]', 23)
        );

        // Получаем информацию по компоненту
        $itemData = self::getCompData($pBlockItemId);
        //$className = '{user\}core\comp\\' . $itemData['ns'] . 'logic\\' . $className;

        $classFullName = comp::getFullCompClassName($pClassType, $itemData['ns'], 'logic', $className);
        $compObj = new $classFullName();

        $methodList = get_class_methods($compObj);
        // Фильтруем методы. Нам нужны только в окончанием Action
        $methodList = array_filter($methodList, function($pItem) {
            return substr($pItem, -6) == 'Action';
        });
        // Получаем его методы
        return [
            'method' => $methodList,
            'urlTpl' => isset($compObj::$urlTplList) ? array_keys($compObj::$urlTplList) : null
        ];
        // func. getSiteClassData
    }

    /**
     * Возвращает список сохранёных данных по URL regexp
     * @param integer $pBlockItemId block Item Id см. табл. blockItem
     * @param table $tableOrm ORM компонента
     * @param boolean $onlyFolder значение onlyFolder компонента. см. таблицу component
     * @return array
     */
    public static function loadRegxList(integer $pBlockItemId, $tableOrm, $onlyFolder) {
        $blockItemRegxUrl = new blockItemRegxUrl();
        // Хотим получить фильтр(regexp), выбранный ID контента, и если onlyFolder = 1
        // то получим выбранное табличное значение
        $select = 'ru.`regexp`, ru.contId, ru.tableId';
        // если onlyFolder = 1, так же получим название табличной записи
        if ($onlyFolder && $tableOrm) {
            $select .= ', t.caption';
        }
        $blockItemRegxUrl->select($select, 'ru');
        // если onlyFolder = 1, то для получения название табличной записи
        // нужно сделать Left join, так как компонентов много, по этому сюда передаётся
        // его ORM
        if ($onlyFolder && $tableOrm) {
            $blockItemRegxUrl->joinLeftOuter($tableOrm::TABLE . ' t', 'ru.tableId=t.id');
        }
        $regxList = $blockItemRegxUrl->where('ru.blockItemId=' . $pBlockItemId)
            ->comment(__METHOD__)
            ->fetchAll();
        return $regxList;
        // func. loadRegxList
    }

    // class blockItem
}