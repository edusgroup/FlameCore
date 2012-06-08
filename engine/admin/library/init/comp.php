<?php

namespace admin\library\init;

// Engine
use core\classes\request;
use core\classes\filesystem;
use core\classes\comp as compCore;

// Conf
use \DIR;
use \SITE;

// Model
use admin\library\mvc\manager\complist\model as complistModel;

/**
 * Класс инициализации управление всеми структруами GUI в адмнике.<br/>
 * Формат URL:<br/>
 * <b>$t</b>=comp&<b>$m</b>={Method_Name}&<b>id</b>={Content_ID}
 *
 * @author Козленко В.Л.
 */
class comp {

    const DEFAULT_VALUE = 'default';

    public function run($pSiteName) {
        // Получаем имя контроллера
        $contId = request::getVarInt('$c');

        global $gObjProp;
        $gObjProp = compCore::getCompContProp($contId);
        if (!isset($gObjProp['ns'])) {
            throw new \Exception('ContId: ' . $contId . ' not found', 345);
        }
        $contrObj = compCore::getCompObject($gObjProp);

        $nsPath = filesystem::nsToPath($gObjProp['ns']);
        $tplPath = DIR::getTplPath('comp/' . $nsPath);
        $themeResUrl = sprintf(DIR::THEME_RES_URL, SITE::THEME_NAME);
        $contrObj->__construct($tplPath, $themeResUrl);

        $contrObj->objProp = $gObjProp;
        $contrObj->contId = $contId;
        $contrObj->compId = (int)$gObjProp['compId'];
        $contrObj->setSiteName($pSiteName);
        unset($gObjProp);
        // Получаем метод, который хотим вызвать
        $methodName = trim(request::getVar('$m'));
        // Вызываем метод. Методы доступные для вызова должны иметь окончание Action
        $contrObj->callMethod($methodName);
        // Выводим на экран то что получилось
        $contrObj->render();
        // func. run
    }
    // class. comp
}

?>