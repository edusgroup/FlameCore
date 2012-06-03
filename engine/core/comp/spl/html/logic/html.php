<?php

namespace core\comp\spl\html\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;


/**
 * Description of html
 *
 * @author Козленко В.Л.
 */
class html {
    //public static $urlTplList = ['category' => null];

    public static function renderAction($pName) {
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];


        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/html.txt';
        print file_get_contents($file);
        // func. render
    }
    // class. html
}