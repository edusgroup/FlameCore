<?php

namespace admin\library\mvc\utils\users;

// Conf
use \DIR;

// Engine
use core\classes\render;
use core\classes\mvc\controllerAbstract;
use core\classes\admin\dirFunc;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\grid as dhtmlxGrid;


/**
 * @author Козленко В.Л.
 */
class users extends controllerAbstract {

    CONST USER_COUNT = 5;

    public function init() {

    }

    public function indexAction() {
        $this->view->setBlock('panel', 'users/users.tpl.php');
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function loadPageAction() {
        $this->view->setRenderType(render::NONE);
        header('Content-Type: text/xml; charset=UTF-8');

        $posStart = self::getInt('posStart', 1);
        echo model::getUserXmlGrid($posStart);
        // loadPageAction
    }

    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);
        $data = self::post('data');

        //eventsys::callOffline(event::BLOCKITEM, 'blockitem:change', $eventData);

        $listId = model::saveData($data);
        self::setVar('json', array(
                                  'list' => $listId
                             ));
        // func. loadPageAction
    }

    // class users
}