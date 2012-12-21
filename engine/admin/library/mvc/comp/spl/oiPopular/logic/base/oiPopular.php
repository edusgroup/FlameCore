<?php

namespace admin\library\mvc\comp\spl\oiPopular\logic\base;

// Conf
use \DIR;

// Engine
use core\classes\render;
use core\classes\event as eventCore;
use core\classes\filesystem;
use core\classes\admin\dirFunc;

// ORM
use ORM\comp\spl\oiPopular\oiPopular as oiPopularOrm;
use ORM\comp\spl\oiPopular\oiPopularProp as oiPopularPropOrm;
use ORM\tree\compcontTree;
use ORM\tree\componentTree;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// Event
use admin\library\mvc\comp\spl\oiPopular\event;

// Model
use admin\library\mvc\comp\spl\oiList\model;


/**
 * Description of oiPopular
 *
 * @author Козленко В.Л.
 */
class oiPopular extends \core\classes\component\abstr\admin\comp {

    public function init() {
        
    }

    public function indexAction() {
        $contId = $this->contId;
        self::setVar('contId', $contId);

        // Получаем данные по компоненту objItem
        $objItemProp = (new componentTree())->selectFirst('*', 'sysname="objItem"');

        // Получаем весь список контента по oiPopular
        $contData = (new compcontTree())->select('cc.*', 'cc')
            ->where('cc.isDel="no" AND cc.comp_id=' . $objItemProp['id'])
            ->fetchAll();
        // Преобразуем список в дерево
        $contTree = dhtmlxTree::all($contData, 0);
        self::setJson('contTree', $contTree);

        // Получаем список id веток ранее выбранных и сохранённых
        $selItem = (new oiPopularOrm)->selectList('*', 'selContId', 'contId='.$contId);
        self::setJson('selItem', $selItem);

        // Получаем количество элементов для списка, которые было ранее сохранено
        $oiPopularProp = ( new oiPopularPropOrm() )->selectFirst('*', 'contId='.$contId);
        // Передаём все сохранённые переменные из настроек в шаблоны
        if ( $oiPopularProp){
            foreach( $oiPopularProp as $key => $val ){
                self::setVar($key, $val );
            }
        } // if

        // Получаем список разновидностей objItem
        $nsPath = filesystem::nsToPath($objItemProp['ns']);

        // Дерево классов для builder
        $classTree = model::getBuildClassTree($nsPath);
        self::setJson('classTree', $classTree);

        $this->view->setBlock('panel', $this->tplFile);
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }
    
    public function saveDataAction(){
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

        $oiPopularOrm = new oiPopularOrm();
        $oiPopularOrm->delete('contId='.$contId);

        $selData = self::post('sel');
        $selData = substr($selData, 0, strlen($selData)-1);
        if ( $selData ){
            $selData = explode(',', $selData);
            $selData = array_map('intVal', $selData);

            $oiPopularOrm->insertMulti(['selContId' => $selData], ['contId' => $contId]);
        } // if selData

        $classFile = self::post('classFile');
        if ( !$classFile ){
            return;
        } // if
        // Получаем данные по компоненту objItem
        $objItemProp = (new componentTree())->selectFirst('*', 'sysname="objItem"');
        // Получаем список разновидностей objItem
        $nsPath = filesystem::nsToPath($objItemProp['ns']);
        model::isClassFileExit($classFile, $nsPath);

        $saveData = [
            'itemsCount' => self::postInt('itemsCount'),
            'resizeType' => self::post('resizeType'),
            'previewWidth' => self::postInt('previewWidth'),
            'isAddMiniText' => self::postInt('isAddMiniText'),
            'isCreatePreview' => self::postInt('isCreatePreview'),
            'classFile' => $classFile
        ];
        (new oiPopularPropOrm())->saveExt(['contId' => $contId], $saveData);

        // func. saveDataAction
    }

// class oiPopular
}