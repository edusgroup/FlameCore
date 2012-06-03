<?php

namespace admin\library\mvc\manager\blockItem;

// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;

// Engine
use core\classes\validation\word;
use core\classes\render;
use core\classes\filesystem;
use core\classes\comp;
use core\classes\event as eventsys;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// Orm
use ORM\blockItem as blockItemOrm;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\tree\routeTree;
use ORM\blockItemSettings;
use ORM\blockItemRegxUrl;
use ORM\urlTplList as urlTplListOrm;

// Model
use admin\library\mvc\manager\varible\model as varModel;
use admin\library\mvc\manager\complist\model as complistModel;

/**
 *
 * @author Козленко В.Л.
 */
class blockItem extends \core\classes\mvc\controllerAbstract {

    public function init() {

    }

    /**
     * Отображение и редактирование параметров blockItem
     */
    public function indexAction() {
        // Получаем Id компонента в блоке
        $blockItemId = self::getInt('id');
        self::setVar('blockItemId', $blockItemId);

        // Получаем ID компонента для объекта
        $itemData = model::getCompData($blockItemId);
        self::setVar('descr', $itemData['name']);
        self::setVar('sysname', $itemData['sysname']);
        self::setVar('compname', $itemData['compname']);
        // Важный параметр. Есть ли у компонента разделение на таблицу
        $onlyFolder = (int)$itemData['onlyFolder'];
        self::setVar('onlyfolder', $onlyFolder);

        $acId = self::getInt('acid', '');
        self::setVar('acId', $acId);

        self::setVar('acParent', (int)($itemData['acId'] == $acId));

        $blockItemSettings = new blockItemSettings();
        // Загружаем сохранённые настройки
        $saveData = $blockItemSettings->selectFirst('*', 'blockItemId=' . $blockItemId);
        if ($saveData) {
            // Загрузаем методы класса компонента
            $classData = model::getSiteClassData($saveData['classFile'], $blockItemId);
            self::setJson('classData', $classData);
            $tableOrm = null;
            if ($onlyFolder && isset($saveData['statId'])) {
                $objProp = comp::getCompContProp((int)$saveData['statId']);
                $contrObj = comp::getCompObject($objProp);
                $tableOrm = $contrObj->getTableOrm();
                $statName = $tableOrm->get('caption', 'id=' . (int)$saveData['tableId']);
                self::setVar('statName', $statName);
            } // if

            $regxList = model::loadRegxList($blockItemId, $tableOrm, $onlyFolder);
            self::setJson('regxList', $regxList);

            // Избавление от NULL
            $saveData['statId'] = $saveData['statId'] ? : '';
            $saveData['tableId'] = $saveData['tableId'] ? : '';
        } // if (saveData)

        self::setJson('saveData', $saveData);

        // Получаем дерево контента
        $tree = dhtmlxTree::createTreeOfTable(
            new compContTree(),
            ['comp_id' => $itemData['compId'],
            'isDel' => 'no']);
        self::setJson('contTree', $tree);

        // Получаем дерево Action, только с обычными папка и переменными
        dhtmlxTree::setField(['propType']);
        $tree = dhtmlxTree::createTreeOfTable(
            new routeTree(),
            'propType in (0,1) AND isDel = 0'
        );
        dhtmlxTree::clear();
        self::setJson('actionTree', $tree);


        $nsPath = filesystem::nsToPath($itemData['ns']);

        // Дерево с классами сайта для компонента
        $siteClassPath = DIR::CORE.comp::getFullCompClassName(null, $itemData['ns'], 'logic', '');
        $siteClassPath = filesystem::nsToPath($siteClassPath);
        $tree = dhtmlxTree::createTreeOfDir($siteClassPath);
        self::setJson('classTree', $tree);

        // Дерево с шаблонами сайта для компонента
        $siteTplPath = DIR::getSiteCompTplPath($nsPath);
        $tree = dhtmlxTree::createTreeOfDir($siteTplPath);
        self::setJson('tplTree', $tree);

        if ($acId) {
            $routeTree = new routeTree();
            $treeUrl = $routeTree->getTreeUrlById(routeTree::TABLE, $acId);
            if ($treeUrl) {
                $varList = varModel::getVarList($routeTree, $treeUrl);
                array_unshift($varList, ['name' => '---', 'id' => '']);
                self::setVar('varList', ['list' => $varList]);
            } // if
        } // if ($acId)

        self::setVar('typeCont', 1);

        if ($saveData) {
            $urlTplListOrm = new urlTplListOrm();
            $urlTplArr = $urlTplListOrm->selectAll('name, acId', 'blockItemId=' . $blockItemId);
            $urlTplList = [];
            foreach ($urlTplArr as $item) {
                $name = $item['name'];
                if (in_array($name, $classData['urlTpl'])) {
                    $urlTplList[$name] = $item['acId'];
                }
            }
            self::setJson('urlTplList', $urlTplList);
        } // if

        $this->view->setBlock('panel', 'block/blockItem.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Возвращает методы от класса сайта
     */
    public function loadClassMethodAction() {
        $this->view->setRenderType(render::JSON);
        // Название класса
        $classFile = self::get('class');
        $blockItemId = self::getInt('blockitemid');
        // Получаем методы класа
        $classData = model::getSiteClassData($classFile, $blockItemId);

        self::setVar('json', $classData);
        // func. loadClassMethodAction
    }

    /**
     * Получаем табличный контент, если у компонента onlyFolder = 1
     * @throws \Exception если метод getTableData у класса не найден
     */
    public function loadCompTableAction() {
        $this->view->setRenderType(render::JSON);
        $contId = self::getInt('contid');

        $objProp = comp::getCompContProp($contId);
        $contrObj = comp::getCompObject($objProp);

        if (!method_exists($contrObj, 'getTableData')) {
            throw new \Exception(' getTableData не найден: ' . $contId, 24);
        }
        $contList = $contrObj->getTableData($contId);
        self::setVar('json', $contList);
        // func. loadCompTableAction
    }

    /**
     * Сохраняет данные в таблицы
     * @throws \Exception
     */
    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);

        $blockItemId = self::getInt('id');
        $acId = self::getInt('acid');

        $eventData = ['biId' => $blockItemId];
        eventsys::callOffline(event::BLOCKITEM, event::CHANGE, $eventData);

        $saveData = [
            'blockItemId' => $blockItemId,
            'tplFile' => self::post('tplFile'),
            'classFile' => self::post('classFile'),
            'methodName' => self::post('methodName'),
            // Данные по статичному контенту
            'statId' => self::postInt('statId', null),
            'tableId' => self::postInt('tableId', null),
            'varId' => self::postInt('varName', null),
            'varTableId' => self::postInt('varTableName', null)
        ];

        $blockItemSettings = new blockItemSettings();
        $blockItemSettings->save('blockItemId=' . $blockItemId, $saveData);
		unset($saveData);

        $blockItemRegxUrl = new blockItemRegxUrl();
        // Очищаем всё
        $blockItemRegxUrl->delete('blockItemId=' . $blockItemId);

        $contList = self::post('cont');
        $regxList = self::post('regx');
        if (is_array($regxList)) {
            foreach ($regxList as $i => $item) {
                if (!isset($contList[$i])) {
                    throw new \Exception('Рассинхронизация данных: cont и regx', 32);
                } // if
                $contId = isset($contList[$i]['contid']) ? (int)$contList[$i]['contid'] : '';
                $tableId = isset($contList[$i]['tableid']) ? (int)$contList[$i]['tableid'] : '';

                $saveData = array(
                    'blockItemId' => $blockItemId,
                    'regexp' => $regxList[$i],
                    'contId' => $contId,
                    'tableId' => $tableId
                );
                $blockItemRegxUrl->insert($saveData);
            } // foreach
        } // if is_array
		unset($regxList, $saveData, $contList);
		
		$where = $acId ? ' AND id='.$acId : '';
		(new routeTree())->update('isSave="yes"', 'id != 0'.$where);

        $urlTpl = self::post('urlTpl');
        if (is_array($urlTpl)) {
            $urlTplListOrm = new urlTplListOrm();
            $urlTplListOrm->delete('blockItemId=' . $blockItemId);
            foreach ($urlTpl as $name => $acId) {
                $urlTplListOrm->insert(
                    [
                    'blockItemId' => $blockItemId,
                    'name' => $name,
                    'acId' => $acId
                    ]
                );
            } // foreach
        } // if is_array
		unset($urlTpl);

        self::setVar('json', []);
        // func. saveDataAction
    }

