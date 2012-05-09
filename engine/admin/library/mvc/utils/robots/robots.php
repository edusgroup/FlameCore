<?php

namespace admin\library\mvc\utils\robots;

// Conf
use \DIR;
// Engine
use core\classes\render;
use core\classes\mvc\controllerAbstract;
use core\classes\filesystem;
use core\classes\event as eventsys;
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
        
        $this->view->setBlock('panel', 'robots/robots.tpl.php');
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function saveDataAction(){
        $this->view->setRenderType(render::JSON);
        if (!self::isPost())
            return;
        
        eventsys::callOffline(event::NAME, event::ITEM_SAVE);
        
        $crawlDelay = self::post('crawlDelay');
        $cleanParam = self::post('cleanParam');

        $robotsOrm = new robotsOrm();
        
        if ( is_array($crawlDelay) ){
            foreach( $crawlDelay as $id => $crawlDelayVal ){
                $id = (int) $id;
                $saveData = array(
                    'crawlDelay' => $crawlDelayVal,
                    'cleanParam' => isset($cleanParam[$id])?$cleanParam[$id]:''
                ); // array
                $robotsOrm->update($saveData, 'id='.$id);
            } // foreach
        } // if
        
        //filesystem::saveFile($path, 'robots.txt', $data);
        // func. saveDataAction
    }

// class action
}

?>