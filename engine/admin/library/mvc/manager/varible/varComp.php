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

class varComp {

    // Сохранение данные, если выбран типо переменной дерево
    public static function saveData($pController, integer $pAcId) {
        // Описание переменной
        $descrip = $pController->post('descrip');

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
        $className = 'site\core\comp\\' . $classData['ns'] . 'vars\\' . $storageType . '\\' . $className;
        // Получаем методы
        if (!method_exists($className, $methodName)) {
            throw new \Exception('Метод: ' . $methodName . ' не найден', 38);
        }
        
        $contId = $pController->postInt('contId');

        $saveArr = array(
            'acId' => $pAcId,
            'compId' => $compId,
            'descrip' => $descrip,
            'className' => $classNameFile,
            'methodName' => $methodName,
            'contId' => $contId
        );

        $varCompOrm = new varCompOrm();
        $varCompOrm->save('acId=' . $pAcId, $saveArr);

        $urlTreePropVar = new urlTreePropVar();
        $routeData = array(
            'storageType' => $storageType,
            'varType' => model::VAR_TYPE_COMP);
        $urlTreePropVar->update($routeData, 'acId=' . $pAcId);

        $pController->setVar('json', array('ok' => 1));
        // func. saveData
    }

    public static function getFileClassList(integer $pCompId, string $pStorageType) {
        // Получаем файл для переменной по компоненту
        $classData = comp::getClassDataByCompId($pCompId);
        $classNamePath = filesystem::nsToPath($classData['ns']);
        $path = DIR::getVarClassPath($classNamePath, $pStorageType);
        return filesystem::dir2array($path);
        // func.getFileClassList
    }

    public static function fileClassToMethod(integer $pCompId, string $pStorageType, string $pClassName) {
        // Получаем директорию с классами переменной по компоненту
        $classData = comp::getClassDataByCompId($pCompId);
        // Получаем имя класса
        $className = filesystem::getName($pClassName);
        $className = 'site\core\comp\\' . $classData['ns'] . 'vars\\' . $pStorageType . '\\' . $className;
        
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
            // Описание
            $pController->setVar('descrip', $data['descrip']);
            // Компонент ID
            $pController->setVar('compid', $compId);

            $className = array();
            $className['list'] = self::getFileClassList($compId, $pStorageType);
            $className['val'] = $data['className'];
            $pController->setJson('className', $className);
            
            $methodList = array();
            $methodList['list'] = self::fileClassToMethod($compId, $pStorageType, $data['className']);
            $methodList['val'] = $data['methodName'];
            $pController->setJson('methodName', $methodList);
            
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

?>
