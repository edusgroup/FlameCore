<?php

namespace core\comp\spl\oiRandom\logic;

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
 * @author �������� �.�.
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
			$rnd = mt_rand(1, $data['fileNum']);
			$rndData = file_get_contents($file.'rnd'.$rnd.'.txt');
			$list = \unserialize($rndData);
			
			$tpl = userUtils::getCompTpl($comp);
            $nsPath = $comp['nsPath'];
            $tplFile = DIR::TPL . 'comp/' . $nsPath;
            (new render($tplFile, ''))
                ->setVar('list', $list)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
		} // if data[fileNum]

        // �������� ���������� ������ � �����
        /*$size = unpack('c1c', substr($data, 0, 1))['c'];
        $miniData = unpack('i' . $size . 'int', substr($data, 1));
        // 1 (����) ���������� + 4 * $size ������� ������
        $last = 1 + 4 * $size;
        // ����������� �����. ���������� �� �� �
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
        } // if*/
        // func. renderAction
    }
    // class. main
}