    public function customSettingsAction() {

        // Получаем Id компонента в блоке
        $blockItemId = self::getInt('blockitemid');
        self::setVar('blockItemId', $blockItemId);

        $blockItemSettings = new blockItemSettings();
        $custContId = $blockItemSettings->get('custContId', 'blockItemId=' . $blockItemId);
        self::setVar('custContId', $custContId);

        // Получаем ID компонента для объекта
        $itemData = model::getCompData($blockItemId);

        $acId = self::getInt('acid', '');
        self::setVar('acId', $acId);

        // Получаем дерево контента
        $tree = dhtmlxTree::createTreeOfTable(
            new compContTree(),
            ['comp_id' => $itemData['compId']]
        );

        self::setJson('contTree', $tree);

        $this->view->setBlock('panel', 'block/blockItemCusSett.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. customSettingsAction
    }

    public function custSettSaveAction() {
        $this->view->setRenderType(render::JSON);

        $blockItemId = self::getInt('blockitemid');

        $eventData = ['biId' => $blockItemId];
        eventsys::callOffline(event::BLOCKITEM, event::CHANGE, $eventData);

        $custContId = self::postInt('contid');

        $blockItemSettings = new blockItemSettings();
        $blockItemSettings->update(
            'custContId=' . $custContId
            , 'blockItemId=' . $blockItemId);

        // Создаём объекта класса
        $objProp = comp::getCompContProp($custContId);
        $contrObj = comp::getCompObject($objProp);

        if (!method_exists($contrObj, 'blockItemSave')) {
            throw new \Exception(' getTableData не найден', 24);
        }
        $contrObj->blockItemSave($blockItemId, $this);

        // func. custSettSaveAction
    }

    // class blockItem
}

?>