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
use core\classes\validation\filesystem as filevalid;

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
        global $gObjProp;
        $gObjProp = model::loadData($contId);
        if (!$gObjProp) {
            $gObjProp = comp::getCompContProp($contId);
            $gObjProp['parentLoad'] = 1;
        } // if

        // =====================================================
        $category = '';
        $categoryDir = DIR::CORE . 'admin/library/mvc/comp/'.$nsPath.'category/';
        if (is_dir($categoryDir)) {
            $categoryList = [];
            $categoryList['list'] = filesystem::dir2array($categoryDir, filesystem::DIR);
            $categoryVal = $gObjProp['category'] ?: (isset($categoryList['list'][0])?$categoryList['list'][0]:'');
            $categoryList['val'] = $categoryVal;
            $category = 'category/'.$categoryVal.'/';
            $gObjProp['category'] = $categoryVal;
            self::setVar('categoryList', $categoryList);
        } // if is_dir

        // ===== Формируем список пользовательских шаблонов
        $tplPath = DIR::getTplPath('comp/' . $nsPath . $category. CONSTANT::USER_FOLDER);
        // Получаем список файлов-шаблонов
        $tplArr = filesystem::dir2array($tplPath, filesystem::FILE);
        $tplList = [];
        $tplList['list'] = htmlelem::dirList2Select($tplArr);
        $tplList['val'] = $gObjProp['tplUserFile'];
        self::setVar('tplUserList', $tplList);

        // ===== Формируем список встроенных шаблонов
        $tplPath = DIR::getTplPath('comp/' . $nsPath . $category.'ext');
        // Получаем список файлов-шаблонов
        $tplArr = filesystem::dir2array($tplPath, filesystem::FILE);
        $tplList = [];
        $tplList['list'] = htmlelem::dirList2Select($tplArr);
        $tplList['val'] = $gObjProp['tplExtFile'];
        self::setVar('tplExtList', $tplList);

        // ===== Формируем список пользовательских классов
        $classPath = DIR::getCompClassPath();
        $classPath .= $nsPath . $category.CONSTANT::USER_FOLDER;
        // Получаем список папок
        $classArr = filesystem::dir2array($classPath, filesystem::FILE);
        $classList = [];
        $classList['list'] = htmlelem::dirList2Select($classArr);
        $classList['val'] = $gObjProp['classUserFile'];
        self::setVar('classUserList', $classList);

        // ===== Формируем список пользовательских классов
        $classPath = DIR::getCompClassPath();
        $classPath .= $nsPath . $category.'ext';
        // Получаем список папок
        $classArr = filesystem::dir2array($classPath, filesystem::FILE);
        $classList = [];
        $classList['list'] = htmlelem::dirList2Select($classArr);
        $classList['val'] = $gObjProp['classExtFile'];
        self::setVar('classExtList', $classList);

        // ===== Есть ли рассширенные настройки 
        $classObj = comp::getCompObject($gObjProp, $compProp);
        $extendsSettings = (int)method_exists($classObj, 'compPropAction');
        self::setVar('extSettings', $extendsSettings);

        // ===== Установка значение radio button
        $tplType = $gObjProp['tplType'] ? : comp::DEFAULT_VALUE;
        $classType = $gObjProp['classType'] ? : comp::DEFAULT_VALUE;

        self::setVar('tplType', $tplType);
        self::setVar('classType', $classType);

        // ===== Наследуем ли мы настройки от родителя
        $parentLoad = isset($gObjProp['parentLoad']) ? $gObjProp['parentLoad'] : 1;
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
        if (!in_array($tplType, [comp::DEFAULT_VALUE, 'ext', 'user', 'builder'])) {
            throw new \Exception('Bad type tplType', 23);
        }
        // Тип класса для админки
        $classType = self::post('classType');
        if (!in_array($classType, [comp::DEFAULT_VALUE, 'ext', 'user'])) {
            throw new \Exception('Bad type classType', 24);
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

        $category = trim(self::post('category'));
        $categoryDir = '';
        if ( $category ){
            if ( !filevalid::isSafe($category) ){
                throw new \Exception('Bad name: '.$category, 234);
            }
            $categoryDir = DIR::CORE . 'admin/library/mvc/comp/spl/objItem/category/';
            if ( !is_dir($categoryDir.$category)){
                throw new \Exception('Category '.$category. ' not found', 239);
            }
            $categoryDir = 'category/'.$category.'/';
        } // if $category


        if ($tplUserFile) {
            model::checkTplName($nsPath . $categoryDir.\CONSTANT::USER_FOLDER, $tplUserFile);
        }
        if ($tplExtFile) {
            model::checkTplName($nsPath . $categoryDir.'ext', $tplExtFile);
        }

        if ( $classUserFile ){
            model::checkClassName($nsPath . $categoryDir.\CONSTANT::USER_FOLDER, $classUserFile);
        }
        if ( $classExtFile ){
            model::checkClassName($nsPath . $categoryDir.'ext', $classExtFile);
        }

        $saveData = [
            'contId' => $contId,
            'tplType' => $tplType,
            'classType' => $classType,
            'classUserFile' => $classUserFile,
            'tplUserFile' => $tplUserFile,
            'tplExtFile' => $tplExtFile,
            'classExtFile' => $classExtFile,
            'parentLoad' => $parentLoad,
            'category' => $category
        ];

        model::saveData($contId, $saveData);

        global $gObjProp;
        $gObjProp = $saveData;

        // ===== Проверка на существования расширенных настроек
        $objProp['classType'] = $classType;
        $objProp['classFile'] = $classUserFile;
        //$extendsSettings = (int) model::isExistsExtendsProp($objProp);
        $classObj = comp::getCompObject($saveData, $compProp);
        $extendsSettings = (int)method_exists($classObj, 'compPropAction');
        self::setVar('json', ['ok' => 1, 'extSettings' => $extendsSettings]);

        // func. saveDataAction
    }

}

?>