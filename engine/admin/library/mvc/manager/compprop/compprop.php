<?php

namespace admin\library\mvc\manager\compprop;

// Model
use admin\library\mvc\manager\complist\model as complistModel;
use admin\library\mvc\manager\compprop\model;

// Engine
use core\classes\html\element as htmlelem;
use core\classes\render;
use core\classes\filesystem;
use core\classes\comp;

// Conf
use \DIR;
use \CONSTANT;

/**
 *
 * @author Козленко В.Л.
 */
class compprop extends \core\classes\mvc\controllerAbstract {

    public function init() {
    }

    /**
     * Отображение и редактирование параметров blockItemOrm
     */
    public function indexAction() {
        $contId = self::getInt('contid');
        // ID компонента
        self::setVar('contId', $contId);

        $compProp = comp::getCompPropByContId($contId);
        if (!$compProp || $contId == 0) {
            throw new \Exception('ContId: ' . $contId . ' not found', 234);
        }
        $nsPath = filesystem::nsToPath($compProp['ns']);
        $loadData = model::loadData($contId);
        if (!$loadData) {
            $loadData = comp::getCompContProp($contId);
            $loadData['parentLoad'] = 1;
        } // if

        // ===== Формируем список пользовательских шаблонов
        $tplPath = DIR::getTplPath('comp/' . $nsPath . CONSTANT::USER_FOLDER);
        // Получаем список файлов-шаблонов
        $tplArr = filesystem::dir2array($tplPath, filesystem::FILE);
        $tplList = array();
        $tplList['list'] = htmlelem::dirList2Select($tplArr);
        // Получаем сохранёное значение, если есть
        if (isset($loadData['tplUserFile'])) {
            $tplList['val'] = $loadData['tplUserFile'];
        }
        self::setVar('tplUserList', $tplList);

        // ===== Формируем список встроенных шаблонов
        $tplPath = DIR::getTplPath('comp/' . $nsPath . 'ext/');
        // Получаем список файлов-шаблонов
        $tplArr = filesystem::dir2array($tplPath, filesystem::FILE);
        $tplList = array();
        $tplList['list'] = htmlelem::dirList2Select($tplArr);
        // Получаем сохранёное значение, если есть
        if (isset($loadData['tplExtFile'])) {
            $tplList['val'] = $loadData['tplExtFile'];
        }
        self::setVar('tplExtList', $tplList);

        // ===== Формируем список пользовательских классов
        $classPath = DIR::getCompClassPath();
        $classPath .= $nsPath . CONSTANT::USER_FOLDER;
        // Получаем список папок
        $classArr = filesystem::dir2array($classPath, filesystem::FILE);
        $classList = array();
        $classList['list'] = htmlelem::dirList2Select($classArr);
        if (isset($loadData['classUserFile'])) {
            $classList['val'] = $loadData['classUserFile'];
        }
        self::setVar('classUserList', $classList);

        // ===== Формируем список пользовательских классов
        $classPath = DIR::getCompClassPath();
        $classPath .= $nsPath . 'ext/';
        // Получаем список папок
        $classArr = filesystem::dir2array($classPath, filesystem::FILE);
        $classList = array();
        $classList['list'] = htmlelem::dirList2Select($classArr);
        if (isset($loadData['classExtFile'])) {
            $classList['val'] = $loadData['classExtFile'];
        }
        self::setVar('classExtList', $classList);

        // ===== Есть ли рассширенные настройки 

        $classObj = comp::getCompObject($loadData, $compProp);
        $extendsSettings = (int)method_exists($classObj, 'compPropAction');
        self::setVar('extSettings', $extendsSettings);

        // ===== Установка значение radio button
        $tplType = $loadData['tplType'] ? : comp::DEFAULT_VALUE;
        $classType = $loadData['classType'] ? : comp::DEFAULT_VALUE;

        self::setVar('tplType', $tplType);
        self::setVar('classType', $classType);

        // ===== Наследуем ли мы настройки от родителя
        $parentLoad = isset($loadData['parentLoad']) ? $loadData['parentLoad'] : 1;
        self::setVar('parentLoad', $parentLoad);

        $this->view->setBlock('panel', 'block/compprop.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);

        // ID контента компонента
        $contId = self::getInt('contid');
        // Тип шаблона для админки
        $tplType = self::post('tplType');
        if (!in_array($tplType, array(comp::DEFAULT_VALUE, 'ext', 'user', 'builder'))) {
            throw new \Exception('Неверный тип tplType', 23);
        }
        // Тип класса для админки
        $classType = self::post('classType');
        if (!in_array($classType, array(comp::DEFAULT_VALUE, 'ext', 'user'))) {
            throw new \Exception('Неверный тип classType', 24);
        }

        $tplUserFile = self::post('tplUser');
        $classUserFile = self::post('classUser');

        $tplExtFile = self::post('tplExt');
        $classExtFile = self::post('classExt');

        // Нужно ли наследовать от родителя настройки
        $parentLoad = self::postInt('parentLoad');

        // ===== Сохранение данных 
        $compProp = comp::getCompPropByContId($contId);
        $nsPath = filesystem::nsToPath($compProp['ns']);

        model::checkTplName($nsPath . \CONSTANT::USER_FOLDER, $tplUserFile);
        model::checkTplName($nsPath . 'ext\\', $tplExtFile);

        model::checkClassName($nsPath . \CONSTANT::USER_FOLDER, $classUserFile);
        model::checkClassName($nsPath . 'ext\\', $classExtFile);

        $saveData = array(
            'contId' => $contId,
            'tplType' => $tplType,
            'classType' => $classType,
            'classUserFile' => $classUserFile,
            'tplUserFile' => $tplUserFile,
            'tplExtFile' => $tplExtFile,
            'classExtFile' => $classExtFile,
            'parentLoad' => $parentLoad
        );

        model::saveData($contId, $saveData);

        // ===== Проверка на существования расширенных настроек
        $objProp['classType'] = $classType;
        $objProp['classFile'] = $classUserFile;
        //$extendsSettings = (int) model::isExistsExtendsProp($objProp);
        $classObj = comp::getCompObject($saveData, $compProp);
        $extendsSettings = (int)method_exists($classObj, 'compPropAction');
        self::setVar('json', array('ok' => 1, 'extSettings' => $extendsSettings));

        // func. saveDataAction
    }

}

?>