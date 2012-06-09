<?php

namespace admin\library\mvc\comp\spl\oiList;

// Conf
use \DIR;
// Model
use admin\library\mvc\manager\complist\model as complistModel;
// Engine
use core\classes\render;
use core\classes\event as eventCore;
// ORM
use ORM\comp\spl\oiList\oiList as oiListOrm;
use ORM\comp\spl\oiList\oiListProp as oiListPropOrm;
use ORM\tree\compcontTree;
use ORM\tree\componentTree;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

//use ORM\comp\spl\oiList\oiListCont as oiListContOrm;

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
        $compcontTree = new compcontTree();
        $contData = $compcontTree->select('cc.*', 'cc')
            ->join(componentTree::TABLE.' c', 'c.id=cc.comp_id')
            ->where('c.sysname="objItem"')
            ->fetchAll();

        $contTree = dhtmlxTree::all($contData, 0);
        self::setJson('contTree', $contTree);

        $artlist = (new oiListOrm)->selectList('*', 'selContId', 'contId='.$contId);
        self::setJson('artlist', $artlist);

        self::setVar('contId', $this->contId);

        $oiListProp = ( new oiListPropOrm() )->selectFirst('');
        if ( $oiListProp){
            self::setVar('itemsCount', $oiListProp['itemsCount'] );
            self::setVar('memcacheCount', $oiListProp['memcacheCount']);
            self::setVar('fileCount', $oiListProp['fileCount']);
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

        $oiListOrm = new oiListOrm();
        $oiListOrm->delete('contId='.$contId);

        $selData = self::post('sel');
        $selData = substr($selData, 0, strlen($selData)-1);
        if ( $selData ){
            $selData = explode(',', $selData);
            $selData = array_map('intVal', $selData);

            $oiListOrm->insertMulti(['selContId' => $selData]);
            $oiListOrm->update('contId='.$contId, 'contId=0');
        } // if selData

        $saveData = [
            'itemsCount' => self::postInt('itemsCount'),
            'memcacheCount' => self::postInt('memcacheCount'),
            'fileCount' => self::postInt('fileCount')
        ];
        ( new oiListPropOrm() )->saveExt(['contId' => $contId], $saveData);

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

// class oiList
}