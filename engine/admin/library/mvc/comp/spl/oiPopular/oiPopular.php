<?php

namespace admin\library\mvc\comp\spl\oiPopular;

// Conf
use \DIR;

// Engine
use core\classes\render;
use core\classes\event as eventCore;
use core\classes\filesystem;

// ORM
use ORM\comp\spl\oiPopular\oiPopular as oiPopularOrm;
use ORM\comp\spl\oiPopular\oiPopularProp as oiPopularPropOrm;
use ORM\tree\compcontTree;
use ORM\tree\componentTree;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;


/**
 * Description of oiPopular
 *
 * @author Козленко В.Л.
 */
class oiPopular extends \core\classes\component\abstr\admin\comp {

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

        // Получаем весь список контента по oiPopular
        $contData = (new compcontTree())->select('cc.*', 'cc')
            ->where('cc.isDel="no" AND cc.comp_id=' . $objItemProp['id'])
            ->fetchAll();
        // Преобразуем список в дерево
        $contTree = dhtmlxTree::all($contData, 0);
        self::setJson('contTree', $contTree);

        // Получаем список id веток ранее выбранных и сохранённых
        $oiPopular = (new oiPopularOrm)->selectList('*', 'selContId', 'contId='.$contId);
        self::setJson('oiPopular', $oiPopular);

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
        $categoryDir = DIR::CORE . 'admin/library/mvc/comp/' . $nsPath . 'category/';
        if (is_dir($categoryDir)) {
            $categoryList = [];
            $categoryList['list'] = filesystem::dir2array($categoryDir, filesystem::DIR);
            $categoryList['val'] = $oiPopularProp['category'];
            self::setVar('categoryList', $categoryList);
        } // if is_dir

        $tplFile = self::getTplFile();
        $this->view->setBlock('panel', $tplFile);
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }
    
    public function saveDataAction(){
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

        $oiPopularOrm = new oiPopularOrm();
        $oiPopularOrm->delete('contId='.$contId);

        $selData = self::post('sel');
        $selData = substr($selData, 0, strlen($selData)-1);
        if ( $selData ){
            $selData = explode(',', $selData);
            $selData = array_map('intVal', $selData);

            $oiPopularOrm->insertMulti(['selContId' => $selData]);
            $oiPopularOrm->update('contId='.$contId, 'contId=0');
        } // if selData

        $saveData = [
            'itemsCount' => self::postInt('itemsCount'),
            'resizeType' => self::post('resizeType'),
            'previewWidth' => self::postInt('previewWidth'),
            'isAddMiniText' => self::postInt('isAddMiniText'),
            'isCreatePreview' => self::postInt('isCreatePreview'),
            'category' => self::post('category')
        ];
        (new oiPopularPropOrm())->saveExt(['contId' => $contId], $saveData);

        // func. saveDataAction
    }

    public function getTableData($pContId) {
        
    }

    public function getTableOrm() {
        
    }
    
    public function blockItemShowAction(){
        $this->view->setRenderType(render::NONE);
        echo 'Нет данных';
    }

// class oiPopular
}