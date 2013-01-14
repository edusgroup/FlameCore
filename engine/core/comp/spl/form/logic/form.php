<?php

namespace core\comp\spl\form\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;
use core\classes\userUtils;
use core\classes\render;
use core\classes\site\dir as sitePath;

/**
 * Description of form
 *
 * @author Козленко В.Л.
 */
class form {
    //public static $urlTplList = ['category' => null];

    public static function renderAction($pName) {
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];

        $tpl = userUtils::getCompTpl($comp);
        $tplPath = sitePath::getSiteCompTplPath($comp['isTplOut'], $comp['nsPath']);
        (new render($tplPath, ''))
            ->setVar('action', $comp['action'])
            ->setMainTpl($tpl)
            ->setContentType(null)
            ->render();
        //$file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/html.txt';
        //print file_get_contents($file);
        // func. render
    }

    public static function init(){
       // dbus::addJsDyn('/res/core/js/comp/spl/form/universal.js');
        // func. init
    }
    // class. form
}