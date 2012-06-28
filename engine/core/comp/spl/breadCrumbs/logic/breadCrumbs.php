<?php

namespace core\comp\spl\breadCrumbs\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;
use core\classes\word;
use core\classes\render;

/**
 * Description of objItem
 *
 * @author Козленко В.Л.
 */
class breadCrumbs {

    public static function renderAction($pName) {
        $std = dbus::$comp[$pName];
        $breadcrumbs = $std['breadcrumbs'];
        foreach ($breadcrumbs as &$item) {
            $name = $item['name'];
            // Если есть breadCrumbsCaption, т.е. заголовок для крошки
            if (isset(dbus::$vars[$name]['caption'])) {
                $item['caption'] = dbus::$vars[$name]['caption'];
            }else
            if (isset(dbus::$comp[$name]['caption'])) {
                $item['caption'] = dbus::$comp[$name]['caption'];
            } // if
        } // foreach

        $tpl = $std['tpl'];
        $nsPath = $std['nsPath'];
        $tplFile = DIR::TPL . 'tpl/' . SITE::THEME_NAME . '/comp/' . $nsPath;
        $render = new render($tplFile, '');
        $render->setMainTpl($tpl)
            ->setVar('breadcrumbs', $breadcrumbs)
            ->setContentType(null)
            ->render();
        // renderFile
    }

    // class menu
}