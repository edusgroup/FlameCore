<?php

namespace core\comp\spl\breadCrumbs\logic;

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
class breadCrumbs {

    public static function renderAction($pName) {
		if ( $_SERVER['DOCUMENT_URI'] == '/' ){
			return;
		}
        $comp = dbus::$comp[$pName];
        $breadcrumbs = $comp['breadcrumbs'];
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

        $tpl = $comp['tpl'];
        $tplPath = sitePath::getSiteCompTplPath($comp['isTplOut'], $comp['nsPath']);
        $render = new render($tplPath, '');
        $render->setMainTpl($tpl)
            ->setVar('breadcrumbs', $breadcrumbs)
            ->setContentType(null)
            ->render();
        // renderFile
    }
    // class menu
}