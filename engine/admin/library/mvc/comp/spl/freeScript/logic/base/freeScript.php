<?php

namespace admin\library\mvc\comp\spl\freeScript\logic\base;

// Engine
use core\classes\render;
use core\classes\filesystem;
use core\classes\event as eventCore;
use core\classes\admin\dirFunc;
use core\classes\comp;
use core\classes\validation\filesystem as fileValid;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// Conf
use \DIR;
use \SITE;

/**
 * @author Козленко В.Л.
 */
class freeScript extends \core\classes\component\abstr\admin\comp {

    public function init() {

    }

    public function indexAction() {
        $contId = $this->contId;
        $compId = $this->compId;

        self::setVar('contId', $contId, -1);
        self::setVar('compId', $compId, -1);

        $pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
        $loadDir = dirFunc::getSiteDataPath($pathPrefix);
		$data = [];
		if ( is_file($loadDir.'data.txt') ){
			$data = filesystem::loadFileContentUnSerialize($loadDir.'data.txt');
			self::setJson('saveData', $data);
		}
		
		$loadData = comp::findCompPropBytContId($contId);
        $nsPath = filesystem::nsToPath($loadData['ns']);

        // Дерево с файловой системой шаблонов сайта
        $siteTplPath = dirFunc::getSiteClassCore($nsPath).'script/';
        $fileTree = dhtmlxTree::createTreeOfDir($siteTplPath);
        self::setJson('fileTree', $fileTree);

        $this->view->setBlock('panel', $this->tplFile);

        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Сохранение данных компонента
     */
    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);
        $contId = $this->contId;
        $compId = $this->compId;
		
		$file = self::post('file');
		if ( !$file ){
			return;
		}
		
		$file = substr($file, 1);
		if (!fileValid::isSafe($file)) {
            throw new \Exception('Bad file name: ' . $file);
        }
		
		$loadData = comp::findCompPropBytContId($contId);
        $nsPath = filesystem::nsToPath($loadData['ns']);
		$siteTplPath = dirFunc::getSiteClassCore($nsPath).'script/';
		if ( !is_file($siteTplPath.$file) ){
			throw new \Exception('File not found: ' . $file);
		}
		
		$pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
        $saveDir = dirFunc::getSiteDataPath($pathPrefix);
		
		$data = ['file' =>$file];
		$dataPublic = \serialize($data);
        filesystem::saveFile($saveDir, 'data.txt', $dataPublic);
        // func. saveDataAction
    }

    // class html
}