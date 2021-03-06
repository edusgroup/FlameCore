<?php

namespace admin\library\mvc\comp\spl\menu\logic\base;

// Engine
use core\classes\render;
use core\classes\filesystem;
use core\classes\upload;
use core\classes\event as eventCore;
use core\classes\admin\dirFunc;

// Conf
use \DIR;
use \SITE;

// ORM
use ORM\tree\comp\menu as menuOrm;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;
use admin\library\mvc\plugin\fileManager\fileManager;

use admin\library\mvc\comp\spl\menu\event;


class menu extends \core\classes\component\abstr\admin\comp {

    public function init() {

    }

    public function indexAction() {
        $contId = $this->contId;
        $compId = $this->compId;

        $pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
        $loadDir = dirFunc::getSiteDataPath($pathPrefix);

        $compTree = dhtmlxTree::createTreeOfTable(new menuOrm(), 'contId=' . $contId);
        self::setJson('menuTree', $compTree);

        self::setVar('contId', $contId, -1);
        self::setVar('compId', $compId, -1);

        $saveData = filesystem::loadFileContentUnSerialize($loadDir . 'private.txt');
        if ($saveData) {
            foreach ($saveData as $key => $item) {
                self::setVar($key, $item);
            } // foreach
        } // if $saveData

        $this->view->setBlock('panel', $this->tplFile);
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function dirAddAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()) {
            return;
        } // if
        $contId = $this->contId;
        $treeId = self::postInt('treeid', 0);
        $name = self::post('name');
        $userData = ['contId' => $contId];
        $objJson = dhtmlxTree::add(new menuOrm(), $name, $treeId, dhtmlxTree::FOLDER, $userData);
        $objJson['treeName'] = self::post('treeName');
        self::setVar('json', $objJson);
        // func. dirAddAction
    }

    public function renameObjAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()){
            return;
        } // if
        $id = self::postInt('id', -1);
        $name = self::post('name');
        $objJson = dhtmlxTree::rename(new menuOrm(), $name, $id);
        $objJson['treeName'] = self::post('treeName');
        self::setVar('json', $objJson);
        // func. renameObjAction
    }

    /**
     * Удалиние ветки в дереве страниц
     * @return void
     */
    public function rmObjAction() {
        $this->view->setRenderType(render::JSON);
        if (!self::isPost()){
            return;
        } // if
        $id = self::postInt('id', -1);
        dhtmlxTree::remove(new menuOrm(), $id);

        $treeName = self::post('treeName');
        self::setVar('json', ['id' => $id, 'treeName' => $treeName]);
        // func. rmObjAction
    }

    /**
     * Сохранение данных компонента
     */
    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);

        $contId = $this->contId;
        $compId = $this->compId;

        // Папка, куда будем сохранять данные
        $pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
        $saveDir = dirFunc::getSiteDataPath($pathPrefix);

        // ID элемента дерева меню, чьи данные обрабатываем
        $menuId = self::postInt('menuid');
        $sortValue = self::postInt('sortValue');

        eventCore::callOffline(
            event::NAME,
            event::ACTION_SAVE,
            array('compId' => $compId),
            $contId
        );

        //$fileImg = trim(self::post('fileImg'));
        // TODO: Вставить проверку на существование

        // Ссылка меню
        $menuLink = self::postSafe('link');
        $menuClass = self::postSafe('class');
        // Нужно ли ставить аттребут nofollow
        $menuNoFollow = (boolean)self::postInt('nofollow');
        $data = [
            'link' => $menuLink,
            'nofollow' => $menuNoFollow,
            //'file' => $fileImg,
            'contId' => $contId,
            'class' => $menuClass,
            'sortValue' => $sortValue
        ];

        // Сохраняем данные по настройкам меню
        $menuOrm = new menuOrm();
        $menuOrm->save('id=' . $menuId, $data);

        // Заголовок
        $caption = self::post('caption');

        // Данные для паблика, т.е. те данные которые будут запрашиваться для сайта
        // из-за этого их меньше
        $dataPublic = ['caption' => $caption];
        $dataPublic = \serialize($dataPublic);
        filesystem::saveFile($saveDir, 'public.txt', $dataPublic);

        // Данные для настроек, т.е. для админки, запоминаем что было введено
        $dataPrivate = ['caption' => $caption];
        $dataPrivate = \serialize($dataPrivate);
        filesystem::saveFile($saveDir, 'private.txt', $dataPrivate);

        self::setVar('json', 'ok');
        // func. saveDataAction
    }

    public function loadMenuDataAction() {
        $menuId = self::getInt('menuid');

        $menuOrm = new menuOrm();
        $data = $menuOrm->selectFirst('link, nofollow, class, sortValue', 'id=' . $menuId);
        self::setVar('link', $data['link']);
        self::setVar('nofollow', $data['nofollow']);
        self::setVar('class', $data['class']);
        self::setVar('sortValue', $data['sortValue']);
        $this->view->setMainTpl('../help/menudata.tpl.php');
        // func. loadMenuDataAction
    }

    // class menu
}