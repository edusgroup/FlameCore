<?php

namespace admin\library\mvc\manager\wareframe;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;
use admin\library\mvc\plugin\dhtmlx\model\grid as dhtmlxGrid;

// Conf
use \site\conf\SITE as SITE_CONF;
use \DIR;

// Engine
use core\classes\filesystem;
use core\classes\valid;
use core\classes\render;
use core\classes\storage\storage;
use core\classes\mvc\controllerAbstract;
use core\classes\event as eventsys;
use core\classes\admin\dirFunc;

// ORM
use ORM\blockItem;
use ORM\tree\componentTree;
use ORM\tree\wareframeTree;
use ORM\tree\routeTree as routeTreeOrm;
use ORM\urlTreePropVar;
use ORM\tree\compContTree;
use ORM\block\blockLink as blockLinkOrm;

// Event
use admin\library\mvc\manager\blockItem\event as eventBlockItem;

// Model
use admin\library\mvc\manager\action\model as actionModel;

/**
 * Логика настроек сайта
 *
 * @author Козленко В.Л.
 */
class wareframe extends controllerAbstract {

    public function init() {

    }

    /**
     * Построение страницы
     */
    public function indexAction() {
        // Получаем ID action
        $acId = self::getInt('acid');
        self::setVar('acId', $acId);

        $urlTreePropVar = new urlTreePropVar();
        $wfId = $urlTreePropVar->getWFId($acId);
        //if ( !$wfId )
        //    throw new \Exception('WF не найден', 25);
        self::setVar('wfId', $wfId);

        $blockId = self::get('blid');
        if ($blockId) {
            blockItem::validBlockId($blockId, new \Exception('Неверный формат block_id', 90));
            self::setVar('blId', $blockId);
        } // if

        // Если action ID не задан, то отображем все элементы
        if (!$acId) {
            // Дерево созданных страниц
            $treeWF = dhtmlxTree::createTreeOfTable(new wareframeTree());
            self::setJson('wfTree', $treeWF);
            // В дереве блоках ни чего не показываем
            self::setJson('blockTree', '');
        } else {
			$data = model::makeTree($wfId, $acId);
			self::setJson('blockTree', $data['tree']);
            $routeTreeOrm = new routeTreeOrm();
            $actTree = actionModel::getActTree($routeTreeOrm);
            self::setJSON('actTree', $actTree);
            self::setJson('wfTree', '');

            $url = $routeTreeOrm->getActionUrlById($acId);
            $url = array_map(function($pItem) {
                return $pItem['name'];
            }, $url);
            $url = array_reverse($url);
            $url = '/' . implode('/', $url) . '/';
            self::setVar('pageCaption', $url);
        } // if

        // Дерево с файловой системой шаблонов сайта
        $siteTplPath = dirFunc::getSiteTplPath();
        $treeFS = dhtmlxTree::createTreeOfDir($siteTplPath);
        self::setJson('filesysTree', $treeFS);

        $compTree = dhtmlxTree::createTreeOfTable(new componentTree());
        self::setJson('compTree', $compTree);

        $this->view->setBlock('panel', 'block/wareframe.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Ajax.Json.Добавляем папку в дерево wareframe
     */
    public function dirAddAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost())
            return;

        $treeId = self::postInt('treeid');
        $name = self::post('name');
        $objJson = dhtmlxTree::add(new wareframeTree(), $name, $treeId, 0);
        $objJson['treeName'] = self::post('treeName');
        self::setVar('json', $objJson);
        // func. dirAddAction
    }

