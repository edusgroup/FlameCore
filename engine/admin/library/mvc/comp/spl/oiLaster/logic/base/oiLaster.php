<?php

namespace admin\library\mvc\comp\spl\oiLaster\logic\base;

// Conf
use \DIR;

// Engine
use core\classes\render;
use core\classes\event as eventCore;
use core\classes\filesystem;
use core\classes\admin\dirFunc;
use core\classes\comp;

// ORM
use ORM\comp\spl\oiLaster\oiLaster as oiLasterOrm;
use ORM\comp\spl\oiLaster\oiLasterProp as oiLasterPropOrm;
use ORM\tree\compcontTree;
use ORM\tree\componentTree;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

// Event
use admin\library\mvc\comp\spl\oiLaster\event;

// Model
use admin\library\mvc\comp\spl\oiList\model;

/**
 * Управление список последних objItem компонентов
 *
 * @author Козленко В.Л.
 */
class oiLaster extends \core\classes\component\abstr\admin\comp {

    public function init() {

    }

    /**
     * Рендеринг дерева контента и отображение всего GUI
     */
    public function indexAction() {
        $contId = $this->contId;
        self::setVar('contId', $contId);

        // Получаем данные по компоненту objItem
        $objItemProp = (new componentTree())->selectFirst('*', 'sysname="objItem"');

        // Получаем весь список контента по oiLaster
        $contData = (new compcontTree())
            ->select('cc.*', 'cc')
            ->where('cc.isDel="no" AND cc.comp_id=' . $objItemProp['id'])
            ->fetchAll();
        // Преобразуем масси в dhtmlTree
        $contTree = dhtmlxTree::all($contData, 0);
        self::setJson('contTree', $contTree);

        // Получаем список id веток ранее выбранных и сохранённых
        $selItem = (new oiLasterOrm)->selectList('*', 'selContId', 'contId=' . $contId);
        self::setJson('selItem', $selItem);

        // Получаем количество элементов для списка, которые было ранее сохранено
        $oiLasterProp = (new oiLasterPropOrm())->selectFirst('*', 'contId=' . $contId);
        // Передаём все сохранённые переменные из настроек в шаблоны
        if ($oiLasterProp) {
            foreach ($oiLasterProp as $key => $val) {
                self::setVar($key, $val);
            } // foreach
        } // if

        // Получаем список разновидностей objItem
        $nsPath = filesystem::nsToPath($objItemProp['ns']);

        // Дерево классов для builder
        $classTree = model::getBuildClassTree($nsPath);
        self::setJson('classTree', $classTree);

        self::setVar('buildClassPathIn', comp::getBuildCompClassPath(false, $nsPath));
        self::setVar('buildClassPathOut', comp::getBuildCompClassPath(true, $nsPath));

        // Получаем названия шаблона. Настраиваеться в настройках компонента
        $this->view->setBlock('panel', $this->tplFile);
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Сохранение данных<br/>
     * POST запрос.<br/>
     * Входящие данные:<br/>
     * <b>sel</b> POST string Список выбранных ID из дерева контента. В формате num,num,num
     * <b>itemsCount</b> POST int Количество элементов в списке
     * @return void
     */
    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()){
            return;
        }

        $contId = $this->contId;

        // Установка события, что произошло сохранение
        // и надо пересоздать список
        eventCore::callOffline(
            event::NAME,
            event::ACTION_SAVE,
            '',
            $contId
        );

        $oiLasterOrm = new oiLasterOrm();
        // Удаляем все старые сохранения
        $oiLasterOrm->delete('contId=' . $contId);

        // Получаем спискок выбранных значений. Данные в формате: num,num,num
        $selData = self::post('sel');
        // Убераем последую запятую
        $selData = trim($selData, ',');
        if ($selData){
            // Получаем массив ID
            $selData = explode(',', $selData);
            // Для безопастности преобразуем их в числа
            $selData = array_map('intVal', $selData);
            // Делаем мульти вставку новых данных
            $oiLasterOrm->insertMulti(['selContId' => $selData], ['contId' => $contId]);
        } // if selData

        $classFile = self::post('class');
        if ( !$classFile ){
            return;
        } // if
        // Получаем данные по компоненту objItem
        $objItemProp = (new componentTree())->selectFirst('*', 'sysname="objItem"');
        // Получаем список разновидностей objItem
        $nsPath = filesystem::nsToPath($objItemProp['ns']);
        model::isClassFileExit($classFile, $nsPath);

        // Сохраняем количество выбранных элементов, которое необходимо отоброжать в списке
        $saveData = [
            'itemsCount' => self::postInt('itemsCount'),
            'resizeType' => self::post('resizeType'),
            'previewWidth' => self::postInt('previewWidth'),
            'isAddMiniText' => self::postInt('isAddMiniText'),
            'isCreatePreview' => self::postInt('isCreatePreview'),
            'classFile' => $classFile
        ];
        // Сохраняем данные
        (new oiLasterPropOrm())->saveExt(['contId' => $contId], $saveData);

        // func. saveDataAction
    }

    // class oiLaster
}
