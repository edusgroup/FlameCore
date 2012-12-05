<?php

namespace admin\library\mvc\utils\ajax;

// Conf
use \DIR;

// Engine
use core\classes\render;
use core\classes\mvc\controllerAbstract;
use core\classes\filesystem;
use core\classes\admin\dirFunc;
use core\classes\comp as compCore;

// ORM
use ORM\tree\componentTree;
use ORM\tree\compContTree;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * @author Козленко В.Л.
 */
class ajax extends controllerAbstract {

    public function init() {

    }

    public function indexAction() {
        $compTree = dhtmlxTree::createTreeOfTable(new componentTree());
        self::setJson('compTreeJson', $compTree);

        $this->view->setBlock('panel', 'ajax/ajax.tpl.php');
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function loadClassTreeAction(){
        $this->view->setRenderType(render::JSON);

        $selCompId = self::getInt('compId');
        $compData = compCore::getClassDataByCompId($selCompId);
        $nsPath = filesystem::nsToPath($compData['ns']);
        $classTree = model::getAjaxTree($nsPath);

        self::setVar('json', ['classTreeJson' => $classTree]);
        // func. loadClassTreeAction
    }

    public function loadClassMethodAction(){
        // func. loadClassMethodAction 
    }

    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()){
            return;
        }


        // func. saveDataAction
    }

    // class ajax
}