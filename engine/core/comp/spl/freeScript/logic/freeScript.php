<?php

namespace core\comp\spl\freeScript\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\word;
use core\classes\userUtils;
use core\classes\dbus;
use core\classes\render;
use core\classes\site\dir as sitePath;
use core\classes\admin\dirFunc;


/**
 * Description of html
 *
 * @author �������� �.�.
 */
class freeScript {

    public static function renderAction($pName) {
		$comp = dbus::$comp[$pName];
        $comp['obj']->run($comp);
        // func. render
    }
	
	 public static function init($pName) {
		$comp = &dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];

		$pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
        $loadDir = dirFunc::getSiteDataPath($pathPrefix);
		$data = @file_get_contents($loadDir.'data.txt');
		if ( !$data){
			return;
		}
		$data = \unserialize($data);
		
		// ������ � �������� �������� �������� �����
        $freeScriptName = dirFunc::getSiteClassCore($comp['nsPath']).'script/'.$data['file'];
		@include($freeScriptName);
		$className = '\\core\\comp\\spl\\freeScript\\logic\\'.substr($data['file'], 0, strlen($data['file']) - 4).'Mvc';
		$comp['obj'] = new $className();
		$comp['obj']->init($comp);
		// func. init
	 }

    // class. html
}