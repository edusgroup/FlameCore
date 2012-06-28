<?php

namespace core\comp\spl\oiLaster\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;
// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\userUtils;

/**
 * Description of oiLaster
 *
 * @author Козленко В.Л.
 */
class main {

    public static function renderAction($pName){
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];


        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/list.txt';
        $data = file_get_contents($file);
        if (!$data) {
            return;
        }
        $list = \unserialize($data);
        unset($data);
        if ($list) {
            $tpl = userUtils::getCompTpl($comp);
            $nsPath = $comp['nsPath'];
            $tplFile = DIR::TPL . 'tpl/' . SITE::THEME_NAME . '/comp/' . $nsPath;
            (new render($tplFile, ''))
                ->setVar('list', $list)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
        } // if
        // func. render
    }
    // class. catalogCont
}
