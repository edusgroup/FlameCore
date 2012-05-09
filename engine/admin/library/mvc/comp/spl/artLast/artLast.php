<?php

namespace admin\library\mvc\comp\spl\artLast;

// Conf
use \DIR;
// Model
use admin\library\mvc\manager\complist\model as complistModel;
// Engine
use core\classes\render;
use core\classes\event as eventCore;
// ORM
use ORM\comp\spl\artLast\artLast as artLastOrm;
use ORM\comp\spl\artLast\artLastProp as artLastPropOrm;
use ORM\tree\compcontTree;
use ORM\tree\componentTree;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

//use ORM\comp\spl\artLast\artLastCont as artLastContOrm;

/**
 * Description of artLast
 *
 * @author Козленко В.Л.
 */
class artLast extends \core\classes\component\abstr\admin\comp {

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
            ->where('c.sysname="article"')
            ->fetchAll();

        $contTree = dhtmlxTree::all($contData, 0);
        self::setJson('contTree', $contTree);

        $artLast = (new artLastOrm)->selectList('*', 'selContId', 'contId='.$contId);
        self::setJson('artLast', $artLast);

        self::setVar('contId', $this->contId);

        $artLastProp = ( new artLastPropOrm() )->selectFirst('', 'contId='.$contId);
        if ( $artLastProp){
            self::setVar('itemsCount', $artLastProp['itemsCount'] );
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

        $artLastOrm = new artLastOrm();
        $artLastOrm->delete('contId='.$contId);

        $selData = self::post('sel');
        $selData = substr($selData, 0, strlen($selData)-1);
        if ( $selData ){
            $selData = explode(',', $selData);
            $selData = array_map('intVal', $selData);

            $artLastOrm->insertMulti(['selContId' => $selData]);
            $artLastOrm->update('contId='.$contId, 'contId=0');
        } // if selData

        $saveData = [
            'itemsCount' => self::postInt('itemsCount')
        ];
        ( new artLastPropOrm() )->saveExt(['contId' => $contId], $saveData);

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

    // class artLast
}
