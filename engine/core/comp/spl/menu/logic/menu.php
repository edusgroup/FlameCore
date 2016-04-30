<?php

namespace core\comp\spl\menu\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;
use core\classes\word;
use core\classes\render;
use core\classes\site\dir as sitePath;

/**
 * Description of objItem
 *
 * @author Козленко В.Л.
 */
class menu {

    public static function renderAction($pName) {
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];
        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/menu.txt';
        $data = file_get_contents($file);

        //var_dump($data);

        $menuTree = null;
        if ($data) {
            $menuTree = \unserialize($data);
        }
		
        if ($menuTree) {
            $tpl = $comp['tpl'];
            $tplPath = sitePath::getSiteCompTplPath($comp['isTplOut'], $comp['nsPath']);
            $render = new render($tplPath, '');
            $render->setVar('menuTree', $menuTree);
            $render->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
        }
        // renderFile
    }

    // class menu
}