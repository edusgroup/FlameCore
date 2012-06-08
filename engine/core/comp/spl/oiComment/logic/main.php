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

/**
 * Description of main
 *
 * @author Козленко В.Л.
 */
class main {

    public static function renderAction($pName) {
        $comp = dbus::$comp[$pName];

        $tpl = userUtils::getCompTpl($comp);
        
        $acticleId = dbus::$vars[$comp['varible']]['id'];
        
        $splitId = word::idToSplit($acticleId);
        $commFile = DIR::APP_DATA . 'comp/' . $comp['compId'] . '/' . $comp['type'] . '/'.$splitId.'/comm.html';

        $nsPath = $comp['nsPath'];
        $tplFile = DIR::SITE_CORE . 'tpl/' . SITE::THEME_NAME . '/comp/' . $nsPath;
        $render = new render($tplFile, '');
        $render->setMainTpl($tpl)
                ->setContentType(null)
                ->setVar('blockItemId', $comp['blockItemId'])
                ->setVar('acticleId', $acticleId)
                ->setVar('commFile', $commFile)
                ->render();
    }
    
    public static function init(){
        dbus::addJsDyn('/res/core/js/comp/spl/oiComment/comments.js');
        // func. init
    }

}