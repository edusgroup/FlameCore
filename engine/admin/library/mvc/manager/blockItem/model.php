<?php

namespace admin\library\mvc\manager\blockItem;

// Orm
use ORM\blockItem as blockItemOrm;
use ORM\blockItemRegxUrl;
use ORM\tree\componentTree;
use ORM\blockItemSettings;

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
        $itemData = (new blockItemOrm())
            ->select('c.classname, c.ns, t.compId, c.onlyFolder, c.name as compname, t.*, bis.blockItemId isSaveProp,bis.*', 't')
            ->join(componentTree::TABLE . ' c', 't.compId = c.id')
            ->joinLeftOuter(blockItemSettings::TABLE.' bis', 'bis.blockItemId=t.id')
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

    private static function _getBrunchParam($pType, $pText){
        return ['id' => $pType,
                'text' => $pText,
                'im0' => 'folderClosed.gif',
                'userdata' => [['name' => 'type', 'content' => dhtmlxTree::FOLDER]]];
        // func. _getBrunchParam
    }


    public static function getClassTree($pNsPath){
        // Встроенные шаблоны для компонента для сайта
        $classFilePath = comp::getCompClassSitePath(false, $pNsPath);
        $treeInner = dhtmlxTree::createTreeOfDir($classFilePath);
        $treeInner = array_merge($treeInner, self::_getBrunchParam('#in', 'Встроеные'));

        // Внешние шаблоны компонента для сайта
        $classFilePath = comp::getCompClassSitePath(false, $pNsPath);
        // Добавляем префикс, что бы если встретятся одинаковый папки, были разные ID
        $treeOuter = dhtmlxTree::createTreeOfDir($classFilePath, '[o]');
        $treeOuter = array_merge($treeOuter, self::_getBrunchParam('#out', 'Внешние'));
        $treeClass = ['id' => 0, 'item' => [$treeInner, $treeOuter]];
        return $treeClass;
        // func. getClassTree
    }

    public static function getTplTree($nsPath){
        // Встроенные шаблоны для компонента для сайта
        $siteTplPath = DIR::getSiteCompTplPath($nsPath);
        $treeInner = dhtmlxTree::createTreeOfDir($siteTplPath);
        $treeInner = array_merge($treeInner, self::_getBrunchParam('#in', 'Встроеные'));

        // Внешние шаблоны компонента для сайта
        $siteTplPath = DIR::getSiteCompTplOuter($nsPath);
        // Добавляем префикс, что бы если встретятся одинаковый папки, были разные ID
        $treeOuter = dhtmlxTree::createTreeOfDir($siteTplPath, '[o]');
        $treeOuter = array_merge($treeOuter, self::_getBrunchParam('#out', 'Внешние'));
        $treeTpl = ['id'=>0, 'item'=>[$treeInner, $treeOuter]];
        unset($treeInner, $treeOuter);
        return $treeTpl;
        // func. getTplTree
    }

    /**
     * Возвращает методы класса сайта
     * @param string $pClassFile имя класса
     * @param integer $pBlockItemId block Item Id см. табл. blockItem
     * @return array
     */
    public static function getSiteClassData(string $pClassFile, integer $pBlockItemId) {
        // Если Класс не был выбран, возвращаем пустой массив
        if (!$pClassFile) {
            return [];
        }

        // Получаем информацию по компоненту, который указан для блока
        $itemData = self::getCompData($pBlockItemId);

        $compObj = comp::createClassSiteObj($pClassFile, $itemData['ns']);
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