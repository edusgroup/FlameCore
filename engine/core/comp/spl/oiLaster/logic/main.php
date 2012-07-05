<?php

namespace core\comp\spl\oiLaster\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;
// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\userUtils;

/**
 * Рендеринг списка последних obiItem компонентов
 *
 * @author Козленко В.Л.
 */
class main {

    /**
     * Создание списка последних obiItem компонентов
     * @static
     * @param $pName название компонента
     * @return void
     */
    public static function renderAction($pName){
        // Получаем параметры компонента
        $comp = dbus::$comp[$pName];
        // ID компонента
        $compId = $comp['compId'];
        // ID выбранного компонена в админке
        $contId = $comp['contId'];

        // Составляем путь до файла со списком последних obiItem
        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/data.txt';

        // Открываем список
        // TODO: Добавить в будующем, получение данных из memcached
        $data = file_get_contents($file);
        // Если данных нет, то выходим
        if (!$data) {
            return;
        }

        // Получаем количество данных в файле
        $size = unpack('c1c', substr($data, 0, 1))['c'];
        $miniData = unpack('i' . $size . 'int', substr($data, 1));
		//var_dump($miniData);
        // 1 (байт) количество + 4 * $size размеры блоков
        $last = 1 + 4 * $size;
        // Вытаскиваем блоки. Кодировали мы их в
        // buildsys\library\event\comp\spl\oiLaster::createOILaster
        foreach ($miniData as $key => $item) {
            $miniData[] = substr($data, $last, (int)$item);
			//print $item."<br/>";
            $last += (int)$item;
            unset($miniData[$key]);
        } // foreach
		
		
        $data = substr($data, $last);
        $list = \unserialize($data);

        // Преобразум данные в список
        unset($data);
        if ($list) {
            // Получаем имя шаблона, который выбрали в админке
            $tpl = userUtils::getCompTpl($comp);
            $nsPath = $comp['nsPath'];
            // Папка, где храняться шаблоны
            $tplPath = DIR::TPL . 'comp/' . $nsPath;
            (new render($tplPath, ''))
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
