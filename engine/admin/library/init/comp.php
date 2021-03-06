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

    /**
     * Создание и рендеринг страницы компонента в админке
     * @param $pSiteName название сайта который мы редактируем
     * @throws \Exception
     */
    public function run($pSiteName) {
        // Получаем имя контроллера
        $contId = request::getVarInt('$c');

        // Получаем настройки ветки
        $objProp = compCore::findCompPropBytContId($contId);

        // Имя класса который задали в настройках
        $classFile = $objProp['classFile']?: '/base/'.$objProp['classname'].'.php';
        $compNs = $objProp['ns'];
        // Создаём контроллер класса
        $className = compCore::fullNameClassAdmin($classFile, $compNs);
        if ( !isClassExists($className) ){
            //throw new \Exception('Class not found '.$className);
            // TODO: сделать нормально отображение ошибки с помощью вёрстки
            echo 'Class not found <b>'.$className.'</b> - ';
            echo getClassPath($className).'<br/>';
            echo 'In: '.__CLASS__.' - '.__FILE__.'('.__LINE__.')';
            exit;
        }
        $contrObj = new $className('', '');
        // Получаем путь до ресурсов админки
        $themeResUrl = sprintf(DIR::THEME_RES_URL, SITE::THEME_NAME);
        // Получаем имя шаблона
        $tplFile = $objProp['tplFile']?: '/base/'.$objProp['classname'].'.tpl.php';
        // Преобразуем namespace в строку ввиде файлового путя
        $nsPath = filesystem::nsToPath($objProp['ns']);
        // Получаем данные по шаблону
        $tplFileData = compCore::getFileType($tplFile);
        // Получаем полный путь до папки с шаблонами
        $tplPath = compCore::getAdminCompTplPath($tplFileData['isOut'], $nsPath);
        // Устанавливаем путь к шаблонам и ресурсам для рендера
        $contrObj->setPathUrl($tplPath, $themeResUrl);

        // Запоминаем настройки, что бы 20 раз не вызывать в компоненте
        $contrObj->objProp = $objProp;
        $contrObj->contId = $contId;
        $contrObj->compId = (int)$objProp['compId'];
        $contrObj->tplFile = $tplFileData['file'];
        $contrObj->nsPath = $nsPath;

        // Устанавливаем имя сайта, который редактируем
        $contrObj->setSiteName($pSiteName);

        // Получаем метод, который хотим вызвать
        $methodName = trim(request::getVar('$m'));
        // Вызываем метод. Методы доступные для вызова должны иметь окончание(постфикс) Action
        // К примеру indexAction, mathAction, saveDataAction
        $contrObj->callMethod($methodName);
        // Выводим на экран то что получилось
        $contrObj->render();
        // func. run
    }

    // class. comp
}