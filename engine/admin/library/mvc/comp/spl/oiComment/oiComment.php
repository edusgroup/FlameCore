<?php

namespace admin\library\mvc\comp\spl\oiComment;

// Conf
use \DIR;
// Engine
use core\classes\render;
use core\classes\filesystem;
// ORM
use ORM\comp\spl\oiComment\oiCommentProp as oiCommentPropOrm;
use ORM\comp\spl\oiComment\oiCommentBi as oiCommentBiOrm;
use ORM\tree\routeTree;
use ORM\blockItemSettings;
use ORM\comp\spl\oiComment\oiCommentProp as oiCommentpPropOrm;
// Model
use admin\library\mvc\manager\varible\model as varModel;
use admin\library\mvc\manager\blockItem\model as blockItemModel;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;
// Trait
//use admin\library\mvc\manager\blockItem\compBlockItem;

/**
 * Description of oiComment
 *
 * @author Козленко В.Л.
 */
class oiComment extends \core\classes\component\abstr\admin\comp {
    //use compBlockItem;

    public function __construct(string $pTplPath, string $pThemeResUrl) {
        parent::__construct($pTplPath, $pThemeResUrl);
    }

    public function init() {
        
    }

    public function indexAction() {
        $contId = $this->contId;
        self::setVar('contId', $contId);

        $oiCommentPropOrm = new oiCommentPropOrm();
        $data = $oiCommentPropOrm->selectFirst('type', 'contId=' . $contId);
        self::setJson('data', $data);

        $tplFile = self::getTplFile();
        $this->view->setBlock('panel', $tplFile);
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);

        $type = self::post('type');
        $contId = $this->contId;

        $oiCommentPropOrm = new oiCommentPropOrm();
        $oiCommentPropOrm->save(
            'contId=' . $contId,
            ['type' => $type, 'contId' => $contId ]
        );
        // func. saveDataAction 
    }

    public function blockItem() {
        
    }

    public function getTableData($pContId) {
        
    }

    public function getTableOrm() {
        
    }

    /**
     * Сохранение данных, при настройке в blockItem
     * @param int $pBlockItemId
     * @param $pContr
     */
    public function blockItemSave(integer $pBlockItemId, $pContr){
        $save = [
            'tplListFile' => $pContr::post('tplListItemId'),
            'tplComFile' => $pContr::post('tplComItemId'), 
            'actionId' => $pContr::postInt('varName'),
            'blockItemId' => $pBlockItemId
        ];
        $oiCommentBiOrm = new oiCommentBiOrm();
        $oiCommentBiOrm->save('blockItemId='.$pBlockItemId, $save);
        // func. blockItemSave
    }

    /**
     * Создание кода, при создании страницы WF
     * @param $pBlockItemId
     * @return string
     */
    public function getBlockItemParam($pBlockItemId, $pAcId){
        $oiCommentBiOrm = new oiCommentBiOrm();
        $data = $oiCommentBiOrm->select('r.name, acp.type', 'acb')
                        ->join(routeTree::TABLE.' r', 'r.id=acb.actionId')
                        ->join(blockItemSettings::TABLE . ' bis', 'bis.blockItemId=acb.blockItemId')
                        ->join(oiCommentpPropOrm::TABLE . ' acp', 'acp.contId=bis.custContId')
                        ->where('acb.blockItemId='.$pBlockItemId)
                        ->comment(__METHOD__)
                        ->fetchFirst();
        return "\t'varible' => '{$data['name']}'," . PHP_EOL.
               "\t'blockItemId' => '$pBlockItemId'," . PHP_EOL.
               "\t'type' => '{$data['type']}'" . PHP_EOL;
        // func. getBlockItemParam
    }

    /**
     * Отображение блока, при настройке в blockitem
     */
    public function blockItemShowAction() {
        $blockItemId = self::getInt('blockitemid');
        $acId = self::getInt('acid');
        
        $oiCommentData = (new oiCommentBiOrm())->selectFirst('actionId, tplListFile, tplComFile', 'blockItemId='.$blockItemId);
        if ( $oiCommentData ){
            self::setJson('oiCommentData', $oiCommentData);
        } // if
        
        $itemData = blockItemModel::getCompData($blockItemId);

        if ($acId != -1) {
            $routeTree = new routeTree();
            $treeUrl = $routeTree->getTreeUrlById(routeTree::TABLE, $acId);
            if ($treeUrl) {
                $varList = varModel::getVarList($routeTree, $treeUrl);
                self::setVar('varList', ['list' => $varList]);
            } // if
        } // if ($acId)
        
        $nsPath = filesystem::nsToPath($itemData['ns']);
        
        // Дерево с шаблонами сайта для компонента
        $siteTplPath = DIR::getSiteCompTplPath($nsPath);
        $tree = dhtmlxTree::createTreeOfDir($siteTplPath);
        self::setJson('tplTree', $tree);

        $this->view->setMainTpl('blockItem.tpl.php');
        // func. blockItemShowAction
    }

// class oiList
}