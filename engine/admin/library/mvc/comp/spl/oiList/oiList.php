<?php

namespace admin\library\mvc\comp\spl\oiList;

// Conf
use \DIR;

// Engine
use core\classes\render;
use core\classes\event as eventCore;
use core\classes\filesystem;

// ORM
use ORM\comp\spl\oiList\oiList as oiListOrm;
use ORM\comp\spl\oiList\oiListProp as oiListPropOrm;
use ORM\tree\compcontTree;
use ORM\tree\componentTree;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * Description of oiList
 *
 * @author Козленко В.Л.
 */
class oiList extends \core\classes\component\abstr\admin\comp {

    public function __construct(string $pTplPath, string $pThemeResUrl) {
        parent::__construct($pTplPath, $pThemeResUrl);
    }

    public function init() {

    }

    public function indexAction() {
        $contId = $this->contId;
        self::setVar('contId', $contId);

        // Получаем данные по компоненту objItem
        $objItemProp = (new componentTree())->selectFirst('*', 'sysname="objItem"');
        // Получаем весь список контента по oiList
        $contData = (new compcontTree())->select('cc.*', 'cc')
            ->where('cc.isDel="no" AND cc.comp_id=' . $objItemProp['id'])
            ->fetchAll();
        // Преобразуем список в дерево
        $contTree = dhtmlxTree::all($contData, 0);
        self::setJson('contTree', $contTree);

        // Получаем список id веток ранее выбранных и сохранённых
        $oiList = (new oiListOrm)->selectList('*', 'selContId', 'contId=' . $contId);
        self::setJson('oiList', $oiList);

        // Получаем ранее сохранённые настройки по текущему contId в oiList
        $oiListProp = (new oiListPropOrm())->selectFirst('');
        if ($oiListProp) {
            self::setVar('itemsCount', $oiListProp['itemsCount']);
            self::setVar('memcacheCount', $oiListProp['memcacheCount']);
            self::setVar('fileCount', $oiListProp['fileCount']);
        } // if

        // Получаем список разновидностей objItem
        $categoryDir = DIR::CORE . 'admin/library/mvc/comp/' . $objItemProp['ns'] . 'category/';
        if (is_dir($categoryDir)) {
            $categoryList = [];
            $categoryList['list'] = filesystem::dir2array($categoryDir, filesystem::DIR);
            $categoryList['val'] = $oiListProp['category'];
            self::setVar('categoryList', $categoryList);
        } // if is_dir

        // Получаем имя шаблона. Можно изменить через свойство компонента
        $tplFile = self::getTplFile();
        $this->view->setBlock('panel', $tplFile);
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Сохранение выбранных веток и настроек по ioList<br/>
     * POST:<br/>
     * string sel - список выбранных id в дереве. Формат: id1,id2,id3<br/>
     * int itemsCount - Количество элементов objItem в файле<br/>
     * int memcacheCount - не используется<br/>
     * int fileCount - не используется<br/>
     * string category - подтип objItem
     * @return mixed
     */
    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost())
            return;
        $contId = $this->contId;

        eventCore::callOffline(
            event::NAME,
            event::ACTION_SAVE,
            '',
            $contId
        );

        // Удаляем все старые ветки
        $oiListOrm = new oiListOrm();
        $oiListOrm->delete('contId=' . $contId);

        // Получаем список выбранных id. ID веток идут через запятую
        // Формат: id1,id2,id3
        $selData = self::post('sel');
        $selData = trim($selData, ',');
        if ($selData) {
            $selData = explode(',', $selData);
            $selData = array_map('intVal', $selData);
            // Добавляем новые id в таблицу
            $oiListOrm->insertMulti(['selContId' => $selData]);
            $oiListOrm->update('contId=' . $contId, 'contId=0');
        } // if selData

        // Сохраняем настроки по oiList
        $saveData = [
            'itemsCount' => self::postInt('itemsCount'),
            'memcacheCount' => self::postInt('memcacheCount'),
            'fileCount' => self::postInt('fileCount'),
            'category' => self::post('category')
        ];
        (new oiListPropOrm())->saveExt(['contId' => $contId], $saveData);

        // func. saveDataAction
    }

    public function getTableData($pContId) {

    }

    public function getTableOrm() {

    }

    public function blockItemShowAction() {
        $this->view->setRenderType(render::NONE);
        echo 'Нет данных';
    }

    // class oiList
}