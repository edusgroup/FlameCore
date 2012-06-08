<?

namespace admin\library\mvc\manager\varible;

// Engine
use core\classes\mvc\controllerAbstract;
use core\classes\render;
use core\classes\filesystem;
use core\classes\validation\filesystem as filesystemValid;
use core\classes\event as eventCore;
// ORM
use ORM\tree\routeTree;
use ORM\urlTreePropVar;
use ORM\tree\compContTree;
use ORM\tree\componentTree;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;
// Model
use admin\library\mvc\manager\complist\model as complistModel;
// Conf
use \DIR;

/**
 * Управление переменными у action. Установка типа переменной. Сохранение.
 * 
 * @author Козленко В.Л.
 */
class varible extends controllerAbstract {

    public function init() {
        
    }

    /**
     * Отображение настроек переменных
     */
    public function indexAction() {
        // ID action
        $actionId = self::getInt('acid', -1);
        self::setVar('acId', $actionId);

        $routeTree = new routeTree();
        $urlTreePropVar = new urlTreePropVar();
        // Получаем название переменной
        $actData = $urlTreePropVar->selectFirst(
                'varType, storageType'
                , 'acId=' . $actionId);

        $treeUrl = $routeTree->getTreeUrlById(routeTree::TABLE, $actionId);
        $varName = model::makeActionUrl($treeUrl);
        self::setVar('varName', $varName);

        $varList = model::getVarList($routeTree, $treeUrl);
        $varCount = count($varList);
        self::setVar('varCount', $varCount);

        // Получаем список названий, и, если было сохранение, выбранное значение
        $typeList = model::getTypeList();
        self::setVar('varType', [
                'list' => $typeList,
                'val' => $actData['varType']]);

        // Получаем источников хранения, и, если было сохранение, выбранное значение
        $storageList = model::getStorageList();
        self::setVar('varStorage', ['list' => $storageList,
                                   'val' => $actData['storageType']]);

        switch ($actData['varType']) {
            // Тип переменной дерево
            case model::VAR_TYPE_TREE:
                varTree::show($this, $actionId, 'typeBox', $varCount);
                break;
            // Тип переменной таблица
            case model::VAR_TYPE_COMP:
                varComp::show($this, $actionId, $actData['storageType'], 'typeBox');
                break;
        }

        $this->view->setBlock('panel', 'block/varible.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Сохранение данных по переменным
     */
    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);
        // action ID
        $acId = self::postInt('acid');
		(new routeTree())->update('isSave="yes"', 'id='.$acId);
		eventCore::callOffline(event::NAME, event::ITEM_SAVE);
		
        // Тип переменной
        $vartype = self::post('varType');
        switch ($vartype) {
            // Дерево
            case model::VAR_TYPE_TREE:
                varTree::saveData($this, $acId);
                break;
            // Таблица
            case model::VAR_TYPE_COMP:
                varComp::saveData($this, $acId);
                break;
        } // switch
        // func. saveDataAction
    }

    /**
     * Получение ветки контента
     */
    public function loadContTreeAction() {
        // TODO: Подумать, над тем что бы вынести все loadContTree в отдельный Controller
        $this->view->setRenderType(render::JSON);
        // ID компонента. см. табл. component_tree
        $compId = self::getInt('compid');
        // Получаем весь контент для компонента
        $json = complistModel::getOnlyContTreeByCompId($compId);
        self::setVar('json', $json);
        // func. loadContTreeAction
    }

    public function loadTypeVarAction() {
        $actionId = self::getInt('acid');
        $type = self::get('type');
        $storageType = self::get('storageType');
        if (!isset(model::$storageList[$storageType])) {
            throw new \Exception('Неверный тип storage type: ' . $storageType, 39);
        }
        
        $routeTree = new routeTree();
        $treeUrl = $routeTree->getTreeUrlById(routeTree::TABLE, $actionId);
        $varList = model::getVarList($routeTree, $treeUrl);
        $varCount = count($varList);
        
        switch ($type) {
            case model::VAR_TYPE_TREE:
                varTree::show($this, $actionId, '', $varCount);
                break;
            case model::VAR_TYPE_COMP:
                varComp::show($this, $actionId, $storageType);
                break;
            default :
                $this->view->setRenderType(render::NONE);
        } // switch
        // func. loadTypeVarAction
    }

    public function compLoadCompDataAction() {
        $this->view->setRenderType(render::JSON);
        // ID компонента. см. табл. component_tree
        $compId = self::getInt('compid');

        $classType = self::get('classType');
        if ( !in_array($classType, ['user', 'core'])){
            throw new \Exception('Неверный тип class type: ' . $classType, 35);
        }

        $storageType = self::get('varStorage');
        if (!isset(model::$storageList[$storageType])) {
            throw new \Exception('Неверный тип storage type: ' . $storageType, 33);
        }
        $fileList = varComp::getFileClassList($compId, $classType, $storageType);

        self::setVar('json', $fileList);
        // func. compLoadCompDataAction
    }

    public function compLoadMethodDataAction() {
        $this->view->setRenderType(render::JSON);
        // ID компонента. см. табл. component_tree
        $compId = self::getInt('compid');
        // Название хранилища
        $storageType = self::get('varStorage');
        if (!isset(model::$storageList[$storageType])) {
            throw new \Exception('Неверный тип storage type: ' . $storageType, 33);
        }
        $classType = self::get('classType');
        if ( !in_array($classType, ['user', 'core'])){
            throw new \Exception('Неверный тип class type: ' . $classType, 41);
        }

        // Имя выбранно файла классов
        $className = self::get('className');
        filesystemValid::isSafe($className, new \Exception('Неверное имя: ' . $className, 34));

        $methodList = varComp::fileClassToMethod($compId, $classType, $storageType, $className);

        self::setVar('json', $methodList);
        // func. compLoadMethodDataAction
    }

// class varible
}

?>