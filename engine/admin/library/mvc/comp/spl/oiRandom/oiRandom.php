<?php

namespace admin\library\mvc\comp\spl\oiRandom;

// Conf
use \DIR;
// Model
use admin\library\mvc\manager\complist\model as complistModel;
// Engine
use core\classes\render;
use core\classes\event as eventCore;
// ORM
use ORM\comp\spl\oiRandom\oiRandom as oiRandomOrm;
use ORM\comp\spl\oiRandom\oiRandomProp as oiRandomPropOrm;
use ORM\tree\compcontTree;
use ORM\tree\componentTree;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

//use ORM\comp\spl\oiPopular\oiPopularCont as oiPopularContOrm;

/**
 * Description of oiPopular
 *
 * @author Козленко В.Л.
 */
class oiRandom extends \core\classes\component\abstr\admin\comp {

    public function __construct(string $pTplPath, string $pThemeResUrl) {
        parent::__construct($pTplPath, $pThemeResUrl);
    }

    public function init() {
        
    }

    public function indexAction() {
        $contId = $this->contId;
        $compcontTree = new compcontTree();
        $contData = $compcontTree->select('cc.*', 'cc')
            ->join(componentTree::TABLE.' c', 'c.id=cc.comp_id')
            ->where('c.sysname="objItem" AND cc.isDel="no"')
            ->fetchAll();

        $contTree = dhtmlxTree::all($contData, 0);
        self::setJson('contTree', $contTree);

        $oiRandom = (new oiRandomOrm)->selectList('*', 'selContId', 'contId='.$contId);
        self::setJson('oiRandom', $oiRandom);

        self::setVar('contId', $this->contId);

        // Получаем количество элементов для списка, которые было ранее сохранено
        $oiPopularProp = ( new oiRandomPropOrm() )->selectFirst('*', 'contId='.$contId);
        // Передаём все сохранённые переменные из настроек в шаблоны
        if ( $oiPopularProp){
            foreach( $oiPopularProp as $key => $val ){
                self::setVar($key, $val );
            }
        } // if

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

        $saveData = [
            'itemsCount' => self::postInt('itemsCount'),
            'resizeType' => self::post('resizeType'),
            'previewWidth' => self::postInt('previewWidth'),
            'isAddMiniText' => self::postInt('isAddMiniText'),
            'isCreatePreview' => self::postInt('isCreatePreview')
        ];
        (new oiRandomPropOrm())->saveExt(['contId' => $contId], $saveData);

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