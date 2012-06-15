<?php

namespace admin\library\mvc\comp\spl\catalogCont;

// Conf
use \DIR;
// Engine
use core\classes\render;
use core\classes\mvc\controllerAbstract;
use core\classes\event as eventCore;
// ORM
use ORM\tree\compContTree;
use ORM\tree\wareframeTree;
use ORM\comp\spl\catalogCont\catalogCont as catalogContOrm;
use ORM\comp\spl\catalogCont\catalogContProp as catalogContPropOrm;
use ORM\tree\componentTree;
use ORM\tree\routeTree as routeTreeOrm;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * @author Козленко В.Л.
 */
class catalogCont extends \core\classes\component\abstr\admin\comp {

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

        dhtmlxTree::setField(['propType']);
        $tree = dhtmlxTree::createTreeOfTable(new routeTreeOrm(), 'propType in (0,1)');
        dhtmlxTree::clear();
        self::setJson('routeTree', $tree);
        
        $catalogContOrm = new catalogContOrm();
        $catalogList = $catalogContOrm->selectList('*', 'selContId', 'contId='.$contId);
        self::setJson('catalog', $catalogList);

        self::setVar('contId', $this->contId);

        $catalogContProp = (new catalogContPropOrm())->selectFirst('urltpl', 'contId='.$contId);
        self::setJson('tplUrl', $catalogContProp['urltpl']);

        $tplFile = self::getTplFile();
        //print $tplFile;
        $this->view->setBlock('panel', $tplFile);
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Сохраняет данные 
     * @return void
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

        $catalogContOrm = new catalogContOrm();
        $catalogContOrm->delete('contId='.$contId);
        
        $selData = self::post('sel');
        $selData = substr($selData, 0, strlen($selData)-1);
        if ( $selData ){
            $selData = explode(',', $selData);
            $selData = array_map('intVal', $selData);

            $catalogContOrm->insertMulti(['selContId' => $selData]);
            $catalogContOrm->update('contId='.$contId, 'contId=0');

        } // if selData

        $urltpl = self::post('urltpl');
        (new catalogContPropOrm())->saveExt(['contId'=>$contId], ['urltpl'=>$urltpl]);

        // func. saveDataAction 
    }

    public function getTableData($pContId) {

    }

    public function getTableOrm() {

    }

// class action
}

?>