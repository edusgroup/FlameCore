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
use ORM\compprop as compPropOrm;

// Model
use admin\library\mvc\manager\varible\model as varModel;
use admin\library\mvc\manager\complist\model as complistModel;

// Init
use admin\library\init\comp as compInit;

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
        $acId = self::getInt('acid', 0);
        self::setVar('acId', $acId);

        $isLock = self::getInt('islock');
        self::setVar('isLock', $isLock);

        // Загружаем сохранённые настройки
        if ($itemData['isSaveProp']) {
            // Загрузаем методы класса компонента
            $classData = model::getSiteClassData($itemData['classFile'], $blockItemId);
            self::setJson('classData', $classData);

            $tableOrm = null;
            // Если есть деление на таблицу и сохранён статический ID элемента таблиц, то нужно вытащить его название
            if ($onlyFolder && isset($itemData['statId'])) {
                $className = comp::fullNameClassAdmin($itemData['classFile'], $itemData['ns']);
                $contrAdminObj = new $className('', '');
                $tableOrm = $contrAdminObj->getTableOrm();

                $statName = $tableOrm->get('caption', 'id=' . (int)$itemData['tableId']);
                self::setVar('statName', $statName);
            } // if

            $regxList = model::loadRegxList($blockItemId, $tableOrm, $onlyFolder);
            self::setJson('regxList', $regxList);

            // Избавление от NULL
            $itemData['statId'] = $itemData['statId'] ? : '';
            $itemData['tableId'] = $itemData['tableId'] ? : '';
        } // if (saveData)

        self::setJson('saveData', $itemData);

        // Получаем дерево контента
        $contTree = dhtmlxTree::createTreeOfTable(
            new compContTree(),
            ['comp_id' => $itemData['compId'], 'isDel' => 'no']);
        self::setJson('contTree', $contTree);

        // Получаем дерево Action, только с обычными папка и переменными
        dhtmlxTree::setField(['propType']);
        $tree = dhtmlxTree::createTreeOfTable(
            new routeTree(),
            'propType in (0,1) AND isDel = 0'
        );
        dhtmlxTree::clear();
        self::setJson('actionTree', $tree);

        $nsPath = filesystem::nsToPath($itemData['ns']);

        $tree = model::getClassTree($nsPath);
        self::setJson('classTree', $tree);

        // Дерево с шаблонами сайта для компонента
        $treeTpl = model::getTplTree($nsPath);
        self::setJson('tplTree', $treeTpl);

        if ($acId) {
            $routeTree = new routeTree();
            //$treeUrl = $routeTree->getTreeUrlById(routeTree::TABLE, $acId);
            //if ($treeUrl) {
                $varList = varModel::getVarList($routeTree, $acId);
                array_unshift($varList, ['name' => '---', 'id' => '']);
                self::setVar('varList', ['list' => $varList]);
           // } // if
        } // if ($acId)

        if ($itemData['isSaveProp']) {
            $urlTplListOrm = new urlTplListOrm();
            $urlTplArr = $urlTplListOrm->selectAll('name, acId', 'blockItemId=' . $blockItemId);
            $urlTplList = [];
            foreach ($urlTplArr as $item) {
                $name = $item['name'];
                if (in_array($name, $classData['urlTpl'])) {
                    $urlTplList[$name] = $item['acId'];
                }
            } // foreach
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

        $classFile = self::get('classFile');

        $compData = comp::getCompPropByContId($contId);
        if ( !$compData ){
            throw new \Exception('ContId не найден: ' . $contId, 25);
        }

        $className = comp::fullNameClassAdmin($classFile, $compData['ns']);;
        $adminObj = new $className('', '');

        if (!method_exists($adminObj, 'getTableData')) {
            throw new \Exception(' getTableData не найден: ' . $contId, 24);
        }
        $contList = $adminObj->getTableData($contId);
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

        // varId представляет собой ветку в дереве actionTree, помеченной как переменная
        $varId = self::postInt('varName', 0);
        if ( $varId ){
            $isVar = (new routeTree())->get('id', 'id='.$varId);
            if ( !$isVar ){
                throw new \Exception('Varible not found: '.$varId);
            }
        } // if $varId

        $saveData = [
            'blockItemId' => $blockItemId,
            'tplFile' => self::post('tplFile'),
            'classFile' => self::post('classFile'),
            'methodName' => self::post('methodName'),
            // Данные по статичному контенту
            'statId' => self::postInt('statId', null),
            'tableId' => self::postInt('tableId', null),
            'varId' => $varId,
            'varTableId' => self::postInt('varTableName', null)
        ];

        (new blockItemSettings())->save('blockItemId=' . $blockItemId, $saveData);
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

                $saveData = [
                    'blockItemId' => $blockItemId,
                    'regexp' => $regxList[$i],
                    'contId' => $contId,
                    'tableId' => $tableId
                ];
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
                    ['blockItemId' => $blockItemId,
                    'name' => $name,
                    'acId' => $acId]
                );
            } // foreach
        } // if is_array
		unset($urlTpl);
        // func. saveDataAction
    }

    public function customSettingsAction() {

        // Получаем Id компонента в блоке
        $blockItemId = self::getInt('blockitemid');
        self::setVar('blockItemId', $blockItemId);

        // Получаем ID компонента для объекта
        $itemData = model::getCompData($blockItemId);
        self::setVar('custContId', $itemData['custContId']);

        $acId = self::getInt('acid', 0);
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
        $custContId = self::postInt('contid');

        // Получаем настройки ветки
        $objProp = comp::getBrunchPropByContId($custContId);
        if ( !$objProp ){
            $objProp = comp::findCompPropUpToRoot($custContId);
        }
        // Проверяем нашли мы что то, если нет то говорит что ошибка поиска
        if ( !$objProp ){
            throw new \Exception('Prop on contId: ' . $custContId . ' not found', 345);
        } // if

        // Имя класса который задали в настройках
        $classFile = $objProp['classFile']?: '/base/'.$objProp['classname'].'.php';

        $className = comp::fullNameClassAdmin($classFile, $objProp['ns']);;
        $adminObj = new $className('', '');
        if (!method_exists($adminObj, 'blockItemSave')) {
            throw new \Exception(' getTableData не найден', 24);
        }
        $adminObj->blockItemSave($blockItemId, $this);

        (new blockItemSettings())->update(
            'custContId=' . $custContId
            ,'blockItemId=' . $blockItemId);

        $eventData = ['biId' => $blockItemId];
        eventsys::callOffline(event::BLOCKITEM, event::CHANGE, $eventData);
        // func. custSettSaveAction
    }

    // class blockItem
}