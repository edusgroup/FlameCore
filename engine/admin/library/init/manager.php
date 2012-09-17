<?php

namespace admin\library\init;

use core\classes\request;

// Conf
use \DIR;
use \SITE;

// Engine
use core\classes\admin\dirFunc;

/**
 * Класс инициализации управление всеми структруами GUI в адмнике.
 *
 * @author Козленко В.Л.
 */
class manager {

    public function run($pSiteName) {
        // Получаем имя контроллера
        $contrName = trim(request::getVar('$c'));
        // Формируем полное имя класса контроллера
        $contrClassName = 'admin\library\mvc\manager\\' . $contrName . '\\' . $contrName;
        // Проверка на сущесвование контроллера и сразу автоподгрузка
        if (!class_exists($contrClassName)) {
            throw new \Exception('Not found controller: ' . $contrClassName, 24);
        }

        $themeResUrl = sprintf(DIR::THEME_RES_URL, SITE::THEME_NAME);
        $contrObj = new $contrClassName(dirFunc::getTplPath('manager'), $themeResUrl);
        // Получаем метод, который хотим вызвать
        $methodName = trim(request::getVar('$m'));
        $contrObj->setSiteName($pSiteName);
        // Вызываем метод. Методы доступные для вызова должны иметь окончание Action
        $contrObj->callMethod($methodName);
        // Выводим на экран то что получилось
        $contrObj->render();
    }

}