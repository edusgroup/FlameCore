<?php

namespace admin\library\mvc\utils\ajax;

// Conf
use \DIR;
use site\conf\DIR as DIR_CONF;

// Engine
use core\classes\render;
use core\classes\mvc\controllerAbstract;
use core\classes\filesystem;
use core\classes\admin\dirFunc;
use core\classes\comp as compCore;

// ORM
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\utils\ajax as ajaxOrm;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;


/**
 * @author Козленко В.Л.
 */
class ajax extends controllerAbstract {

    public function init() {

    }

    public function indexAction() {
        $compTree = dhtmlxTree::createTreeOfTable(new componentTree());
        self::setJson('compTreeJson', $compTree);

        $saveData = (new ajaxOrm())->selectAll('*') ?: new \stdClass();
        self::setJson('saveData', $saveData);


        /*$selCompId = 3;
        $compData = compCore::getClassDataByCompId($selCompId);
        $nsPath = filesystem::nsToPath($compData['ns']);
        $classTree = model::getAjaxTree($nsPath);*/

        $this->view->setBlock('panel', 'ajax/ajax.tpl.php');
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function loadClassTreeAction(){
        $this->view->setRenderType(render::JSON);

        $num = self::getInt('num');

        $selCompId = self::getInt('compId');
        $compData = compCore::getClassDataByCompId($selCompId);
        $nsPath = filesystem::nsToPath($compData['ns']);
        $classTree = model::getAjaxTree($nsPath);

        self::setVar('json', ['classTreeJson' => $classTree, 'num'=>$num]);
        // func. loadClassTreeAction
    }

    public function loadSettingsAction(){
        $classFile = self::get('classFile');

        $selCompId = self::getInt('compId');
        $compData = compCore::getClassDataByCompId($selCompId);
        if ( !$compData){
            throw new \Exception('Comp not found', 235);
        }


        $nsPath = filesystem::nsToPath($compData['ns']);
        $className = compCore::fullNameClassAdmin($classFile, $compData['ns'], 'ajax');
        // TODO: Сделать проверку на существование файла
        $compObj = new $className();
        $compObj->settingsRender($this, $nsPath);

        // Вполне возможно класс, может быть удалён с файловой системы, а уже используется
        // тогда системы выдаст исключение, от том что файла нет, исключение нужно перехватить

        //
        //$methodList = get_class_methods($compObj);


        // func. loadSettingsAction
    }

    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()){
            return;
        }
        $num = self::postInt('num');
        $data = self::post('data');
        if ( !@json_encode($data)){
            throw new \Exception('Bad data');
        }

        $compId = self::postInt('compId');
        $classFile = self::post('classFile');
        $name = self::post('name');

        $compData = compCore::getClassDataByCompId($compId);
        if ( !$compData){
            throw new \Exception('Comp not found', 235);
        }

        $nsPath = filesystem::nsToPath($compData['ns']);
        $className = compCore::fullNameClassAdmin($classFile, $compData['ns'], 'ajax');
        // TODO: Сделать проверку на существование файла
        $compObj = new $className();
        $codeData = $compObj->createClassFile($name, $compId, $data);

        $whereId = $num < 0 ? [] : ['id'=>$num];

        $ajaxOrm = new ajaxOrm();
        $oldName = $ajaxOrm->get('name', $whereId);
        if ( $oldName){
            filesystem::unlink(DIR_CONF::APP_DATA.'utils/ajax/'.$oldName.'.php');
        }

        filesystem::saveFile(DIR_CONF::APP_DATA.'utils/ajax/', $name.'.php', $codeData);

        $newId = $ajaxOrm->saveExt($whereId,
                                 ['classFile'=>$classFile,
                                 'compId'=>$compId,
                                 'data' => $data,
                                 'name' => $name]);

        self::setVar('json', ['newId'=>$newId, 'oldId'=>$num]);
        // func. saveDataAction
    }

    // class ajax
}