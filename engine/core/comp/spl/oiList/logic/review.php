<?php

namespace core\comp\spl\oiList\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;
// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\userUtils;

/**
 * Description of main
 *
 * @author Козленко В.Л.
 */
class review {

	public static $urlTplList = [];

    /**
     * ����� ����� ������ ( ��� ��������� )
     * @param type $pName 
     */
    public static function renderAction($pName) {
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];

		$file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/1.txt';
		$data = @file_get_contents($file);
        if (!$data) {
            return;
        }
        $oiListData = \unserialize($data);
		if ( $oiListData ){
			$tpl = userUtils::getCompTpl($comp);
            $nsPath = $comp['nsPath'];
            $tplFile = DIR::TPL . 'comp/' . $nsPath;
            (new render($tplFile, ''))
                ->setVar('oiListData', $oiListData)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
		} // if ( $oiListData )
		
        // func. renderAction
    }
	
    // class. main
}