<?php
namespace admin\library\mvc\comp\spl\objItem\logic\base;

// Conf
use \DIR;

// Engine
use core\classes\admin\dirFunc;

/**
 * Description of article
 *
 * @author Козленко В.Л.
 */
class objItem extends \core\classes\component\abstr\admin\comp{

    public function init(){

    }

    public function indexAction(){

        self::setVar('contId', $this->contId);

        $this->view->setBlock('panel', $this->tplFile);
        $this->view->setTplPath(dirFunc::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public static function compPropAction(){

    }

    public function blockItemShowAction() {
        $this->view->setRenderType(render::NONE);
        echo 'article::blockItemShowAction() | No settings in this';
        // func. blockItemShowAction
    }

    // class tsetad
}