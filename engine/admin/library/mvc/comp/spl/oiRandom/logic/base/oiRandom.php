<?php

namespace admin\library\mvc\comp\spl\oiRandom\logic\base;

// Conf
use \DIR;

// Engine
use core\classes\render;
use core\classes\event as eventCore;
use core\classes\filesystem;

// ORM
use ORM\comp\spl\oiRandom\oiRandom as oiRandomOrm;
use ORM\comp\spl\oiRandom\oiRandomProp as oiRandomPropOrm;
use ORM\tree\compcontTree;
use ORM\tree\componentTree;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// Event
use admin\library\mvc\comp\spl\oiRandom\event;


/**
 * Description of oiPopular
 *
 * @author Козленко В.Л.
 */
class oiRandom extends \core\classes\component\abstr\admin\comp {

    public function init() {
        
    }

    public function indexAction(){
        $contId = $this->contId;
        self::setVar('contId', $contId);

        // Получаем данные по компоненту objItem
        $objItemProp = (new componentTree())->selectFirst('*', 'sysname="objItem"');

        // Получаем весь список контента по oiRandom
        $contData = (new compcontTree())->select('cc.*', 'cc')
            ->where('cc.isDel="no" AND cc.comp_id=' . $objItemProp['id'])
            ->fetchAll();
        // Преобразуем список в дерево
        $contTree = dhtmlxTree::all($contData, 0);
        self::setJson('contTree', $contTree);

        // Получаем список id веток ранее выбранных и сохранённых
        $oiRandom = (new oiRandomOrm)->selectList('*', 'selContId', 'contId='.$contId);
        self::setJson('oiRandom', $oiRandom);

        // Получаем количество элементов для списка, которые было ранее сохранено
        $oiPopularProp = ( new oiRandomPropOrm() )->selectFirst('*', 'contId='.$contId);
        // Передаём все сохранённые переменные из настроек в шаблоны
        if ( $oiPopularProp){
            foreach( $oiPopularProp as $key => $val ){
                self::setVar($key, $val );
            }
        } // if

        // Получаем список разновидностей objItem
        $nsPath = filesystem::nsToPath($objItemProp['ns']);
        $categoryDir = DIR::CORE . 'admin/library/mvc/comp/' . $nsPath . 'category/';
        if (is_dir($categoryDir)) {
            $categoryList = [];
            $categoryList['list'] = filesystem::dir2array($categoryDir, filesystem::DIR);
            $categoryList['val'] = $oiPopularProp['category'];
            self::setVar('categoryList', $categoryList);
        } // if is_dir

        $this->view->setBlock('panel', $this->tplFile);
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }
    
    public function saveDataAction(){
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()){
            return;
        }

        $contId = $this->contId;

        eventCore::callOffline(
            event::NAME,
            event::ACTION_SAVE,
            '',
            $contId
        );

        $oiRandomOrm = new oiRandomOrm();
        $oiRandomOrm->delete('contId='.$contId);

        $selData = self::post('sel');
        $selData = substr($selData, 0, strlen($selData)-1);
        if ( $selData ){
            $selData = explode(',', $selData);
            $selData = array_map('intVal', $selData);

            $oiRandomOrm->insertMulti(['selContId' => $selData]);
            $oiRandomOrm->update('contId='.$contId, 'contId=0');
        } // if selData

        // Сохраняем настроки по oiRandom
        $saveData = [
            'itemsCount' => self::postInt('itemsCount'),
            'resizeType' => self::post('resizeType'),
            'previewWidth' => self::postInt('previewWidth'),
            'isAddMiniText' => self::postInt('isAddMiniText'),
            'isCreatePreview' => self::postInt('isCreatePreview'),
            'category' => self::post('category')
        ];
        (new oiRandomPropOrm())->saveExt(['contId' => $contId], $saveData);

        // func. saveDataAction
    }

// class oiPopular
}