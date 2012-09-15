<?php

namespace admin\library\mvc\utils\seo;

// Conf
use \DIR;
use \SITE;

// Engine
use core\classes\render;
use core\classes\event as eventCore;

// ORM
use ORM\tree\routeTree;
use ORM\urlTreePropVar;
use ORM\utils\seo as seoOrm;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * Description of breadCrumbs
 *
 * @author Козленко В.Л.
 */
class seo extends \core\classes\component\abstr\admin\comp {

    public function init() {

    }

    public function indexAction() {
        self::setVar('contId', $this->contId);

        $tree = dhtmlxTree::createTreeOfTable(new routeTree());
        self::setJson('acTree', $tree);

        $this->view->setBlock('panel', 'seo/seo.tpl.php');
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function loadParamAction() {
        // Action ID ветки
        $actionId = self::getInt('itemid');
        $actionProp = (new urlTreePropVar())->selectFirst('isRedir, enable', 'acId=' . $actionId);
        if ($actionProp['isRedir'] || !$actionProp['enable']) {
            echo 'Недоступно: ';
            echo $actionProp['isRedir'] ? 'Редирект' : 'Недоступно';
            exit;
        }

        $complist = [];
        $methods = ['list' => []];

        $seoData = model::getLoadData($actionId);
        if ($seoData) {
            self::setVar('title', $seoData['title']);
            self::setVar('descr', $seoData['descr']);
            self::setVar('keywords', $seoData['keywords']);
            $complist['val'] = $seoData['blItemId'];
            self::setVar('linkNextUrl', $seoData['linkNextUrl']);
            self::setVar('linkNextTitle', $seoData['linkNextTitle']);

            $methods['list'] = model::getMethodListByBlockItemId((int)$seoData['blItemId']);
            $methods['val'] = $seoData['method'];

        } // if

        self::setVar('methods', $methods);

        $data = model::loadCompList($actionId);
        $complist['list'] = $data;
        self::setVar('complist', $complist);
        unset($data);

        $this->view->setMainTpl('seo/loadParam.tpl.php');
        // func. loadParamAction
    }

    public function saveDataAction() {
        // Указываем что, результат нужно отдать в формате JSON
        $this->view->setRenderType(render::JSON);
        // Action ID ветки
        $actionId = self::postInt('itemid');
        $title = self::post('title');
        $descr = self::post('descr');
        $keywords = self::post('keywords');
        $blCompId = self::post('blCompId');
        $linkNextUrl = self::post('linkNextUrl');
        $linkNextTitle = self::post('linkNextTitle');
        $method = self::post('method');

        eventCore::callOffline(event::NAME, event::ITEM_SAVE);

        (new routeTree())->update('isSave="yes"', 'id=' . $actionId);

        // Сохраняем выбранные данные
        (new seoOrm())->saveExt(
            ['acId' => $actionId],
            ['title' => $title,
            'descr' => $descr,
            'keywords' => $keywords,
            'blItemId' => $blCompId,
            'linkNextUrl' => $linkNextUrl,
            'method' => $method,
            'linkNextTitle' => $linkNextTitle]);
        // func. saveDataAction
    }

    public function loadClassMethodAction() {
        $this->view->setRenderType(render::JSON);
        $blockItemId = self::getInt('biCompId');
        $list = model::getMethodListByBlockItemId($blockItemId);
        self::setVar('json', $list);
        // func. loadClassMethod
    }

    // class breadCrumbs
}