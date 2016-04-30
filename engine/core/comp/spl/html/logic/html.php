<?php

namespace core\comp\spl\html\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;
use core\classes\site\dir as sitePath;


/**
 * Description of html
 *
 * @author Козленко В.Л.
 */
class html {

    public static function renderAction($pName) {
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];

        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/html.txt';

        $fr = @fopen($file, 'r');
		if ( !$fr ){
			return;
		}
        fpassthru($fr);
        fclose($fr);
        // func. render
    }

    // class. html
}