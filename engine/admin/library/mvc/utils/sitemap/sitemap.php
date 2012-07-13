<?php

namespace admin\library\mvc\utils\sitemap;

// Conf
use \DIR;
// Engine
use core\classes\render;
use core\classes\mvc\controllerAbstract;
use core\classes\filesystem;
use core\classes\event as eventCore;
// ORM
use ORM\tree\compContTree;
use ORM\tree\routeTree;
use ORM\tree\wareframeTree;
use ORM\sitemaps as sitemapsOrm;
use ORM\tree\componentTree;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * @author Козленко В.Л.
 */
class sitemap extends controllerAbstract {

    public function init() {
        
    }

    public function indexAction() {
        
        $compcontTree = new compcontTree();
        $contData = $compcontTree->select('cc.*', 'cc')
                     ->join(componentTree::TABLE.' c', 'c.id=cc.comp_id')
                     ->where('c.sysname="objItem" AND cc.isDel="no"')
                     ->fetchAll();
        
        $contTree = dhtmlxTree::all($contData, 0);
        self::setJson('contTree', $contTree);
        
        $sitemapsOrm = new sitemapsOrm();
        self::setJson('sitemaps', $sitemapsOrm->selectList('*', 'contId'));
        
        $this->view->setBlock('panel', 'sitemaps/sitemaps.tpl.php');
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

        eventCore::callOffline(event::NAME, event::ACTION_SAVE, '');

        $sitemapsOrm = new sitemapsOrm();
        $sitemapsOrm->delete();

        $selData = self::post('sel');
        $selData = substr($selData, 0, strlen($selData)-1);
        if ( $selData ){
            $selData = explode(',', $selData);
            $selData = array_map('intVal', $selData);

            $sitemapsOrm->insertMulti(['contId' => $selData]);

        } // if selData
        // func. saveDataAction
    }

// class action
}