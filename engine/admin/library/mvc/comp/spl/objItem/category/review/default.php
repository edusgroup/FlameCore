<?php
namespace admin\library\mvc\comp\spl\objItem\category;

// Engine
use core\classes\render;
use core\classes\filesystem;
use core\classes\event as eventCore;

// Plugin
use admin\library\mvc\plugin\fileManager\fileManager;
use admin\library\mvc\plugin\fileManager\model as fileManagerModel;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;

// Event
use admin\library\mvc\comp\spl\objItem\category\article\event;

// Conf
use \DIR;

// ORM
use ORM\comp\spl\objItem\review\review as reviewOrm;
use ORM\comp\spl\objItem\objItem as objItemOrm;

// trait category ( itemObj->review )
trait category{
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
        $loadDir = objItemModel::getPath($compId, $contId, $itemObjId);
        $loadDir = DIR::getSiteDataPath($loadDir);
        if (is_readable($loadDir . 'text.txt')) {
            self::setVar('textDesc', file_get_contents($loadDir . 'text.txt'));
        } // if is_readable

        $tplFile = self::getTplFile();

        $this->view->setBlock('panel', $tplFile);
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. itemAction
    }

    public function blockItemShowAction() {
        $this->view->setRenderType(render::NONE);
        echo 'people::blockItemShowAction() | No settings in this';
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
            event::NAME,
            event::ACTION_SAVE,
            ['compId' => $compId, 'contId' => $contId],
            $itemObjId
        );

        (new reviewOrm())->saveExt(['itemObjId' => $itemObjId],
                                   ['caption' => $caption,
                                   'imgPrevUrl' => $prevImgUrl,
                                   'videoUrl' => $videoUrl]);

        // Директория с данными статьи
        $saveDir = objItemModel::getPath($compId, $contId, $itemObjId);
        $saveDir = DIR::getSiteDataPath($saveDir);

        $textDesc = self::post('textDesc');
        filesystem::saveFile($saveDir, 'text.txt', $textDesc);

        // func. saveDataAction
	}
// trait category ( itemObj->review )
}