<?php

namespace core\comp\spl\imgGallery\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\userUtils;

/**
 * Description of html
 *
 * @author Козленко В.Л.
 */
class main {

    public static function renderAction($pName) {
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];
        $prefix = 'comp/' . $compId . '/' . $contId .'/';
        $file = DIR::APP_DATA . $prefix . 'list.txt';
        $dataList = @file_get_contents($file);
        $dataList = unserialize($dataList);
        if ($dataList) {
            $tpl = userUtils::getCompTpl($comp);
            $nsPath = $comp['nsPath'];
            $tplFile = DIR::TPL . 'comp/' . $nsPath;
            (new render($tplFile, ''))
                ->setVar('list', $dataList)
                ->setVar('href', DIR::URL_IMG_RESIZE_PUBLIC.$prefix)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
        } // if $dataList
        // func. render
    }
    // class. html
}