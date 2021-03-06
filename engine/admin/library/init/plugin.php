<?php

namespace admin\library\init;

// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;

// Engine
use core\classes\admin\dirFunc;
use core\classes\request;

/**
 * Класс инициализации управление всеми структруами GUI в адмнике.
 *
 * @author Козленко В.Л.
 */
class plugin {

    public function run($pSiteName) {
        // Получаем имя контроллера
        $contrName = trim(request::getVar('$c'));

        // Формируем полное имя класса контроллера
        $contrClassName = 'admin\library\mvc\plugin\\' . $contrName . '\\' . $contrName;
        // Проверка на сущесвование контроллера и сразу автоподгрузка
        if (!class_exists($contrClassName)) {
            throw new \Exception('Контроллер не найден: ' . $contrClassName, 24);
        }
        $themeResUrl = sprintf(DIR::THEME_RES_URL, SITE_CONF::THEME_NAME);
        $contrObj = new $contrClassName(dirFunc::getAdminTplPathIn('plugin'), $themeResUrl);
        // Получаем метод, который хотим вызвать
        $methodName = trim(request::getVar('$m'));
        // Вызываем метод. Методы доступные для вызова должны иметь окончание Action
        $contrObj->callMethod($methodName);
        // Выводим на экран то что получилось
        $contrObj->render();
    }

}