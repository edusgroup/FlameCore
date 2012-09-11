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
		// Получаем имя файла, где хранится список файлов изображений
        $prefix = 'comp/' . $compId . '/' . $contId .'/';
        $file = DIR::APP_DATA . $prefix . 'list.txt';
        $dataList = @file_get_contents($file);
        $dataList = unserialize($dataList);
		// Есть ли что нибудь в списке
        if ($dataList) {
            $tpl = userUtils::getCompTpl($comp);
            $tplFile = $comp['isTplOut'] ? DIR::SITE_CORE . '/tpl/comp/' : DIR::TPL . SITE::THEME_NAME. '/comp/';
            $tplFile .= $comp['nsPath'];
            (new render($tplFile, ''))
                ->setVar('list', $dataList)
                ->setVar('hrefResize', DIR::URL_IMG_RESIZE_PUBLIC.$prefix)
                ->setVar('hrefDist', DIR::URL_FILE_DIST.$prefix)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
        } // if $dataList
        // func. render
    }
    // class. html
}