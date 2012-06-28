<?php

namespace core\comp\spl\oiPopular\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\userUtils;

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

        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/data.txt';
        $data = file_get_contents($file);
        if (!$data) {
            return;
        }
        //unpack("c1d/i*int", $miniDescrHead);
        // Получаем количество данных в файле
        $size = unpack('c1c', substr($data, 0, 1))['c'];
        $miniData = unpack('i' . $size . 'int', substr($data, 1));
        // 1 (байт) количество + 4 * $size размеры блоков
        $last = 1 + 4 * $size;
        // Вытаскиваем блоки. Кодировали мы их в
        // buildsys\library\event\comp\spl\oiPopular::createArtPopular
        foreach ($miniData as $key => $item) {
            $miniData[] = substr($data, $last, (int)$item);
            $last += (int)$item;
            unset($miniData[$key]);
        } // foreach

        $data = substr($data, $last);
        $list = \unserialize($data);
        unset($data);
        if ($list) {
            $tpl = userUtils::getCompTpl($comp);
            $nsPath = $comp['nsPath'];
            $tplFile = DIR::TPL . 'comp/' . $nsPath;
            (new render($tplFile, ''))
                ->setVar('list', $list)
                ->setVar('miniData', $miniData)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
        } // if
        // func. render
    }
    // class. catalogCont
}