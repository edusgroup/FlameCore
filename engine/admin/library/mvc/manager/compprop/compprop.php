<?php

namespace admin\library\mvc\manager\compprop;

// Model
use admin\library\mvc\manager\complist\model as complistModel;

// Engine
use core\classes\html\element as htmlelem;
use core\classes\render;
use core\classes\filesystem;
use core\classes\comp;
use core\classes\validation\filesystem as filevalid;

// Conf
use \DIR;
use \CONSTANT;

// ORM
use ORM\compprop as compPropOrm;

// Init
use admin\library\init\comp as compInit;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

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
        // Есть ли такой компонент
        if (!$compProp || $contId == 0) {
            throw new \Exception('ContId: ' . $contId . ' not found', 234);
        } // if

        // Получаем Ns пусть до компонента
        $nsPath = filesystem::nsToPath($compProp['ns']);

        // Сохранённые настройки
        $loadData = model::loadData($contId);
        // Если настроек нет
        if (!$loadData) {
            // Выставляем их по умолчанию или ищем выше веткой
            $loadData = comp::findCompPropUpToRoot($contId);
            // Выставляем флаг, что данные загружены от родителя(хотя их может и не быть)
            //$loadData['parentLoad'] = 1;
        } // if

        $extendsSettings = 0;
        // Если настроеки были или мы их нашли
        if ( $loadData ){
            // ===== Есть ли рассширенные настройки
            $extendsSettings = model::isClassHasExtendsProp($loadData['classFile'], $compProp['ns']);
        } // if

        self::setJSON('loadData', $loadData);
        self::setJSON('compProp', $compProp);
        self::setVar('extSettings', $extendsSettings);

        $tree = model::getClassTree($nsPath);
        self::setJson('classTree', $tree);

        $tree = model::getTplTree($nsPath);
        self::setJson('tplTree', $tree);

        $this->view->setBlock('panel', 'block/compprop.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);

        // ID контента компонента
        $contId = self::getInt('contid');
        // Получаем данные по компоненту
        $compProp = comp::getCompPropByContId($contId);
        // Есть ли такой компонент
        if (!$compProp || $contId == 0) {
            throw new \Exception('ContId: ' . $contId . ' not found', 231);
        } // if
        $nsPath = filesystem::nsToPath($compProp['ns']);

        // ================ Класс =====================================
        // Выбранный класс файл для админки
        $classFile = self::post('classFile');
        if ( !$classFile ){
            return;
        } // if
        $classFileData = comp::getFileType($classFile);
        // Правильно ли имя файла
        filevalid::isSafe($classFileData['file'], new \Exception('Неверное имя файла:' .$classFileData['file']));

        // Проверяем налачие файла
        $classFilePath = comp::getCompClassPath($classFileData['isOut'], $nsPath);
        if ( !is_file($classFilePath.$classFileData['file']) ){
            throw new \Exception('File : ' . $classFileData['file'] . ' not found', 235);
        } // if

        // ================ Шаблон =====================================
        // Выбранный класс файл для админки
        $tplFile = self::post('tplFile');
        if ( !$tplFile ){
            return;
        } // if
        $tplFileData = comp::getFileType($tplFile);
        // Правильно ли имя файла
        filevalid::isSafe($tplFileData['file'], new \Exception('Неверное имя файла:' .$tplFileData['file']));

        // Проверяем налачие файла
        $tplFilePath = comp::getCompTplPath($tplFileData['isOut'], $nsPath);
        if ( !is_file($tplFilePath.$tplFileData['file']) ){
            throw new \Exception('File : ' . $tplFileData['file'] . ' not found', 235);
        } // if

        // ================= Сохранение ================================
        $parentLoad = self::postInt('parentLoad');

        ( new compPropOrm())->saveExt(
            ['contId'=>$contId],
            ['classFile' => $classFile,
             'tplFile' => $tplFile,
             'parentLoad' => $parentLoad]);

        $extendsSettings = model::isClassHasExtendsProp($classFile, $compProp['ns']);
        $data = ['extSettings' => $extendsSettings];
        self::setVar('json', $data);

        // func. saveDataAction
    }

    // class compprop
}