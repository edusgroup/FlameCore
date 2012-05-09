<?php

namespace admin\library\init;

use core\classes\request;
// Conf
use \DIR;
use \SITE;
use \CONSTANT as CONSTANT;

/**
 * Класс инициализации управление всеми структруами GUI в адмнике.
 *
 * @author Козленко В.Л.
 */
class manager {

    public function run() {
        // Получаем имя контроллера
        $contrName = trim(request::getVar('$c'));
        // Формируем полное имя класса контроллера
        $contrClassName = 'admin\library\mvc\manager\\' . $contrName . '\\' . $contrName;
        // Проверка на сущесвование контроллера и сразу автоподгрузка
        if (!class_exists($contrClassName)) {
            throw new \Exception('Not found controller: ' . $contrClassName, 24);
        }
        
        $themeResUrl = sprintf(DIR::THEME_RES_URL, SITE::THEME_NAME);
        $contrObj = new $contrClassName(DIR::getTplPath('manager'), $themeResUrl);
        // Получаем метод, который хотим вызвать
        $methodName = trim(request::getVar('$m'));
        // Вызываем метод. Методы доступные для вызова должны иметь окончание Action
        $contrObj->callMethod($methodName);
        // Выводим на экран то что получилось
        $contrObj->render();
    }

}

?>