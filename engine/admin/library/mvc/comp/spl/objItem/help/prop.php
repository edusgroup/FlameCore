<?php

namespace admin\library\mvc\comp\spl\objItem\help;

// Conf
use \DIR;
use \SITE;

// Engine
use core\classes\storage\storage;
use core\classes\render;
use core\classes\event as eventCore;
use core\classes\DB\tree;
use core\classes\admin\dirFunc;

// Plugin
use admin\library\mvc\plugin\fileManager\fileManager;

// ORM
use ORM\imgSizeList;
use ORM\tree\compContTree;
use ORM\comp\spl\objItem\objItemProp;
use ORM\comp\spl\objItem\article\article as articleOrm;

// Event
use admin\library\mvc\comp\spl\objItem\help\event\base\event as eventBase;


/**
 * Description of objItem
 * @see http://fancyapps.com/fancybox/
 * @author Козленко В.Л.
 */
trait prop {

    /**
     * Расширенные настройки для компонента
     */
    public function compPropAction() {
        $contId = $this->contId;
        self::setVar('contId', $this->contId);

        $sizeList = (new imgSizeList())->selectAll('name, val, type, id', 'contid=' . $this->contId) ? : [];
        self::setJson('sizeList', $sizeList);

        $url = (new objItemProp())->get('url', 'contId=' . $contId);
        self::setVar('url', $url);

        $tplPath = dirFunc::getAdminTplPathIn('comp').$this->nsPath;
        $this->view->setTplPath($tplPath);
        $this->view->setBlock('panel', 'prop/objItem.tpl.php');
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
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

    public function savePropDataAction() {
        $this->view->setRenderType(render::JSON);
        $url = self::get('url');
        $contId = $this->contId;

        $objItemProp = new objItemProp();
        if ($url) {
            $objItemProp->saveExt(['contId' => $contId], ['url' => $url]);
            $type = 'save';
        } else {
            $objItemProp->delete('contId=' . $contId);
            $type = 'del';
        }

        eventCore::callOffline(
            eventBase::NAME,
            eventBase::ACTOIN_CUSTOM_PROP_SAVE,
            $type,
            $contId
        );

        // savePropDataAction 
    }



    // trait prop
}