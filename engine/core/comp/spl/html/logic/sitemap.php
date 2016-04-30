<?php

namespace core\comp\spl\html\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;
use core\classes\site\dir as sitePath;


/**
 * Description of sitemap
 *
 * @author Козленко В.Л.
 */
class sitemap {

    public static function renderAction($pName) {
        $file = DIR::APP_DATA . 'sitemap/sitemap.html';

        $fr = fopen($file, 'r');
        fpassthru($fr);
        fclose($fr);
        // func. render
    }

    // class. sitemap
}