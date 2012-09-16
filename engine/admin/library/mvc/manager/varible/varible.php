<?

namespace admin\library\mvc\manager\varible;

// Engine
use core\classes\mvc\controllerAbstract;
use core\classes\render;
use core\classes\filesystem;
use core\classes\event as eventCore;
use core\classes\comp;
use core\classes\validation\filesystem as filevalid;

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
        // Получаем название переменной
        $actData = (new urlTreePropVar())->selectFirst('varType', 'acId=' . $actionId);

        $treeUrl = $routeTree->getTreeUrlById(routeTree::TABLE, $actionId);
        $varName = model::makeActionUrl($treeUrl);
        self::setVar('varName', $varName);

        $varList = model::getVarList($routeTree, $actionId);
        $varCount = count($varList);
        self::setVar('varCount', $varCount);

        // Получаем список типов веток, и если было сохранение, выбранное значение
        // tree и comp
        $typeList = model::getTypeList();
        self::setVar('varType',
                     ['list' => $typeList,
                      'val' => $actData['varType']]);

        // Тип уже выбранного значаения, может быть пустым
        switch ($actData['varType']) {
            // Тип переменной дерево
            case model::VAR_TYPE_TREE:
                varTree::show($this, $actionId, 'typeBox', $varCount);
                break;
            // Тип переменной таблица
            case model::VAR_TYPE_COMP:
                varComp::show($this, $actionId, 'typeBox');
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
        // Говорит системе, что, хорошо бы эту страницу пересоздать
        (new routeTree())->update('isSave="yes"', 'id=' . $acId);
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

    public function compLoadMethodsAction(){
        $this->view->setRenderType(render::JSON);

        $compId = self::getInt('compId');
        $compData = comp::getClassDataByCompId($compId);

        // Название класса
        $classFile = self::get('classFile');
        if ( !$classFile ){
            return;
        } // if
        $classFileData = comp::getFileType($classFile);
        // Правильно ли имя файла
        filevalid::isSafe($classFileData['file'], new \Exception('Неверное имя файла:' .$classFileData['file']));

        $nsPath = filesystem::nsToPath($compData['ns']);
        // Проверяем налачие файла
        $classFilePath = comp::getSiteVarClassPath($classFileData['isOut'], $nsPath);
        if ( !is_file($classFilePath.$classFileData['file']) ){
            throw new \Exception('File : ' . $classFileData['file'] . ' not found', 235);
        } // if

        // Получаем методы класа
        $className = comp::fullNameVarClass($classFileData, $compData['ns']);
        $methodList = get_class_methods(new $className());

        self::setVar('json', $methodList);
        // func. compLoadMethodsAction
    }

    public function compLoadCompDataAction(){
        $this->view->setRenderType(render::JSON);

        $compId = self::getInt('compid');
        $compData = comp::getClassDataByCompId($compId);

        $nsPath = filesystem::nsToPath($compData['ns']);

        $classTree = model::getVarClassTree($nsPath);

        $contTree = dhtmlxTree::createTreeOfTable(
            new compContTree(),
            ['comp_id' => $compId, 'isDel'=>'no']);

        self::setVar('json', ['classTree'=>$classTree, 'contTree' => $contTree]);
        // func. compLoadCompDataAction
    }

    public function loadTypeVarAction() {
        $actionId = self::getInt('acid');
        $type = self::get('type');

        $varList = model::getVarList((new routeTree()), $actionId);
        $varCount = count($varList);

        switch ($type) {
            case model::VAR_TYPE_TREE:
                varTree::show($this, $actionId, '', $varCount);
                break;
            case model::VAR_TYPE_COMP:
                varComp::show($this, $actionId, '');
                break;
            default :
                $this->view->setRenderType(render::NONE);
        } // switch
        // func. loadTypeVarAction
    }

    // class varible
}