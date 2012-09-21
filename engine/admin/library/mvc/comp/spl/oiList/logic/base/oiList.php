<?php

namespace admin\library\mvc\comp\spl\oiList\logic\base;

// Conf
use \DIR;

// Engine
use core\classes\render;
use core\classes\event as eventCore;
use core\classes\filesystem;
use core\classes\comp;
use core\classes\validation\filesystem as filevalid;
use core\classes\admin\dirFunc;

// ORM
use ORM\comp\spl\oiList\oiList as oiListOrm;
use ORM\comp\spl\oiList\oiListProp as oiListPropOrm;
use ORM\tree\compcontTree;
use ORM\tree\componentTree;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// Event
use admin\library\mvc\comp\spl\oiList\event;

// Model
use admin\library\mvc\comp\spl\oiList\model;

/**
 * Description of oiList
 *
 * @author Козленко В.Л.
 */
class oiList extends \core\classes\component\abstr\admin\comp {

    public function init() {

    }

    public function indexAction() {
        $contId = $this->contId;
        self::setVar('contId', $contId);

        // Получаем данные по компоненту objItem
        $objItemProp = (new componentTree())->selectFirst('*', 'sysname="objItem"');

        // Получаем список разновидностей objItem
        $nsPath = filesystem::nsToPath($objItemProp['ns']);

        // Получаем весь список контента по oiList
        $contData = (new compcontTree())->select('cc.*', 'cc')
            ->where('cc.isDel="no" AND cc.comp_id=' . $objItemProp['id'])
            ->fetchAll();
        // Преобразуем список в дерево Контента
        $contTree = dhtmlxTree::all($contData, 0);
        self::setJson('contTree', $contTree);

        // Дерево классов для builder
        $classTree = model::getBuildClassTree($nsPath);
        self::setJson('classTree', $classTree);

        // Получаем список id веток ранее выбранных и сохранённых
        $selItem = (new oiListOrm)->selectList('*', 'selContId', 'contId=' . $contId);
        self::setJson('selItem', $selItem);

        // Получаем ранее сохранённые настройки по текущему contId в oiList
        $oiListPropLoad = (new oiListPropOrm())->selectFirst('*', 'contId='.$contId);
        // Передаём все сохранённые переменные из настроек в шаблоны
        if ( $oiListPropLoad){
            foreach( $oiListPropLoad as $key => $val ){
                self::setVar($key, $val );
            } // foreach
        } // if

        // Получаем имя шаблона. Можно изменить через свойство компонента
        $this->view->setBlock('panel', $this->tplFile);
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
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
        if (!self::isPost()){
            return;
        } // if
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
            $oiListOrm->insertMulti(['selContId' => $selData], ['contId' => $contId]);
        } // if selData

        $classFile = self::post('class');
        if ( !$classFile ){
            return;
        } // if
        // Получаем данные по компоненту objItem
        $objItemProp = (new componentTree())->selectFirst('*', 'sysname="objItem"');
        // Получаем список разновидностей objItem
        $nsPath = filesystem::nsToPath($objItemProp['ns']);
        model::isClassFileExit($classFile, $nsPath);

        // Сохраняем настроки по oiList
        $saveData = [
            'itemsCount' => self::postInt('itemsCount'),
            'memcacheCount' => self::postInt('memcacheCount'),
            'fileCount' => self::postInt('fileCount'),
            'classFile' => $classFile,
            'isCreateCategory' => self::postInt('isCreateCategory', 0)
        ];
        (new oiListPropOrm())->saveExt(['contId' => $contId], $saveData);
        // func. saveDataAction
    }

    // class oiList
}