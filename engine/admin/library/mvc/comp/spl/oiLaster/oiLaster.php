<?php

namespace admin\library\mvc\comp\spl\oiLaster;

// Conf
use \DIR;
// Model
use admin\library\mvc\manager\complist\model as complistModel;
// Engine
use core\classes\render;
use core\classes\event as eventCore;
// ORM
use ORM\comp\spl\oiLaster\oiLaster as oiLasterOrm;
use ORM\comp\spl\oiLaster\oiLasterProp as oiLasterPropOrm;
use ORM\tree\compcontTree;
use ORM\tree\componentTree;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * Управление список последних objItem компонентов
 *
 * @author Козленко В.Л.
 */
class oiLaster extends \core\classes\component\abstr\admin\comp {

    public function __construct(string $pTplPath, string $pThemeResUrl) {
        parent::__construct($pTplPath, $pThemeResUrl);
    }

    public function init() {

    }

    /**
     * Рендеринг дерева контента и отображение всего GUI
     */
    public function indexAction() {
        $contId = $this->contId;
        $compcontTree = new compcontTree();
        // Выбераем дерево objItem
        $contData = $compcontTree->select('cc.*', 'cc')
            ->join(componentTree::TABLE.' c', 'c.id=cc.comp_id')
            ->where('c.sysname="objItem"')
            ->fetchAll();
        // Преобразуем масси в dhtmlTree
        $contTree = dhtmlxTree::all($contData, 0);
        self::setJson('contTree', $contTree);

        // Получаем Сохранённые данные, если они есть
        $oiLaster = (new oiLasterOrm)->selectList('*', 'selContId', 'contId='.$contId);
        self::setJson('oiLaster', $oiLaster);

        self::setVar('contId', $contId);

        // Получаем количество элементов для списка, которые было ранее сохранено
        $oiLasterProp = ( new oiLasterPropOrm() )->selectFirst('*', 'contId='.$contId);
        // Передаём все сохранённые переменные из настроек в шаблоны
        if ( $oiLasterProp){
            foreach( $oiLasterProp as $key => $val ){
                self::setVar($key, $val );
            }
        } // if

        // Получаем названия шаблона. Настраиваеться в настройках компонента
        $tplFile = self::getTplFile();
        $this->view->setBlock('panel', $tplFile);
        $this->view->setTplPath(DIR::getTplPath('manager'));
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
    public function saveDataAction(){
        $this->view->setRenderType(render::JSON);
        if (!self::isPost())
            return;
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
        $oiLasterOrm->delete('contId='.$contId);

        // Получаем спискок выбранных значений. Данные в формате: num,num,num
        $selData = self::post('sel');
        // Убераем последую запятую
        $selData = trim($selData, ',');
        if ( $selData ){
            // Получаем массив ID
            $selData = explode(',', $selData);
            // Для безопастности преобразуем их в числа
            $selData = array_map('intVal', $selData);
            // Делаем мульти вставку новых данных
            $oiLasterOrm->insertMulti(['selContId' => $selData]);
            // Для всех новых данных, выставляем contId
            $oiLasterOrm->update('contId='.$contId, 'contId=0');
        } // if selData

        // Сохраняем количество выбранных элементов, которое необходимо отоброжать в списке
        $saveData = [
            'itemsCount' => self::postInt('itemsCount'),
            'resizeType' => self::post('resizeType'),
            'previewWidth' => self::postInt('previewWidth'),
            'isAddMiniText' => self::postInt('isAddMiniText'),
            'isCreatePreview' => self::postInt('isCreatePreview')
        ];
        // Сохраняем данные
        ( new oiLasterPropOrm() )->saveExt(['contId' => $contId], $saveData);

        // func. saveDataAction
    }

    public function getTableData($pContId) {

    }

    public function getTableOrm() {

    }

    public function blockItemShowAction(){
        $this->view->setRenderType(render::NONE);
        echo 'Нет данных';
    }

    // class oiLaster
}
