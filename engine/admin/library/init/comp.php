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

        $objProp = compCore::getCompContProp($contId);
        if ( !isset($objProp['ns'])){
            throw new \Exception('ContId: '.$contId.' not found', 345);
        }
        $contrObj = compCore::getCompObject($objProp);

        $nsPath = filesystem::nsToPath($objProp['ns']);
        $nsPath = substr($nsPath, 0, strlen($nsPath)-1);
        
        $themeResUrl = sprintf(DIR::THEME_RES_URL, SITE::THEME_NAME);
        $contrObj->__construct(DIR::getTplPath('comp/'.$nsPath), $themeResUrl);

        $contrObj->objProp = $objProp;
        $contrObj->contId = $contId;
        $contrObj->compId = (int) $objProp['compId'];
        $contrObj->setSiteName($pSiteName);

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