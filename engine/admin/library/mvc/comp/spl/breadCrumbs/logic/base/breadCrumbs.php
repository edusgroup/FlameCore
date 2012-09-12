<?php

namespace admin\library\mvc\comp\spl\breadCrumbs;

// Conf
use \DIR;
use \SITE;
// Engine
use core\classes\render;
// ORM
use ORM\tree\routeTree;
use ORM\comp\spl\breadCrumbs\breadCrumbs as breadCrumbsOrm;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * Description of breadCrumbs
 *
 * @author Козленко В.Л.
 */
class breadCrumbs extends \core\classes\component\abstr\admin\comp {
    // Настрока кастом параметров в blockItem
    //use compBlockItem;

    public function __construct(string $pTplPath, string $pThemeResUrl) {
        parent::__construct($pTplPath, $pThemeResUrl);
    }

    public function init() {

    }

    /**
     * Index метод. Отображение дерева
     */
    public function indexAction() {

        self::setVar('contId', $this->contId);
        // Создаём дерево action
        $tree = dhtmlxTree::createTreeOfTable(new routeTree());
        self::setJson('acTree', $tree);

        $this->view->setBlock('panel', $this->tplFile);
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Получение данных хлебных крошек, по веткам дерева
     * в формате HTML
     */
    public function loadParamAction(){
        // Action ID ветки
        $actionId = self::getInt('itemid');
        // ID контента, которым управляем
        $contId = $this->contId;
        self::setVar('contId', $contId);

        // Список компонентов wareframe
        //$compList = [];

        // Получаем сохранённые данные
        $name = (new breadCrumbsOrm())->get('name', ['acId' => $actionId, 'contId' => $contId] );
        if ( $name ){
            self::setVar('name', $name);
        }

        // Получаем список компонентов в Wareframe по actionId
        //$compList['list'] = model::loadCompList($actionId);
        //self::setVar('complist', $compList);

        $this->view->setMainTpl('loadParam.tpl.php');
        // func. loadParamAction
    }

    public function saveDataAction(){
        // Указываем что, результат нужно отдать в формате JSON
        $this->view->setRenderType(render::JSON);
        $contId = $this->contId;
        // Action ID ветки
        $actionId = self::postInt('itemid');
        $name = self::post('name');
        // Сохраняем выбранные данные
        (new breadCrumbsOrm())->saveExt(
            ['acId' => $actionId,
             'contId'=>$contId],
            ['name' => $name]);

        // func. saveDataAction
    }

    public function getBlockItemParam($pBlockItemId, $pAcId){
        return model::createCrumbs($pBlockItemId, $pAcId);
        // func. getBlockItemParam
    }

    public function blockItemShowAction(){
        $this->view->setRenderType(render::NONE);
        print 'Не указаны';
    }

    public function getTableData($pContId) {

    }

    public function getTableOrm() {

    }
// class breadCrumbs
}