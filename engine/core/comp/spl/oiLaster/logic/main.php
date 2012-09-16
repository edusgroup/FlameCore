<?php

namespace core\comp\spl\oiLaster\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;
// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\userUtils;
use core\classes\site\dir as sitePath;

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
        $list = \unserialize($data);
        // Преобразум данные в список
        unset($data);
        if ($list) {
            // Получаем имя шаблона, который выбрали в админке
            $tpl = userUtils::getCompTpl($comp);
            // Папка, где храняться шаблоны
            $tplPath = sitePath::getSiteCompTplPath($comp['isTplOut'], $comp['nsPath']);
            (new render($tplPath, ''))
                ->setVar('list', $list)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
        } // if
        // func. render
    }
    // class. catalogCont
}
