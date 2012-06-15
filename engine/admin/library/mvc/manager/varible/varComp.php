<?php

namespace admin\library\mvc\manager\varible;

// Engine
use core\classes\DB\tree;
use core\classes\comp;
use core\classes\filesystem;
use core\classes\validation\filesystem as filesystemValid;
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

    // Сохранение данные, если выбран типо переменной дерево
    public static function saveData($pController, integer $pAcId) {
        // Описание переменной
        $descrip = $pController->post('descrip');
        $classType = $pController->post('classType');
        if ( !in_array($classType, ['user', 'core'])){
            throw new \Exception('Неверный тип class type: ' . $classType, 35);
        }
        // Тип хранилища: db или memcache
        $storageType = $pController->post('varStorage');
        if (!isset(model::$storageList[$storageType])) {
            throw new \Exception('Неверный тип storage type: ' . $storageType, 32);
        }

        $compId = $pController->postInt('compId');
        // Получаем данные по классуы
        $classData = comp::getClassDataByCompId($compId);

        $classNameFile = $pController->post('className');
        $methodName = $pController->post('methodName');

        filesystemValid::isSafe($classNameFile, new \Exception('Неверное имя: ' . $classNameFile, 34));
        // Получаем имя класса
        $className = filesystem::getName($classNameFile);
        //$className = '\core\comp\\' . $classData['ns'] . 'vars\\' . $storageType . '\\' . $className;
        $className = comp::getFullCompClassName($classType, $classData['ns'], 'vars\\'.$storageType, $className);
        // Получаем методы
        if (!method_exists($className, $methodName)) {
            throw new \Exception('Метод: ' . $methodName . ' не найден', 38);
        }
        
        $contId = $pController->postInt('contId');

        $saveArr = [
            'acId' => $pAcId,
            'compId' => $compId,
            'descrip' => $descrip,
            'classType' => $classType,
            'className' => $classNameFile,
            'methodName' => $methodName,
            'contId' => $contId
        ];

        $varCompOrm = new varCompOrm();
        $varCompOrm->save('acId=' . $pAcId, $saveArr);

        $urlTreePropVar = new urlTreePropVar();
        $routeData = [
            'storageType' => $storageType,
            'varType' => model::VAR_TYPE_COMP];
        $urlTreePropVar->update($routeData, 'acId=' . $pAcId);

        $pController->setVar('json', ['ok' => 1]);
        // func. saveData
    }

    public static function getFileClassList(integer $pCompId, string $pClassType, string $pStorageType) {
        // Получаем файл для переменной по компоненту
        $classData = comp::getClassDataByCompId($pCompId);
        $siteClassPath = DIR::CORE;
        if ( $pClassType == 'user' ){
            $siteClassPath = SITE_DIR::SITE_CORE;
        } // if
        $siteClassPath .= comp::getFullCompClassName($pClassType, $classData['ns'], 'vars\\'.$pStorageType, '');
        $siteClassPath = filesystem::nsToPath($siteClassPath);
        return filesystem::dir2array($siteClassPath);
        // func.getFileClassList
    }

    public static function fileClassToMethod(integer $pCompId, string $classType, string $pStorageType, string $pClassName) {
        // Получаем директорию с классами переменной по компоненту
        $classData = comp::getClassDataByCompId($pCompId);
        // Получаем имя класса
        $className = filesystem::getName($pClassName);
        $className = comp::getFullCompClassName($classType, $classData['ns'], 'vars\\'.$pStorageType, $className);
        // Получаем методы
        return get_class_methods(new $className());
        // func. fileClassToMethod
    }

    /**
     * Отображение Тип дерево в переменных
     * @param type $pController
     * @param integer $pActionId 
     */
    public static function show($pController, integer $pActionId, string $pStorageType, $pInclude = null) {
        $varCompOrm = new varCompOrm();
        // Получаем сохранёные данные
        $data = $varCompOrm->selectFirst('*', 'acId=' . $pActionId);
        // Есть ли сохранёные данные
        if ($data) {
            $compId = (int) $data['compId'];
            $classType = $data['classType'];
            // Описание
            $pController->setVar('descrip', $data['descrip']);
            // Компонент ID
            $pController->setVar('compid', $compId);

            $className = [];
            $className['list'] = self::getFileClassList($compId, $classType, $pStorageType);
            $className['val'] = $data['className'];
            $pController->setJson('className', $className);

            $methodList = [];
            $methodList['list'] = $className['list'] ? self::fileClassToMethod($compId, $classType, $pStorageType, $data['className']) : [];
            $methodList['val'] = $data['methodName'];
            $pController->setJson('methodName', $methodList);

            $pController->setJson('classType', $classType);

            $pController->setVar('contid', (int)$data['contId']);

            $contTree = complistModel::getOnlyContTreeByCompId($compId);
            $pController->setJson('contTree', $contTree);
        } // if ($data)
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

// class varComp
}