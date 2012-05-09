<?php

namespace admin\library\mvc\manager\action;

// ORM
use ORM\tree\routeTree;
use ORM\tree\wareframeTree;
use ORM\urlTreePropVar;
use ORM\users\group as usersGroupOrm;
use ORM\action\relation as actGroupRelationOrm;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// Conf
use \DIR;

// Engine
use core\classes\validation\word;
use core\classes\render;
use core\classes\convert;
use core\classes\event as eventsys;
use core\classes\jquery;
use core\classes\html\element as htmlelem;
use core\classes\mvc\controllerAbstract;
use core\classes\filesystem;

// Site CONF
use site\conf\DIR as SITE_DIR;
use site\conf\SITE as SITE_CONF;

/**
 * Логика и настройка создания дерева каталогов для сайта
 *
 * @author Козленко В.Л.
 */
class action extends controllerAbstract {

    public function init() {

    }

    /**
     * Вывод дерева action и настройка веток. Метод по умолчанию.<br/>
     * <b>GET параметры:</b><br/>
     * acid - action Id (см. ORM actionTree), указания дереву action что мы хотим
     * подрузкить данные<br/>
     */
    public function indexAction() {
        $routeTree = new routeTree();

        $acId = self::getInt('acid', -1);
        //$routeTree->isExists($acId, new \Exception('Route acid='.$acId.' not found', 33));
        self::setVar('acId', $acId);

        // Формируем дерево action
        $actTree = model::getActTree($routeTree);
        self::setJSON('actTree', $actTree);

        // Формируем дерево wareframe 
        $wfTree = dhtmlxTree::createTreeOfTable(new wareframeTree());
        self::setJSON('wfTree', $wfTree);

        $groupTree = dhtmlxTree::createTreeOfTable(new usersGroupOrm());
        self::setJSON('groupTree', $groupTree);

        $this->view->setBlock('panel', 'block/action.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function addDirAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()) {
            return;
        }

        $treeId = self::postInt('treeid', 0);
        $name = self::post('name');
        // Тип настроек элемента URL_tree: 0-tpl 1-varible 2-comp method
        $propType = self::postInt('propType', 0);

        $routeTreeOrm = new routeTree();

        $varCount = $routeTreeOrm->getActVarCountById($treeId);

        $userData = [
            'propType' => $propType,
            'varCount' => $varCount
        ];
        $objJson = dhtmlxTree::add($routeTreeOrm, $name, $treeId, 0, $userData);
        $objJson['treeName'] = self::post('treeName');

        $urlTreePropVar = new urlTreePropVar();
        $urlTreePropVar->insert(array('acId' => $objJson['objId']));

        eventsys::callOffline(
            event::URLTREE, event::ITEM_CRATE, null, $objJson['objId']
        );

        switch ($propType) {
            // Переменная
            case 1:
                $charPref = '$';
                break;
            // Функция
            case 2:
                $charPref = '';
                break;
            default :
                $charPref = '';
        }
        $objJson['name'] = $charPref . $objJson['name'];
        self::setVar('json', $objJson);
        // func. addDirAction
    }

