<?php

namespace admin\library\mvc\manager\varible;

// Engine
use core\classes\DB\tree;
use core\classes\comp;
use core\classes\filesystem;
use core\classes\validation\filesystem as filevalid;

// ORM
use ORM\tree\routeTree;
use ORM\urlTreePropVar;
use ORM\varComp as varCompOrm;
use ORM\tree\componentTree;
use ORM\tree\compContTree;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// Model
use admin\library\mvc\manager\varible\model;
use admin\library\mvc\manager\complist\model as complistModel;

// Conf
use \DIR;
use \site\conf\DIR as SITE_DIR;

class varComp {

    /**
     * Отображение Тип дерево в переменных
     * @param type $pController
     * @param integer $pActionId
     */
    public static function show($pController, integer $pActionId, $pInclude = null) {
        // Получаем сохранёные данные
        $dataLoad = (new varCompOrm())->select('vc.*, c.ns', 'vc')
            ->joinLeftOuter(componentTree::TABLE . ' c', 'c.id=vc.compId')
            ->where('vc.acId=' . $pActionId)
            ->comment(__METHOD__)
            ->fetchFirst();

        // Есть ли сохранёные данные
        if ($dataLoad) {
            $compId = (int)$dataLoad['compId'];
            // Описание
            $pController->setVar('descrip', $dataLoad['descrip']);
            // Компонент ID
            $pController->setVar('compid', $compId);

            // Получаем Ns пусть до компонента
            $nsPath = filesystem::nsToPath($dataLoad['ns']);

            // Получаем дерево контена, если $compId доступен, т.е. был выбран компонент
            $contTree = $compId ? dhtmlxTree::createTreeOfTable(
                new compContTree(),
                ['comp_id' => $compId, 'isDel' => 'no']) : null;
            $pController->setJson('contTree', $contTree);

            $pController->setVar('contid', $dataLoad['contId']);
            $pController->setVar('methodName', $dataLoad['methodName']);

            $pController->setVar('classFile', $dataLoad['classFile']);

            $methodList = [];
            if ( $dataLoad['classFile'] ){
                $classFileData = comp::getFileType($dataLoad['classFile']);
                // Получаем методы класа
                $className = comp::fullNameVarClass($classFileData, $dataLoad['ns']);
                $methodList = get_class_methods(new $className());
            } // if
            $pController->setJson('methodList', $methodList);


            $classTree = model::getVarClassTree($nsPath);
            $pController->setJson('classTree', $classTree);
        } // if ($dataLoad)

        // Дерево компонентов
        $compTree = dhtmlxTree::createTreeOfTable(new componentTree());
        $pController->setJson('compTree', $compTree);

        $file = 'block/vartype/comp.tpl.php';
        if ($pInclude) {
            $pController->view->setBlock($pInclude, $file);
        } else {
            $pController->view->setMainTpl($file);
        }
        // func. show
    }

    // Сохранение данные, если выбран типо переменной дерево
    public static function saveData($pController, integer $pAcId) {
        // Описание переменной
        $descrip = $pController->post('descrip');
        $compId = $pController->postInt('compId');
        $methodName = $pController->post('methodName');
        $classFile= $pController->post('classFile');
        $contId = $pController->postInt('contId');

        if ( !$compId ){
            throw new \Exception('Не выбран компонента');
        } // if

        if ( !$classFile ){
            throw new \Exception('Не выбран класс');
        } // if

        if ( !$methodName ){
            throw new \Exception('Не выбран метод');
        } // if

        $compData = comp::getClassDataByCompId($compId);

        $classFileData = comp::getFileType($classFile);
        // Правильно ли имя файла
        filevalid::isSafe($classFileData['file'], new \Exception('Неверное имя файла:' .$classFileData['file']));

        $nsPath = filesystem::nsToPath($compData['ns']);
        // Проверяем налачие файла
        $classFilePath = comp::getSiteVarClassPath($classFileData['isOut'], $nsPath);
        if ( !is_file($classFilePath.$classFileData['file']) ){
            throw new \Exception('File : ' . $classFileData['file'] . ' not found', 235);
        } // if

        $saveArr = [
            'acId' => $pAcId,
            'compId' => $compId,
            'descrip' => $descrip,
            'methodName' => $methodName,
            'classFile' => $classFile,
            'contId' => $contId
        ];

        (new varCompOrm())->saveExt(['acId' => $pAcId], $saveArr);

        $routeData = ['varType' => model::VAR_TYPE_COMP];
        (new urlTreePropVar())->saveExt(['acId' => $pAcId ], $routeData);
        // func. saveData
    }

    // class varComp
}