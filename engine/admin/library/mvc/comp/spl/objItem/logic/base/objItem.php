<?php
namespace admin\library\mvc\comp\spl\objItem\logic\base;

use \DIR;

/**
 * Description of article
 *
 * @author Козленко В.Л.
 */
class objItem extends \core\classes\component\abstr\admin\comp{
    public function indexAction(){

        self::setVar('contId', $this->contId);

        $this->view->setBlock('panel', $this->tplFile);
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public static function compPropAction(){

    }

    public function init(){

    }
    // class tsetad
}