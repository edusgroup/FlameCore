<?php

namespace admin\library\mvc\utils\rss;

// Conf
use \DIR;

// Engine
use core\classes\render;
use core\classes\mvc\controllerAbstract;
use core\classes\filesystem;
use core\classes\event as eventCore;
use core\classes\admin\dirFunc;

// ORM
use ORM\tree\compContTree;
use ORM\tree\routeTree;
use ORM\tree\wareframeTree;
use ORM\utils\rss as rssOrm;
use ORM\utils\rssProp as rssPropOrm;
use ORM\tree\componentTree;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * @author Козленко В.Л.
 */
class rss extends controllerAbstract {

    public function init() {

    }

    public function indexAction() {

        $contData = (new compcontTree())->select('cc.*', 'cc')
            ->join(componentTree::TABLE . ' c', 'c.id=cc.comp_id')
            ->where('c.sysname="objItem" AND cc.isDel="no"')
            ->fetchAll();

        $contTree = dhtmlxTree::all($contData, 0);
        self::setJson('contTree', $contTree);

        self::setJson('rss', (new rssOrm())->selectList('*', 'contId'));

        $propData = (new rssPropOrm())->selectAll('');
        foreach ($propData as $item) {
            self::setVar($item['key'], $item['val']);
        }

        $this->view->setBlock('panel', 'rss/rss.tpl.php');
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
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

        $rssOrm = new rssOrm();
        $rssOrm->delete();

        $rssPropOrm = new rssPropOrm();
        $propData = $rssPropOrm->selectAll('');
        $data = [];
        foreach ($propData as $item) {
            $key = $item['key'];
            $val = self::post($key);
            $rssPropOrm->update(['val' => $val], ['`key`' => $key]);
        } // foreach
        ;

        $selData = self::post('sel');
        $selData = substr($selData, 0, strlen($selData) - 1);
        if ($selData) {
            $selData = explode(',', $selData);
            $selData = array_map('intVal', $selData);

            $rssOrm->insertMulti(['contId' => $selData]);

        } // if selData
        // func. saveDataAction
    }

    // class action
}