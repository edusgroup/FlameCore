<?php

namespace core\comp\spl\menu\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;
// Engine
use core\classes\dbus;
use core\classes\word;
use core\classes\render;

/**
 * Description of article
 *
 * @author Козленко В.Л.
 */
class menu {

    public static function renderAction($pName) {
        $std = dbus::$comp[$pName];
        $compId = $std['compId'];
        $contId = $std['contId'];
        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/menu.txt';
        $data = file_get_contents($file);

        $menuTree = null;
        if ($data) {
            $menuTree = \unserialize($data);
        }
        if ($menuTree) {
            $tpl = $std['tpl'];
            $nsPath = $std['nsPath'];
            $tplFile = DIR::SITE_CORE . 'tpl/' . SITE::THEME_NAME . '/comp/' . $nsPath;
            $render = new render($tplFile, '');
            $render->setVar('menuTree', $menuTree);
            $render->setMainTpl($tpl)
                    ->setContentType(null)
                    ->render();
        }
        // renderFile
    }

    // class menu
}