<?php

namespace admin\library\mvc\comp\spl\form\logic\base;

// Engine
use core\classes\render;
use core\classes\filesystem;
use core\classes\event as eventCore;
use core\classes\validation\filesystem as fileValid;
use core\classes\admin\dirFunc;

// Conf
use \DIR;
use \SITE;

// ORM
use ORM\comp\spl\form\form as formOrm;
use ORM\tree\routeTree;
use ORM\blockItemSettings;
use ORM\blockItem as blockItemOrm;

// Event
use admin\library\mvc\manager\blockItem\event as blockItemEvent;

/**
 * @author Козленко В.Л.
 */
class form extends \core\classes\component\abstr\admin\comp {

    public function init() {

    }

    public function indexAction() {
        $contId = $this->contId;
        //$compId = $this->compId;

        self::setVar('contId', $contId, -1);
        //self::setVar('compId', $compId, -1);

        $ns = $this->objProp['ns'];
        $nsPath = filesystem::nsToPath($ns);

        $classList = [];

        $loadData = (new formOrm())->selectFirst('action', 'contId=' . $contId);
        if ($loadData) {
            $classList['val'] = $loadData['action'] . '.php';
        } // if

        $compLogicDir = DIR::CORE . 'core/comp/spl/form/action/';
        $classList['list'] = filesystem::dir2array($compLogicDir, filesystem::FILE);
        self::setVar('classList', $classList);

        $this->view->setBlock('panel', $this->tplFile);

        $this->view->setTplPath(dirFunc::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Сохранение данных компонента
     */
    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);
        $contId = $this->contId;

        $action = self::post('action');
        if (!fileValid::isSafe($action)) {
            throw new \Exception('Bad file name: ' . $action);
        }
        // TODO: Заменить на функцию из DIR
        $compLogicDir = DIR::CORE . 'core/comp/spl/form/action/';
        if (!is_file($compLogicDir . $action)) {
            throw new \Exception('File not exists: ' . $action);
        }
        $action = substr($action, 0, strlen($action) - 4);

        $blData = (new blockItemSettings())
            ->select('bis.blockItemId, bi.acId', 'bis')
            ->join(blockItemOrm::TABLE . ' bi', 'bis.blockItemId = bi.id')
            ->where('bis.statId=' . $contId);
        foreach ($blData as $item) {
            $eventData = ['blId' => (int)$item['blockItemId'], 'owner' => 'form'];
            eventCore::callOffline(
                blockItemEvent::BLOCKITEM,
                blockItemEvent::CHANGE,
                $eventData
            );

            $where = $item['acId'] ? ' AND id=' . $item['acId'] : '';
            (new routeTree())->update('isSave="yes"', 'id != 0' . $where);
        } // foreach

        (new formOrm())->saveExt(['contId' => $contId], ['action' => $action]);

        // func. saveDataAction
    }

    /**
     * Создание кода, при создании страницы WF
     * @param $pBlockItemId
     * @return string
     */
    public function getBlockItemParam($pBlockItemId, $pAcId) {
        $formOrm = new formOrm();
        $data = $formOrm->select('f.action', 'f')
            ->join(blockItemSettings::TABLE . ' bis', 'bis.statId = f.contId')
            ->where('bis.blockItemId=' . $pBlockItemId)
            ->comment(__METHOD__)
            ->fetchFirst();
        return "\t'action' => '{$data['action']}'" . PHP_EOL;
        // func. getBlockItemParam
    }

    // class form
}