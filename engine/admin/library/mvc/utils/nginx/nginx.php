<?php

namespace admin\library\mvc\utils\nginx;

// Conf
use \DIR;
use site\conf\DIR as DIR_CONF;

// Engine
use core\classes\render;
use core\classes\mvc\controllerAbstract;
use core\classes\filesystem;
use core\classes\admin\dirFunc;
use core\classes\comp as compCore;



/**
 * @author Козленко В.Л.
 */
class nginx extends controllerAbstract {

    public function init() {

    }

    public function indexAction() {
        $loadDir = dirFunc::getSiteDataPath('utils/nginx/');
        $textData = filesystem::loadFileContent($loadDir . 'data.txt');
        self::setVar('textData', $textData);

        $textData = filesystem::loadFileContent($loadDir . 'servData.txt');
        self::setVar('servData', $textData);

        $this->view->setBlock('panel', 'nginx/nginx.tpl.php');
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }


    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()){
            return;
        }

        $saveDir = dirFunc::getSiteDataPath('utils/nginx');

        $data = self::post('data');
        filesystem::saveFile($saveDir,  'data.txt', $data);

        $data = self::post('servdata');
        filesystem::saveFile($saveDir,  'servData.txt', $data);
        // func. saveDataAction
    }

    // class ajax
}