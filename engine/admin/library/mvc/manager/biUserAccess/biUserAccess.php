<?php

namespace admin\library\mvc\manager\biUserAccess;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;
// Conf
use \site\conf\SITE as SITE_CONF;
use \DIR;
// Engine
use core\classes\filesystem;
use core\classes\render;
use core\classes\mvc\controllerAbstract;
// ORM
use ORM\users\group as usersGroupOrm;
use ORM\blockItem as blockItemOrm;
use ORM\blockItem\relation as biGroupOrm;
// Model
use admin\library\mvc\manager\blockItem\model as blockItemModel;


/**
 * Логика настроек сайта
 *
 * @author Козленко В.Л.
 */
class biUserAccess extends controllerAbstract {

    public function init() {
        
    }

    public function indexAction() {
        $blockItemId = self::getInt('blockItemId');
        // Получаем ID компонента для объекта
        $itemData = blockItemModel::getCompData($blockItemId);
        $nsPath = filesystem::nsToPath($itemData['ns']);
        // Дерево с шаблонами сайта
        $siteTplPath = DIR::getSiteCompTplPath($nsPath);
        $tree = dhtmlxTree::createTreeOfDir($siteTplPath);
        self::setJson('tplTree', $tree);

        $groupTree = dhtmlxTree::createTreeOfTable(new usersGroupOrm());
        self::setJSON('groupTree', $groupTree);
        
        self::setVar('blockItemId', $itemData['id']);
        self::setVar('acId', $itemData['acId']);
        
        self::setJson('itemData', $itemData); 

        $biGroupOrm = new biGroupOrm();
        $biGroupData = $biGroupOrm->selectList('groupId', 'groupId', 'biId='.$blockItemId);
        self::setJson('biGroupData', $biGroupData);

        $this->view->setBlock('panel', 'block/biUserAccess.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }
    
    public function saveDataAction(){
        $this->view->setRenderType(render::JSON);
        
        $blockItemId = self::getInt('blockItemId');
        
        // Соотношение пользователя
        $biGroupOrm = new biGroupOrm();
        $biGroupOrm->delete('biId=' . $blockItemId);

        $group = self::post('group');
        if ($group) {
            $group = explode(',', $group);
            array_map(function($pGroupId)use($blockItemId, $biGroupOrm) {
                        $biGroupOrm->insert([
                            'biId' => $blockItemId,
                            'groupId' => (int) $pGroupId
                        ]);
                    }, $group);
        } // if
        
        $tpl = self::post('tpl');
        $userReg = self::postInt('userReg');
        
        $blockItemOrm = new blockItemOrm();
        $blockItemOrm->update([
            'userReg' => $userReg,
            'tplAccess' => $tpl
        ], 'id='.$blockItemId);
        
        // func. saveDataAction
    }

// class biUserAccess
}