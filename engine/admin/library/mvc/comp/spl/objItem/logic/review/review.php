<?php

namespace admin\library\mvc\comp\spl\objItem\logic\review;

// Engine
use core\classes\render;
use core\classes\filesystem;
use core\classes\event as eventCore;
use core\classes\admin\dirFunc;

// Plugin
use admin\library\mvc\plugin\fileManager\fileManager;

// Conf
use \DIR;

// ORM
use ORM\comp\spl\objItem\review\review as reviewOrm;
use ORM\comp\spl\objItem\objItem as objItemOrm;

// Model
use admin\library\mvc\comp\spl\objItem\help\model\base\model as objItemModel;
use admin\library\mvc\comp\spl\objItem\help\model\base\model as baseModel;

// Event
use admin\library\mvc\comp\spl\objItem\help\event\base\event as eventBase;
use admin\library\mvc\comp\spl\objItem\help\event\article\event as eventArticle;

/**
 * Description of review
 *
 * @author Козленко В.Л.
 */
class review extends \core\classes\component\abstr\admin\comp implements \core\classes\component\abstr\admin\table{
    use \admin\library\mvc\comp\spl\objItem\help\table;
    use \admin\library\mvc\comp\spl\objItem\help\file;
    use \admin\library\mvc\comp\spl\objItem\help\prop;

    public function init(){
        // func. init
    }

    public function itemAction() {
        $contId = $this->contId;
        self::setVar('contId', $contId);
        $compId = $this->compId;

        $itemObjId = self::getInt('id');
        self::setVar('objItemId', $itemObjId, -1);

        // Получаем параметры статьи и ранее сохранёные настройки (если они есть)
        $objItemData = (new objItemOrm())
            ->select('a.*, i.*', 'i')
            ->joinLeftOuter(reviewOrm::TABLE.' a', 'a.itemObjId=i.id')
            ->where('i.id=' . $itemObjId)
            ->fetchFirst();
        foreach( $objItemData as $key=>$val){
            self::setVar($key, $val);
        }

        // Получаем путь до папки, где храняться данные превью
        $loadDir = baseModel::getPath($compId, $contId, $itemObjId);
        $loadDir = dirFunc::getSiteDataPath($loadDir);
        if (is_readable($loadDir . 'text.txt')) {
            self::setVar('textDesc', file_get_contents($loadDir . 'text.txt'));
        } // if is_readable

        $this->view->setBlock('panel', $this->tplFile);
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. itemAction
    }

    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);

        $contId = $this->contId;
        $compId = $this->compId;

        $itemObjId= self::postInt('itemObjId');

        $caption = self::post('caption');
        $prevImgUrl = self::post('prevImgUrl');
        $videoUrl = self::post('videoUrl');

        eventCore::callOffline(
            eventBase::NAME,
            eventArticle::ACTION_SAVE,
            ['compId' => $compId, 'contId' => $contId],
            $itemObjId
        );

        (new reviewOrm())->saveExt(['itemObjId' => $itemObjId],
                                   ['caption' => $caption,
                                   'imgPrevUrl' => $prevImgUrl,
                                   'videoUrl' => $videoUrl]);

        // Директория с данными статьи
        $saveDir = baseModel::getPath($compId, $contId, $itemObjId);
        $saveDir = dirFunc::getSiteDataPath($saveDir);

        $textDesc = self::post('textDesc');
        filesystem::saveFile($saveDir, 'text.txt', $textDesc);

        // func. saveDataAction
    }

    public function blockItemShowAction() {
        $this->view->setRenderType(render::NONE);
        echo 'article::blockItemShowAction() | No settings in this';
        // func. blockItemShowAction
    }

    // class review
}