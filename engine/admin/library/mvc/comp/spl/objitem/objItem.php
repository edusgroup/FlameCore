<?php

namespace admin\library\mvc\comp\spl\objItem;

// Conf
use \DIR;
use \SITE;

// Engine
use core\classes\storage\storage;
use core\classes\render;
use core\classes\event as eventCore;
use core\classes\filesystem;
use core\classes\word;
use core\classes\upload;
use core\classes\image\resize;
use core\classes\DB\tree;
use core\classes\image\imageProp;
use core\classes\validation\filesystem as fileValid;

// Plugin
use admin\library\mvc\plugin\fileManager\fileManager;
use admin\library\mvc\plugin\fileManager\model as fileManagerModel;
use admin\library\mvc\plugin\dhtmlx\model\grid as dhtmlxGrid;

// ORM
use ORM\imgSizeList;
use ORM\tree\compContTree;
use ORM\contFile;
use ORM\comp\spl\objItem\objItem as objItemOrm;
use ORM\comp\spl\objItem\objItemProp;

/**
 * Description of objItem
 * @see http://fancyapps.com/fancybox/
 * @author Козленко В.Л.
 */
class objItem extends \core\classes\component\abstr\admin\comp {
    use category\category;

    public function __construct(string $pTplPath, string $pThemeResUrl) {
        parent::__construct($pTplPath, $pThemeResUrl);
    }

    public function init() {

    }

    public function indexAction() {
        $contId = $this->contId;
        self::setVar('contId', $contId);

        $data = model::getList($contId);
        $listXML = dhtmlxGrid::createXMLOfArray($data, null, null);
        $listXML = addslashes($listXML);

        self::setVar('listXML', $listXML, false);

        $this->view->setBlock('panel', 'table.tpl.php');
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Расширенные настройки для компонента
     */
    public function compPropAction() {
        $contId = $this->contId;
        self::setVar('contId', $this->contId);

        $sizeList = (new imgSizeList())->selectAll('name, val, type, id', 'contid=' . $this->contId) ? : [];
        self::setJson('sizeList', $sizeList);

        $objItemProp = new objItemProp();
        $url = $objItemProp->get('url', 'contId=' . $contId);
        self::setVar('url', $url);

        $this->view->setBlock('panel', 'prop/objItem.tpl.php');

        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. compPropAction
    }

    /**
     * Удаляем размер изображения из настроек<br/>
     * GET параметры:<br/>
     * itemid - ID удаляемого изображения. ORM imgSizeList
     */
    public function delItemAction() {
        $this->view->setRenderType(render::JSON);
        $itemId = self::getInt('itemid');

        $imgSizeList = new imgSizeList();
        $imgSizeList->delete('id=' . $itemId . ' AND is_use=0');
        $affectedRows = $imgSizeList->affectedRows();
        if ($affectedRows == 0) {
            $error = [];
            $error['error'] = ['msg' => 'Размер используется', 'code' => 32];
            self::setVar('json', $error);
            return;
        }

        self::setVar('json', ['itemid' => $itemId]);
        // func. delItemAction
    }

    /**
     * Добавление нового размера изображения. Расширенные настройки<br/>
     * GET параметры:<br/>
     * type - тип данных: может быть width или height<br/>
     * val - значение типа<br/>
     * name - название размера изображения<br/>
     * @throws \Exception
     */
    public function addSizeAction() {
        $this->view->setRenderType(render::JSON);
        // Тип данных: width или height
        $type = self::get('type');
        if ($type != 'width' && $type != 'height') {
            throw new \Exception('Неверный тип данных: ' . $type, 23);
        }
        // Значение $type
        $val = self::getInt('val');
        if (!$val) {
            throw new \Exception('Не заданно значение $val', 24);
        }

        $size = [];
        // Название размера
        $size['name'] = self::get('name');
        $size['contid'] = $this->contId;
        $size['val'] = $val;
        $size['type'] = $type;

        $imgSizeList = new imgSizeList();
        $imgSizeList->insert($size);
        $size['id'] = $imgSizeList->insertId();

        self::setVar('json', $size);
        // func. addSizeAction
    }

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
        //$list = dhtmlxGrid::rmRows($rowsId, new objItemOrm());
        self::setVar('json', [0 => 'ok', 'list' => $userData]);
        // func. rmTableItemAction
    }

    /**
     * Сохранение заголовка, системного имени и публикации.
     * т.е. сохранение в общей таблице статей
     */
    public function saveTableItemDataAction() {
        $this->view->setRenderType(render::JSON);
        $contId = $this->contId;
        //$compId = $this->compId;

        $data = self::post('data');

        $listId = model::saveTableItemData($data, $contId);
        $json = ['newId' => $listId];
        self::setVar('json', $json);
        // func. saveTableItemDataAction
    }

    public function getTableOrm() {
        //$objItemOrm = new objItemOrm();
        //return $objItemOrm->get('caption','id='.$pTableId);
        return new objItemOrm();
        // func. getTableItemName
    }

    /**
     * Возврашает список статей пренадлежащей категории
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

    public function savePropDataAction() {
        $this->view->setRenderType(render::JSON);
        $url = self::get('url');
        $contId = $this->contId;

        $objItemProp = new objItemProp();
        if ($url) {
            $objItemProp->saveExt(
                ['contId' => $contId],
                ['url' => $url]);
            $type = 'save';
        } else {
            $objItemProp->delete('contId=' . $contId);
            $type = 'del';
            //$urlList = (new tree())->getTreeUrlById(compContTree::TABLE, $contId);
            //var_dump($urlList);
        }

        eventCore::callOffline(
            event::NAME,
            event::ACTOIN_CUSTOM_PROP_SAVE,
            $type,
            $contId
        );

        // savePropDataAction 
    }

    public function blockItemShowAction() {
        $this->view->setRenderType(render::NONE);
        echo 'Нет данных';
    }

    // class objItem
}

?>