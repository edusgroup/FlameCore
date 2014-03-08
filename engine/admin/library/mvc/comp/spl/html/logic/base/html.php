<?php

namespace admin\library\mvc\comp\spl\html\logic\base;

// Engine
use core\classes\render;
use core\classes\filesystem;
use core\classes\event as eventCore;
use core\classes\admin\dirFunc;

// Conf
use \DIR;
use \SITE;

/**
 * @author Козленко В.Л.
 */
class html extends \core\classes\component\abstr\admin\comp {

    public function init() {

    }

    public function indexAction() {
        $contId = $this->contId;
        $compId = $this->compId;

        self::setVar('contId', $contId, -1);
        self::setVar('compId', $compId, -1);

        $pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
        $loadDir = dirFunc::getSiteDataPath($pathPrefix);

        $htmlCodeData = filesystem::loadFileContent($loadDir . 'source.txt');
        self::setVar('htmlCode', $htmlCodeData);

        self::setVar('savedir', $loadDir);

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

        // Данные HTML
        $htmlCode = self::post('htmlCode');
        // Исходный файл
        if ( !filesystem::saveFile($saveDir, 'source.txt', $htmlCode) ){
            throw new \Exception('Error: Save source.txt fail');
        }
        // Нужно ли применять функцию
        $isOnlyText = self::postInt('isOnlyText');
        if ($isOnlyText) {
            $htmlCode = htmlspecialchars($htmlCode);
        }
        // Обработанный файл
        if ( !filesystem::saveFile($saveDir, 'html.txt', $htmlCode) ){
            throw new \Exception('Error: Save html.txt fail');
        }

        // Заголовок
        $caption = self::post('caption');

        // Данные для паблика, т.е. те данные которые будут запрашиваться для сайта
        // из-за этого их меньше
        $dataPublic = [
            'caption' => $caption
        ];
        $dataPublic = \serialize($dataPublic);
        if ( !filesystem::saveFile($saveDir, 'public.txt', $dataPublic) ){
            throw new \Exception('Error: Save public.txt fail');
        }

        // Данные для настроек, т.е. для админки, запоминаем что было введено
        $dataPrivate = [
            'caption' => $caption,
            'isOnlyText' => $isOnlyText
        ];
        $dataPrivate = \serialize($dataPrivate);
        if ( !filesystem::saveFile($saveDir, 'private.txt', $dataPrivate)){
            throw new \Exception('Error: Save private.txt fail');
        }
        // func. saveDataAction
    }

    // class html
}