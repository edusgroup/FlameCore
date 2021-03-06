<?php

namespace core\comp\spl\oiRandom\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\userUtils;
use core\classes\site\dir as sitePath;

/**
 * Description of oiPopular
 *
 * @author Козленко В.Л.
 */
class main {

    public static function renderAction($pName) {
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];

        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/';
        $data = file_get_contents($file.'data.txt');
        if (!$data) {
            return;
        }
		$data = \unserialize($data);
		if ( $data['fileNum'] ){
			$rnd = mt_rand(1, $data['fileNum']-1);
			$rndData = file_get_contents($file.'rnd'.$rnd.'.txt');
			$list = \unserialize($rndData);
			
			$tpl = userUtils::getCompTpl($comp);
            $tplPath = sitePath::getSiteCompTplPath($comp['isTplOut'], $comp['nsPath']);
            (new render($tplPath, ''))
                ->setVar('list', $list)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
		} // if data[fileNum]
        // func. renderAction
    }
    // class. main
}