    public function fileAddAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost())
            return;

        $treeId = self::postInt('treeid');
        $name = self::post('name');
        $objJson = dhtmlxTree::add(new wareframeTree(), $name, $treeId, 1);
        $objJson['treeName'] = self::post('treeName');
        self::setVar('json', $objJson);
        // func. fileAddAction
    }

    public function renameObjAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost())
            return;
        $id = self::postInt('id', -1);
        $name = self::post('name');
        $objJson = dhtmlxTree::rename(new wareframeTree(), $name, $id);
        $objJson['treeName'] = self::post('treeName');
        self::setVar('json', $objJson);
        // func. renameObjAction
    }

    /**
     * Ajax.Json.Удалиние ветки в дереве страниц
     * @return void
     */
    public function rmObjAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost())
            return;
        $id = self::postInt('id', -1);
        dhtmlxTree::remove(new wareframeTree(), $id);
        self::setVar('json', ['id' => $id,
                             'treeName' => self::post('treeName') ]);
        // func. rmObjAction
    }

    /**
     * Ajax.Json.Загрузка дерева блоков
     * @return void
     */
    public function loadBlockTreeAction() {
        $this->view->setRenderType(render::JSON);
        // Получаем action Id
        $acId = self::getInt('acid');
        // Получаем wareframe Id
        $wfId = self::getInt('wfid', null);
        if ( !$wfId && $acId ){
            $wfId = (int)(new urlTreePropVar())->getWFId($acId);
        } // if
        // Проверяем существует ли такой wareframe Id
        (new wareframeTree())->isExists($wfId, new \Exception('WF not found', 33));
        // Получаем дерево в формате JSON
        $json = model::makeTree($wfId, $acId);
        $json['wfid'] = $wfId;
        // В какое box грузить дерево
        $json['treeBox'] = self::get('treeBox');
        self::setVar('json', $json);
        // func. loadBlockTreeAction
    }

    /**
     * Ajax.Json.Сохранение блоков(добавленных шаблонов в дерево WF)<br/>
     * Функция типа: JSON <br/>
     * Входящие параметры: <br/>
     *
     * @return type
     */
    public function saveBlockAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()) {
            return;
        }

        // Все файлы в формате JSON
        $file = self::post('file');
        $rmList = self::post('del');
        $linkList = self::post('link');

        $acId = self::postInt('acid');
        $wfId = self::postInt('wfid');
        // Проверка на существование wareframe Id
        $wareframeTree = new wareframeTree();
        $wareframeTree->isExists($wfId, new \Exception('wf not found', 33));

        $eventData = ['acId' => $acId, 'wfId' => $wfId];
        eventsys::callOffline(eventBlockItem::BLOCKITEM, eventBlockItem::CHANGE, $eventData);

        $routeTreeOrmWhere = $acId ? 'id='.$acId : 'id != 0';
        (new routeTreeOrm())->update('isSave="yes"', $routeTreeOrmWhere);

        model::saveBlockLink($acId, $wfId, $linkList);

        $json = ['ok' => 'ok'];
        $json['new'] = model::saveBlock($wfId, $acId, $file, $rmList);

        self::setVar('json', $json);
        // func. saveBlockAction
    }

    /**
     * Ajax.Json.Парсинг файла темлейта и получение из него блоков.<br/>
     * Используется на событии onDbClick на fsTree
     * Возвращает в формате:<br/>
     * {list:['blockname1', 'blockname2'], file:'filename', id:id}
     */
    public function tplToBlockAction() {
        $this->view->setRenderType(render::JSON);
        $file = self::get('file', '');

        $json = ['file' => $file];
        $json['list'] = model::tplBlockParser($file, new \Exception('Файл не достпен для чтения', 70));
        $json['id'] = self::get('id');
        $json['fileId'] = self::get('fileId');
        self::setVar('json', $json);
        // func. tplToBlockAction
    }

    /**
     * Ajax.Json.Сохранение данных по таблице с компонентами в Wareframe<br/>
     * Функция типа: JSON <br/>
     * Входящие параметры: <br/>
     * <b>data</b> json - Формат данных: [{id:val, data:{compId:val, name:val, sysname:val}], {...}]
     * acid
     * blid
     * wfid
     */
    public function saveBlockItemAction() {
        $this->view->setRenderType(render::JSON);
        // Данные для сохранения. JSON
        // Формат данных: [{id:val, data:{compId:val, name:val, sysname:val}], {...}]
        $data = self::post('data');
        // action id. см. таблицу url_tree
        // если значение пришло null, мы находим в общей WF
        $acId = self::getInt('acid');
        $blId = self::get('blid');
        $wfId = self::getInt('wfid');

        $eventData = ['acId' => $acId,
                      'blId' => $blId,
                      'wfId' => $wfId];
        // Выставление события, о том что блок изменился
        eventsys::callOffline(eventBlockItem::BLOCKITEM, eventBlockItem::CHANGE, $eventData);

        $listId = model::saveBlockItem($data, $acId, $blId, $wfId);

        // Если было изменён порядок следования компонентов
        $position = self::post('position');
        model::changeBlockItemPosition($position, $listId, $acId, $blId);
		
		$where = $acId ? ' AND id='.$acId : '';
		(new routeTreeOrm())->update('isSave="yes"', 'id != 0'.$where);

        $json = ['blid' => $blId, 'listid' => $listId];
        self::setVar('json', $json);
        // func. saveBlockItemAction
    }

    /**
     * Ajax.XML.Загрузка данных в таблицу на странице
     */
    public function loadBlockItemAction() {
        $this->view->setRenderType(render::NONE);
        header('Content-Type: text/xml; charset=UTF-8');

        $acId = self::getInt('acid');
        $blId = self::get('blid');
        $wfId = self::getInt('wfid');

        $blockItemList = model::getBlockItemList($acId, $blId, $wfId);

        $data = ['body' => $blockItemList];
        $listXML = dhtmlxGrid::createXMLOfArray($data, null, ['acId']);

        echo $listXML;
        // func. loadBlockItemAction
    }

    /**
     * Ajax.Json.Удаление компонента из таблицы.
     */
    public function rmBlockItemAction() {
        $this->view->setRenderType(render::JSON);
        $listId = self::post('idlist');
        eventsys::callOffline(eventBlockItem::BLOCKITEM, eventBlockItem::DELETE, $listId);

        $wfId = self::postInt('wfid');
        $blockId = self::post('blid');
        $acId = self::postInt('acid');

        $blockItem = new blockItem();

        $list = dhtmlxGrid::rmRows($listId, $blockItem);

        $itemCount = $blockItem->selectFirst(
            'count(1) as c',
            ['wf_id' => $wfId, 'block_id' => $blockId, 'acId' => $acId]
        )['c'];

        if ( $itemCount == 0 ){
            (new blockLinkOrm())->delete(['linkMainId' => $wfId, 'linkBlockId' => $blockId, 'acId' => $acId]);
        }

        $where = $acId ? ' AND id=' . $acId : '';
        (new routeTreeOrm())->update('isSave="yes"', 'id != 0' . $where);

        self::setVar('json', ['blid' => $blockId, 'list' => $list]);
        // rmBlockItemAction 
    }

    // class wareframe
}