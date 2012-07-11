<?php
namespace admin\library\mvc\comp\spl\objItem\category;

// Engine
use core\classes\render;

// Plugin
use admin\library\mvc\plugin\fileManager\fileManager;
use admin\library\mvc\plugin\fileManager\model as fileManagerModel;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;
// Event
use admin\library\mvc\comp\spl\objItem\category\article\event;

// Conf
use \DIR;

trait category{
    public function itemAction() {
		$contId = $this->contId;
        self::setVar('contId', $contId);
        $compId = $this->compId;
		// TODO: ¬ставить проверку на существование $objItemId
        $objItemId = self::getInt('id');
        self::setVar('objItemId', $objItemId, -1);
		
		
		

        $tplFile = self::getTplFile();

        $this->view->setBlock('panel', $tplFile);
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        //$this->view->setMainTpl('main.tpl.php');
    }

    public function blockItemShowAction() {
        $this->view->setRenderType(render::NONE);
        echo 'people::blockItemShowAction() | No settings in this';
    }
	
	public function saveDataAction() {
        $this->view->setRenderType(render::JSON);
	}

}