<?php

namespace admin\library\mvc\comp\spl\html;

// Engine
use core\classes\render;
use core\classes\filesystem;
use core\classes\event as eventCore;
//use core\classes\filesystem;
// Conf
use \DIR;
use \SITE;

/**
 * @author Козленко В.Л.
 */
class html extends \core\classes\component\abstr\admin\comp {

    public function init() {

    }

    public function indexAction() {
        $contId = $this->contId;
        $compId = $this->compId;

        self::setVar('contId', $contId, -1);
        self::setVar('compId', $compId, -1);

        $pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
        $loadDir = DIR::getSiteDataPath($pathPrefix);
        $htmlCodeData = '';
        if (is_readable($loadDir . 'html.txt')) {
            $htmlCodeData = file_get_contents($loadDir . 'html.txt');
        }
        self::setVar('htmlCode', $htmlCodeData);

        $tplFile = self::getTplFile();
        $this->view->setBlock('panel', $tplFile);

        $this->view->setTplPath(DIR::getTplPath('manager'));
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

        $pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
        $saveDir = DIR::getSiteDataPath($pathPrefix);

        $htmlCode = self::post('htmlCode');

        filesystem::saveFile($saveDir, 'html.txt', $htmlCode);
        // func. saveDataAction
    }

    public function getTableData($pContId) {
        // Не исплользуется
    }

    public function getTableOrm() {
        // Не исплользуется
    }

    public function blockItemShowAction(){
        $this->view->setRenderType(render::NONE);
        echo 'Нет данных';
    }

}