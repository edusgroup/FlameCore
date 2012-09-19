<?php

namespace admin\library\mvc\utils\tree;

// ORM
use ORM\tree\utilsTree;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// Conf
use \DIR;

// Engine
use core\classes\render;
use core\classes\mvc\controllerAbstract;
use core\classes\admin\dirFunc;

/**
 * Логика и настройка создания дерева каталогов для сайта
 *
 * @author Козленко В.Л.
 */
class tree extends controllerAbstract {

    public function init() {

    }


    public function indexAction() {

        $utilsTree = dhtmlxTree::createTreeOfTable(new utilsTree());
        self::setJSON('utilsTree', $utilsTree);

        $this->view->setBlock('panel', 'tree/tree.tpl.php');
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    // class action
}