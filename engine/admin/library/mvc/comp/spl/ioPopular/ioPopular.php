<?php

namespace admin\library\mvc\comp\spl\objItem;

// Conf
use \DIR;
// Model
use admin\library\mvc\manager\complist\model as complistModel;
// Engine
use core\classes\render;
use core\classes\event as eventCore;
// ORM
use ORM\comp\spl\objItem\ioPopular as ioPopularOrm;
use ORM\comp\spl\objItem\ioPopularProp as ioPopularPropOrm;
use ORM\tree\compcontTree;
use ORM\tree\componentTree;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

//use ORM\comp\spl\ioPopular\ioPopularCont as ioPopularContOrm;

/**
 * Description of ioPopular
 *
 * @author Козленко В.Л.
 */
class ioPopular extends \core\classes\component\abstr\admin\comp {

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

        $ioPopular = (new ioPopularOrm)->selectList('*', 'selContId', 'contId='.$contId);
        self::setJson('ioPopular', $ioPopular);

        self::setVar('contId', $this->contId);

        $ioPopularProp = ( new ioPopularPropOrm() )->selectFirst('');
        if ( $ioPopularProp){
            self::setVar('itemsCount', $ioPopularProp['itemsCount'] );
            self::setVar('imgWidth', $ioPopularProp['imgWidth']);
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

        $ioPopularOrm = new ioPopularOrm();
        $ioPopularOrm->delete('contId='.$contId);

        $selData = self::post('sel');
        $selData = substr($selData, 0, strlen($selData)-1);
        if ( $selData ){
            $selData = explode(',', $selData);
            $selData = array_map('intVal', $selData);

            $ioPopularOrm->insertMulti(['selContId' => $selData]);
            $ioPopularOrm->update('contId='.$contId, 'contId=0');
        } // if selData

        $saveData = [
            'itemsCount' => self::postInt('itemsCount'),
            'imgWidth' => self::postInt('imgWidth')
        ];
        (new ioPopularPropOrm())->saveExt(['contId' => $contId], $saveData);

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

// class ioPopular
}

?>