<?php

namespace core\comp\spl\oiComment\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;
// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\word;
use core\classes\userUtils;
use core\classes\site\dir as sitePath;

/**
 * Description of main
 *
 * @author Козленко В.Л.
 */
class main {

    public static function renderAction($pName) {
        $comp = dbus::$comp[$pName];

        $tpl = userUtils::getCompTpl($comp);

        if ( $comp['varible'] == 'root'){
            echo 'Not set varible in oiCommnet';
            return;
        }
        $objItemId = dbus::$vars[$comp['varible']]['id'];
        
        $splitId = word::idToSplit($objItemId);
        $commFile = DIR::APP_DATA . 'comp/' . $comp['compId'] . '/' . $comp['type'] . '/'. $splitId.'comm.html';

        $tplPath = sitePath::getSiteCompTplPath($comp['isTplOut'], $comp['nsPath']);
        $render = new render($tplPath, '');
        $render->setMainTpl($tpl)
                ->setContentType(null)
                ->setVar('blockItemId', $comp['blockItemId'])
                ->setVar('objItemId', $objItemId)
                ->setVar('commFile', $commFile)
                ->render();
    }
    
    public static function init(){
        dbus::addJsDyn('/webcore/res/js/comp/spl/oiComment/comments.js');
        // func. init
    }

}