    /**
     * Добавяем в дерево action_tree файл
     */
    public function addFileAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost())
            return;

        $treeId = self::postInt('treeid', 0);
        $name = self::post('name');
        $propType = self::postInt('propType', 0);

        $userData = ['propType' => $propType];
        $objJson = dhtmlxTree::add(new routeTree(), $name, $treeId, 1, $userData);
        $objJson['treeName'] = self::post('treeName');

        $urlTreePropVar = new urlTreePropVar();
        $urlTreePropVar->insert(array('acId' => $objJson['objId']));

        eventsys::callOffline(
            event::URLTREE, event::ITEM_CRATE, null, $objJson['objId']
        );

        switch ($propType) {
            // Переменная
            case 1:
                $charPref = '$';
                break;
            // Функция
            case 2:
                $charPref = '';
                break;
            default :
                $charPref = '';
        }
        $objJson['name'] = $charPref . $objJson['name'];
        self::setVar('json', $objJson);
        // func. addFileAction
    }

    public function renameObjAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost())
            return;
        $id = self::postInt('id', -1);
        $name = self::post('name');
        $objJson = dhtmlxTree::rename(new routeTree(), $name, $id);
        $objJson['treeName'] = self::post('treeName');
        self::setVar('json', $objJson);
    }

    public function rmObjAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost())
            return;
        $id = self::postInt('id', -1);

        $routeTree = new routeTree();
        $routeTree->update('isDel=1', 'id=' . $id);

        eventsys::callOffline(
            event::URLTREE, event::ITEM_RM, null, $id
        );

        self::setVar('json', array(
                                  'id' => $id,
                                  'treeName' => self::post('treeName')));
        // func. rmObjAction
    }

    /*     * 3
     * Сохраняем данные по action
     * @throws \Exception 
     */

    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);

        // ID экшена
        $acId = self::getInt('acid');

        eventsys::callOffline(
            event::URLTREE, event::PROP_SAVE, null, $acId
        );

        $userReg = self::postInt('reguser');

        $data = array();
        // Доступе ли экшен
        $data['enable'] = self::postInt('enable', 0);
        // Надо ли делать редирект
        $data['isRedir'] = self::postInt('isRedir', 0);
        // Адрес редиректа
        $data['redirect'] = self::post('redirect');
        // Выбранный контроллер
        $data['controller'] = self::post('contrList');
        // Выбранный метод контроллера
        //$data['method'] = self::post('methodList');

        $data['wf_id'] = self::postInt('wfVal', -1);
        $data['acId'] = $acId;
        $data['userReg'] = $userReg;

        $controller = $data['controller'];

        // TODO: Кажется есть уязвимость в расширении файла. Проверить
        // Добавить обычный валидатор имени
        $controller = trim(filesystem::getName($controller));
        if ($controller && !word::isLatin($controller)) {
            throw new \Exception('File not valid', 26);
        }

        model::saveRouteData($data, $acId);

        $robotsVal = self::post('robots', 'none');

        $routeTree = new routeTree();
        $routeTree->update(array(
                                'isSave' => 'yes',
                                'robots' => $robotsVal)
            , 'id=' . $acId);

        // Соотношение пользователя
        $actGroupRelationOrm = new actGroupRelationOrm();
        $actGroupRelationOrm->delete('actionId=' . $acId);

        $group = self::post('group');
        if ($group) {
            $group = explode(',', $group);
            array_map(function($pGroupId) use($acId, $actGroupRelationOrm) {
                $actGroupRelationOrm->insert(array(
                                                  'actionId' => $acId,
                                                  'groupId' => (int)$pGroupId
                                             ));
            }, $group);
        } // if

        $json = array('ok' => 'ok');
        self::setVar('json', $json);
        // func. saveDataAction
    }

    /**
     * Загрузка настраиваемых параметров для action<br/>
     * GET параметры:<br/>
     * id - ID action см ORM actionTree<br/>
     * Возвращает HTML код
     */
    public function loadPropAction() {
        $id = self::getInt('id');
        list($routeData, $loadData) = model::getRouteData($id);
        switch ($routeData) {
            case '2':
                self::loadPropFunc($loadData);
                break;
            default:
                self::loadPropTplVar($loadData, $routeData, $id);
        }
        // func. loadPropAction
    }

    public function loadPropFunc($pLoadData) {
        $this->view->setRenderType(render::NONE);
        print 'Не готово';

        // func. loadPropFunc
    }

    public function loadPropTplVar($pLoadData, $routeData, $pAcId) {
        // Формируем доступных список контроллеров для переопределиния на сайте
        $contrPath = SITE_DIR::SITE_CORE . 'core/logic/';
        $contrList = array();
        $contrList['list'] = filesystem::dir2array($contrPath);
        array_unshift($contrList['list'], 'Выбрите файл');
        $contrList['list'] = htmlelem::dirList2Select($contrList['list']);
        $contrList['list'][0]['id'] = '';
        self::setVar('contrList', $contrList);

        if ($pLoadData) {
            self::setVar('varName', $pLoadData['varName']);
            self::setVar('isRedir', $pLoadData['isRedir']);
            self::setVar('enable', $pLoadData['enable']);
            self::setVar('redirect', $pLoadData['redirect']);
            self::setVar('userReg', $pLoadData['userReg']);

            self::setVar('wfId', $pLoadData['wf_id']);
        } // if

        $actGroupRelationOrm = new actGroupRelationOrm();
        $usGroupData = $actGroupRelationOrm->selectList('groupId', 'groupId', 'actionId=' . $pAcId);
        self::setJson('usGroupData', $usGroupData);

        self::setVar('propType', $routeData['propType']);
        self::setVar('varCount', $pLoadData['varCount']);
        $robotsRuleList = array(
            'list' => array(
                'none' => 'None',
                'disallow' => 'Disallow',
                'allow' => 'Allow'
            ),
            'val' => $routeData['robots']
        );
        self::setVar('robotsRuleList', $robotsRuleList);
        self::setVar('controller', $pLoadData['controller']);

        $this->view->setMainTpl('block/action/loadProp.tpl.php');

        // func. loadPropTplVar
    }

    // class action
}

?>