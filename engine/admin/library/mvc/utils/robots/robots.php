<?php

namespace admin\library\mvc\utils\robots;

// Conf
use \DIR;

// Engine
use core\classes\render;
use core\classes\mvc\controllerAbstract;
use core\classes\filesystem;
use core\classes\event as eventsys;
use core\classes\admin\dirFunc;

//ORM
use ORM\robots as robotsOrm;

/**
 * Логика и настройка файла robots.txt
 * @see http://robotstxt.org.ru/
 * @see http://robotstxt.org.ru/robotsexclusion/guide
 * @see http://robotstxt.org.ru/robotsexclusion/spec
 * @see http://help.yandex.ru/webmaster
 *
 * @author Козленко В.Л.
 */
class robots extends controllerAbstract {

    public function init() {

    }

    public function indexAction() {

        $robotsOrm = new robotsOrm();
        $engineList = $robotsOrm->selectAll('*');
        self::setVar('engineList', $engineList);

        $loadDir = dirFunc::getSiteDataPath('utils/robots/');
        $textData = filesystem::loadFileContent($loadDir . 'data.txt');
        self::setVar('robotsText', $textData);

        $this->view->setBlock('panel', 'robots/robots.tpl.php');
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost())
            return;

        eventsys::callOffline(event::NAME, event::ITEM_SAVE);

        $crawlDelay = self::post('crawlDelay');
        $cleanParam = self::post('cleanParam');

        $robotsOrm = new robotsOrm();

        if (is_array($crawlDelay)) {
            foreach ($crawlDelay as $id => $crawlDelayVal) {
                $id = (int)$id;
                $saveData = array(
                    'crawlDelay' => $crawlDelayVal,
                    'cleanParam' => isset($cleanParam[$id]) ? $cleanParam[$id] : ''
                ); // array
                $robotsOrm->update($saveData, 'id=' . $id);
            } // foreach
        } // if

        $robotsText = self::post('robotsText');
        $saveDir = dirFunc::getSiteDataPath('utils/robots/');
        filesystem::saveFile($saveDir, 'data.txt', $robotsText);
        // func. saveDataAction
    }

    // class action
}