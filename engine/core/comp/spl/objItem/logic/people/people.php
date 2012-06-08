<?php

namespace core\comp\spl\objItem\logic\people;

// conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\word;
use core\classes\userUtils;
use core\classes\dbus;
use core\classes\render;

/**
 * Description of article
 *
 * @author Козленко В.Л.
 */
class people {

    public static $urlTplList = array(
        'category' => null
    );

    public static function renderAction($pName) {
        print 'People';
        // func. run3
    }


    /**
     * @static
     * Установка параметров SEO
     */
    public static function setDataSeo($pName, $pParam) {
        $infoData = dbus::$comp[$pName]['data'];
        $linkNextTitle = $pParam['linkNextTitle'];

        $comp = dbus::$comp[$pName];

        if (isset(dbus::$vars[$comp['varTableName']]['seoUrl'])) {
            echo '<link rel="canonical" href="' . $infoData['canonical'] . '" />';
        } // if
        if (isset($infoData['prev'])) {
            echo '<link rel="prev" '
                . 'title="' . sprintf($linkNextTitle, $infoData['prev']['caption']) . '" '
                . 'href="' . $infoData['prev']['url'] . '" />';
        } // if
        if (isset($infoData['next'])) {
            echo '<link rel="next" '
                . 'title="' . sprintf($linkNextTitle, $infoData['next']['caption']) . '" '
                . 'href="' . $infoData['next']['url'] . '" />';
        } // if
        // func. setSeo
    }

    // class article
}