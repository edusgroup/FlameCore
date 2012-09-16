<?php

namespace core\comp\spl\catalogCont\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\userUtils;
use core\classes\site\dir as sitePath;

/**
 * Description of objItem
 *
 * @author Козленко В.Л.
 */
class catalogCont {
    //public static $urlTplList = ['category' => null];

    public static function renderAction($pName) {
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
            $tplPath = sitePath::getSiteCompTplPath($comp['isTplOut'], $comp['nsPath']);
            (new render($tplPath, ''))
                ->setVar('list', $list)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
        } // if
        // func. render
    }
    // class. catalogCont
}