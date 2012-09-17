<?php
namespace admin\library\mvc\comp\spl\objItem\help;

// Conf
use \DIR;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\grid as dhtmlxGrid;

// Engine
use core\classes\render;
use core\classes\event as eventCore;
use core\classes\admin\dirFunc;

// ORM
use ORM\comp\spl\objItem\objItem as objItemOrm;


/**
 * Description of article
 *
 * @author Козленко В.Л.
 */
trait table {

    /**
     * Отображение таблицы с объектами.<br/>
     * Управление названием, публикацией, seo названием
     */
    public function indexAction(){
        $contId = $this->contId;
        self::setVar('contId', $contId);

        $data = model\table\model::getList($contId);
        $listXML = dhtmlxGrid::createXMLOfArray($data, null, null);
        $listXML = addslashes($listXML);

        self::setVar('listXML', $listXML, false);

        $this->view->setBlock('panel', '../help/table.tpl.php');
        $this->view->setTplPath(dirFunc::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Сохранение заголовка, системного имени и публикации.
     * т.е. сохранение в общей таблице статей
     */
    public function saveTableItemDataAction() {
        $this->view->setRenderType(render::JSON);
        $contId = $this->contId;

        $data = self::post('data');

        $listId = model\table\model::saveTableItemData($data, $contId);
        $json = ['newId' => $listId];
        self::setVar('json', $json);
        // func. saveTableItemDataAction
    }

    /**
     * Выставление флага на удаление объекта objItem
     */
    public function rmTableItemAction() {
        $this->view->setRenderType(render::JSON);
        $rowsId = self::post('rowsId');
        $compId = $this->compId;
        $contId = $this->contId;

        $userData = explode(',', $rowsId);
        $userData = array_map('intVal', $userData);

        eventCore::callOffline(
            event::NAME,
            event::ACTION_DELETE,
            ['itemsId' => $userData,
            'compId' => $compId],
            $contId
        );

        $objItemOrm = new objItemOrm();
        $where = implode(',', $userData);
        $objItemOrm->update('isDel=1', 'id in (' . $where . ')');
        self::setVar('json', [0 => 'ok', 'list' => $userData]);
        // func. rmTableItemAction
    }

    /**
     * Возврашает список табличных данных, пренадлежащех категории $pContId
     * Может быть пустым. Нужно только если onlyFolder=1
     * @param integer $pContId ID родителя(категории)
     */
    public function getTableData($pContId) {
        $objItemOrm = new objItemOrm();
        return $objItemOrm->select('id, caption')
            ->where('treeId=' . $pContId . ' AND isPublic="yes" AND isDel=0')
            ->comment(__METHOD__)
            ->fetchAll();
        // func. getTableData
    }

    /**
     * Возврашает имя записи в таблице
     * Может быть пустым. Нужно только если onlyFolder=1
     * @param integer $pTableId ID таблицы
     */
    public function getTableOrm() {
        return new objItemOrm();
        // func. getTableOrm
    }

    // trait tsetad
}