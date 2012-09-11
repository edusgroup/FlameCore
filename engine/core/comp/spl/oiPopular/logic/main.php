<?php

namespace core\comp\spl\oiPopular\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\userUtils;

/**
 * Description of oiPopular
 *
 * @author Козленко В.Л.
 */
class main {

    public static function renderAction($pName) {
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];

        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/data.txt';
        $data = file_get_contents($file);
        if (!$data) {
            return;
        }
        $list = \unserialize($data);
        unset($data);
        if ($list) {
            $tpl = userUtils::getCompTpl($comp);
            $tplFile = $comp['isTplOut'] ? DIR::SITE_CORE . '/tpl/comp/' : DIR::TPL . SITE::THEME_NAME. '/comp/';
            $tplFile .= $comp['nsPath'];
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