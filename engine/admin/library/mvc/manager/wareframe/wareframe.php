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
use core\classes\DB\adapter\DBException;
use core\classes\mvc\controllerAbstract;
use core\classes\event as eventsys;

// ORM
use ORM\blockItem;
use ORM\tree\componentTree;
use ORM\tree\wareframeTree;
use ORM\tree\routeTree;
use ORM\urlTreePropVar;
use ORM\tree\compContTree;

// Event
use admin\library\mvc\manager\blockItem\event as eventBlockItem;

/**
 * Логика настроек сайта
 *
 * @author Козленко В.Л.
 */
class wareframe extends controllerAbstract {

    public function init() {

    }

    public function indexAction() {

        // Получаем ID action
        $acId = self::getInt('acid', '');
        self::setVar('acId', $acId);

        $urlTreePropVar = new urlTreePropVar();
        $wfId = $urlTreePropVar->getWFId($acId ? : -1);
        //if ( !$wfId )
        //    throw new \Exception('WF не найден', 25);
        self::setVar('wfId', $wfId);

        $blockId = self::get('blid');
        if ($blockId) {
            blockItem::validBlockId($blockId, new \Exception('Неверный формат block_id', 90));
            self::setVar('blId', $blockId);
        }

        // Если action ID не задан, то отображем все элементы
        if (!$acId) {
            // Дерево созданных страниц
            $treeWF = dhtmlxTree::createTreeOfTable(new wareframeTree());
            self::setJson('wfTree', $treeWF);
            // В дереве блоках ни чего не показываем
            self::setJson('blockTree', '');
        } else {
			//try{
				$data = model::makeTree($wfId, $acId);
				
			//}catch(exception\wareframe $ex){
				//self::setVar('errMsg', $ex->getMessage());
			//	$data['tree'] = [];
			//}
			self::setJson('blockTree', $data['tree']);
            //self::setVar('rootTreeId', $data['root']);
            self::setJson('wfTree', '');
        }

        // Дерево с файловой системой шаблонов сайта
        $siteTplPath = DIR::getSiteTplPath();
        $treeFS = dhtmlxTree::createTreeOfDir($siteTplPath);
        self::setJson('filesysTree', $treeFS);

        $compTree = dhtmlxTree::createTreeOfTable(new componentTree());
        self::setJson('compTree', $compTree);

        $this->view->setBlock('panel', 'block/wareframe.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Добавляем папку. AJAX
     */
    public function dirAddAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost())
            return;

        $treeId = self::postInt('treeid', 0);
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

        $treeId = self::postInt('treeid', 0);
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
     * Удалиние ветки в дереве страниц
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
     * Загрузка дерева блоков
     * @return void
     */
    public function loadBlockTreeAction() {
        $this->view->setRenderType(render::JSON);
        $wfId = self::getInt('wfid');
        $acId = self::getInt('acid', null);
        $wareframeTree = new wareframeTree();
        $wareframeTree->isExists($wfId, new \Exception('WF not found', 33));

        $json = model::makeTree($wfId, $acId);
        $json['wfid'] = $wfId;
        self::setVar('json', $json);
        // func. loadBlockTreeAction
    }

    /**
     * Сохранение блоков(добавленных шаблонов в дерево WF)<br/>
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

        $acId = self::postInt('acid', null);
        $wfId = self::postInt('wfid');
        $wareframeTree = new wareframeTree();
        $wareframeTree->isExists($wfId, new \Exception('wf not found', 33));

        $eventData = ['acId' => $acId, 'wfId' => $wfId];
        eventsys::callOffline(eventBlockItem::BLOCKITEM, eventBlockItem::CHANGE, $eventData);

        $routeTreeWhere = $acId ? 'id='.$acId : 'id != 0';
        (new routeTree())->update('isSave="yes"', $routeTreeWhere);

        $json = ['ok' => 'ok'];
        $json['new'] = model::saveBlock($wfId, $acId, $file, $rmList);

        self::setVar('json', $json);
        // func. saveBlockAction
    }

    /**
     * Парсинг файла темлейта и получение из него блоков.<br/>
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
     * Сохранение данных по таблице с компонентами в Wareframe<br/>
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
        $acId = self::getInt('acid', null);
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
        model::changeBlockItemPosition($position, $listId);
		
		$where = $pAcId ? ' AND id='.$pAcId : '';
		(new routeTree())->update('isSave="yes"', 'id != 0'.$where);

        $json = ['blid' => $blId, 'listid' => $listId];
        self::setVar('json', $json);
        // func. saveBlockItemAction
    }

    public function loadBlockItemAction() {
        $this->view->setRenderType(render::NONE);
        header('Content-Type: text/xml; charset=UTF-8');

        $acId = self::getInt('acid', null);
        $blId = self::get('blid');
        $wfId = self::getInt('wfid');

        $blockItemList = model::getBlockItemList($acId, $blId, $wfId);

        $data = ['body' => $blockItemList];
        $listXML = dhtmlxGrid::createXMLOfArray($data, null, ['acId']);

        echo $listXML;
        // func. loadBlockItemAction
    }

    public function rmBlockItemAction() {
        $this->view->setRenderType(render::JSON);
        $listId = self::post('idlist');
        eventsys::callOffline(eventBlockItem::BLOCKITEM, eventBlockItem::DELETE, $listId);
        $list = dhtmlxGrid::rmRows($listId, new blockItem());

        $blockId = self::post('blid');

        $acId = self::postInt('acid');
        $where = $acId ? ' AND id=' . $acId : '';
        (new routeTree())->update('isSave="yes"', 'id != 0' . $where);

        self::setVar('json', ['blid' => $blockId, 'list' => $list]);
        // rmBlockItemAction 
    }
    // class wareframe
}