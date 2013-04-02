<?php

namespace admin\library\mvc\manager\complist;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// ORM
use ORM\tree\componentTree;
use ORM\tree\compContTree;

// Engine
use core\classes\render;
use core\classes\storage\storage;
use core\classes\event as eventCore;
use core\classes\comp;
use core\classes\word;

// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;

// Event
use admin\library\mvc\manager\complist\event as eventCompList;

/**
 * Управление и отображение компонентами
 *
 * @author Козленко В.Л.
 */
class complist extends \core\classes\mvc\controllerAbstract {

    public function init() {

    }

    public function indexAction() {

        $contId = self::getInt('contid', null);
        //$compId = self::getInt('compid');

        dhtmlxTree::setField(['onlyFolder']);
        $compTree = dhtmlxTree::createTreeOfTable(new componentTree());
        dhtmlxTree::clear();
        self::setJson('compTree', $compTree);

        $compId = null;
        if ( $contId != null ){
            $compId = (new compContTree())->getCompId($contId);
        }

        self::setVar('contId', $contId);
        self::setVar('compId', $compId);

        $this->view->setBlock('panel', 'block/complist.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Загрузка списка контента для компонента
     */
    public function loadContTreeAction() {
        $this->view->setRenderType(render::JSON);
        $compId = self::getInt('compid');

        $return['tree'] = model::getTreeCompCont($compId);
        self::setVar('json', $return);
        // func. loadContTreeAction
    }

    /**
     * Добавляем папку. AJAX
     */
    public function dirAddAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()){
            return;
        }

        $contId = self::postInt('treeid', 0);
        $name = self::post('name');

        $contData = comp::getCompPropByContId($contId);
        $compId = self::postInt('compid');

        eventCore::callOffline($contData['classname'], 'tree:diradd', ['compId' => $compId], $contId);

        $name = explode('|', $name, 2);
        if ( count($name) == 2 ){
            $seoName = $name[1];
            $name = $name[0];
        }else{
            $name = $name[0];
            $seoName = word::wordToUrl($name);
        }

        $userData['comp_id'] = $compId;
        $userData['seoName'] = $seoName;

        $objJson = dhtmlxTree::add((new compContTree()), $name, $contId, dhtmlxTree::FOLDER, $userData);
        $objJson['treeName'] = self::post('treeName');
        self::setVar('json', $objJson);
        // func. dirAddAction
    }

    public function fileAddAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()){
            return;
        }

        $treeId = self::postInt('treeid', 0);
        $name = self::post('name');

        $userData['comp_id'] = self::postInt('compid', -1);
        $userData['seoName'] = word::wordToUrl($name);

        $objData = dhtmlxTree::add((new compContTree()), $name, $treeId, dhtmlxTree::FILE, $userData);

        $objData['treeName'] = self::post('treeName');
        self::setVar('json', $objData);
        // func. fileAddAction
    }

    public function renameObjAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()){
            return;
        }

        $contId = self::postInt('id', -1);
        $name = self::post('name');
        $objJson = dhtmlxTree::rename(new compContTree(), $name, $contId);

        $contData = comp::getCompPropByContId($contId);
        $compId = $contData['compId'];
        $className = $contData['classname'];

        eventCore::callOffline($className, 'tree:rename', ['compId' => $compId], $contId );

        $objJson['treeName'] = self::post('treeName');
        self::setVar('json', $objJson);
        // func. renameObjAction
    }

    /**
     * Удалиние ветки в дереве страниц
     * @return void
     */
    public function rmObjAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()){
            return;
        }
        $contId = self::postInt('id', -1);

        $contData = comp::getCompPropByContId($contId);
        $compId = $contData['compId'];
        $className = $contData['classname'];

        eventCore::callOffline($className, eventCompList::DELETE, ['compId' => $compId], $contId);

        (new compContTree())->update('isDel="yes"', 'id=' . $contId);

        self::setVar('json',['id' => $contId,
                             'treeName' => self::post('treeName')]);
        // func. rmObjAction
    }
    // class complist
}