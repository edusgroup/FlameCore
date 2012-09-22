<?php

namespace admin\library\mvc\comp\spl\objItem\logic\gallery;

// Engine
use core\classes\storage\storage;
use core\classes\render;
use core\classes\event as eventCore;
use core\classes\filesystem;
use core\classes\word;
use core\classes\DB\tree;
use core\classes\admin\dirFunc;
use core\classes\upload;
use core\classes\image\imageProp;
use core\classes\image\resize;

// ORM
use ORM\comp\spl\objItem\objItem as objItemOrm;
use ORM\comp\spl\objItem\article\article as articleOrm;
use ORM\imgSizeList;

// Model
use admin\library\mvc\comp\spl\objItem\help\model\base\model as objItemModel;
use admin\library\mvc\comp\spl\objItem\help\model\base\model as baseModel;
use  admin\library\mvc\comp\spl\objItem\help\model\gallery\model as modelGallery;

// Plugin
use admin\library\mvc\plugin\fileManager\fileManager;
use admin\library\mvc\plugin\fileManager\model as fileManagerModel;

// Event
use admin\library\mvc\comp\spl\objItem\help\event\gallery\event as eventGallery;
use admin\library\mvc\comp\spl\objItem\help\event\base\event as eventBase;
use admin\library\mvc\comp\spl\objItem\help\event\article\event as eventArticle;

/**
 * Description of article
 *
 * @author Козленко В.Л.
 */

class gallery extends \core\classes\component\abstr\admin\comp implements \core\classes\component\abstr\admin\table{
    use \admin\library\mvc\comp\spl\objItem\help\table;
    use \admin\library\mvc\comp\spl\objItem\help\file;

    public function init(){

    }

    public function itemAction() {
        $contId = $this->contId;
        $compId = $this->compId;
        $objItemId = self::getInt('id');

        self::setVar('contId', $contId, -1);
        self::setVar('compId', $compId, -1);
        self::setVar('objItemId', $objItemId);

        $pathPostfix = 'comp/' . $compId . '/' . $contId . '/'.$objItemId.'/';

        // Получаем директорию куда будет заливать файл
        $fileDistPath = dirFunc::getSiteUploadPathData() . $pathPostfix;
        $filePublicUrl = dirFunc::getSiteUploadUrlData() . $pathPostfix;
        $filePreviewUrl = dirFunc::getPreviewImgUrl($pathPostfix);

        // Список размеров изображений, заданных при настройки в ветке компонента
        $sizeList = objItemModel::getSizeList($contId);
        // Создаём из простого масса, список годный для <select>
        $sizeList['list'] = objItemModel::makeSelect($sizeList);

        self::setVar('contrName', $contId);
        self::setVar('callType', 'comp');

        // Файл с ранее сохранёными данными, если было ранее сохранение
        $contDir = $pathPostfix . 'data.txt';
        $contFileData = dirFunc::getSiteDataPath($contDir);
        // Если файла нет, то скорей всего сохранения не было
        if (is_file($contFileData)) {
            $data = file_get_contents($contFileData);
            $data = unserialize($data);
            self::setJson('fileData', $data);
        } // if

        (new fileManager())->showFile($this, $fileDistPath, $filePreviewUrl, $filePublicUrl, $sizeList);

        $this->view->setBlock('panel', $this->tplFile);

        $this->view->setTplPath(dirFunc::getAdminTplPathIn('plugin'));
        $this->view->setBlock('imgGallery', 'fileManager/imgGallery.tpl.php');

        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. itemAction
    }

    /*public function fileManagerAction() {
        $compId = $this->compId;
        $contId = $this->contId;

        $fileManager = new fileManager();

        $pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
        // Получаем директорию куда будет заливать файл
        $fileDistPath = dirFunc::getSiteUploadPathData() . $pathPrefix;
        $filePublicUrl = dirFunc::getSiteUploadUrlData() . $pathPrefix;
        $filePreviewUrl = dirFunc::getPreviewImgUrl($pathPrefix);

        $sizeList = objItemModel::getSizeList($contId);
        $sizeList['list'] = objItemModel::makeSelect($sizeList);

        self::setVar('contrName', $contId);
        self::setVar('callType', 'comp');

        $fileManager->showFile($this, $fileDistPath, $filePreviewUrl, $filePublicUrl, $sizeList);

        $this->view->setTplPath(dirFunc::getAdminTplPathIn('plugin'));
        $this->view->setMainTpl('fileManager/imgGallery.tpl.php');

        // func. fileManagerAction
    }*/

    /**
     * Расширенные настройки для компонента
     */
    public function compPropAction() {
        self::setVar('contId', $this->contId);

        $sizeList = (new imgSizeList())->selectAll('name, val, type, id', 'contid=' . $this->contId) ? : [];
        self::setJson('sizeList', $sizeList);

        $this->view->setBlock('panel', '../prop/gallery.tpl.php');

        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. compPropAction
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

    public function uploadFileAction() {
        $this->view->setRenderType(render::JSON);
        // Если это не пост и не загрузка файлов, то выходим
        if (!self::isPost() || !self::isFileUpload()) {
            return;
        }

        $contId = $this->contId;
        $compId = $this->compId;
        $objItemId = self::getInt('objItemId');

        $pathPostfix = 'comp/' . $compId . '/' . $contId . '/'.$objItemId.'/';

        // Имя переменой файла
        $varName = 'files';
        // Принимаем один файл, не массив файлов
        $upload = new upload(upload::FILE_SINGLE);

        $fileName = fileManagerModel::getFileNewName($upload, $varName);

        // Получаем директорию куда будет заливать файл
        $fileDistPath = dirFunc::getSiteUploadPathData() . $pathPostfix;
        $filePreviewPath = dirFunc::getPreviewImgPath($pathPostfix);

        // Устанавливаем новоемя имя, папку куда сохранять
        $upload->setFileName($fileName)
            ->setDistPath($fileDistPath)
            ->upload($varName);

        // Полное имя файла с директорией
        $fileNameFull = $fileDistPath . $fileName;

        // Изображение ли это
        $isImage = imageProp::isImage($fileNameFull);
        if ($isImage) {
            // Создаём директорию, если нужно
            filesystem::mkdir($filePreviewPath);
            // Делаем ресайз изобржанеия и сохраняем превью
            $resize = new resize();
            $resize->setWidth(128)
                ->setHeight(128)
                ->setType(resize::SQUARE)
                ->resize($fileNameFull, $filePreviewPath . $fileName);
        }

        // Получам по файлу атрибуты и ссылку на превью
        $fileData = fileManagerModel::getFileData($fileName, $fileDistPath);
        self::setVar('json', $fileData);
        // func. uploadFile
    }

    /**
     * Сохранение данных компонента
     */
    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);
        $contId = $this->contId;
        $compId = $this->compId;
        $objItemId = self::getInt('objItemId');

        $pathPostfix = 'comp/' . $compId . '/' . $contId . '/'.$objItemId.'/';

        $dataBuffer = [];
        $captionList = self::post('caption');
        $sFileList = self::post('s');
        if ($captionList && is_array($captionList)) {
            foreach ($captionList as $md5 => $caption) {
                $file = isset($sFileList[$md5]) ? $sFileList[$md5] : null;
                $file = htmlspecialchars($file);
                $caption = htmlspecialchars($caption);
                $dataBuffer['data'][$md5] = [
                    'caption' => $caption,
                    'file' => $file
                ];
            } // foreach
        } // if*/

        eventCore::callOffline(
            eventGallery::NAME,
            eventGallery::ACTION_SAVE,
            ['compId' => $compId],
            $objItemId
        );

        // Размеры большой картинки
        $origSize = self::postInt('origSize');
        $origSize = $origSize == -1 ? null : $origSize;
        // Размеры превью картинки
        $prevSize = self::postInt('prevSize');
        $prevSize = $prevSize == -1 ? null : $prevSize;
        $dataBuffer['size'] = [
            'origSize' => $origSize,
            'prevSize' => $prevSize
        ];
        $dataBuffer['isCrPreview'] = self::postInt('isCrPreview');
        // Всё сохраняем в файл
        $saveDir = dirFunc::getSiteDataPath($pathPostfix);
        filesystem::saveFile($saveDir, 'data.txt', \serialize($dataBuffer));

        // Получаем список файлов, для удаления
        $rFileList = self::post('r');
        // Есть ли что удалять
        if ($rFileList) {
            // Этот список должен приходить массивом
            if (!is_array($rFileList)) {
                throw new \Exception('r - must be array');
            } // if is_array
            // Если сохранение небыло, то надо поставить
            // событие на перегенрацию списка картинок
            if (!$sFileList) {
                eventCore::callOffline(
                    eventGallery::NAME,
                    eventGallery::ACTION_RM,
                    ['compId' => $compId],
                    $objItemId
                );
            } // if
            // Удаляем файлы
            modelGallery::fileRm($pathPostfix, $rFileList);
            // Отправляем на страницу какие файлы удалили
            $idList = array_keys($rFileList);
            self::setVar('json', ['idlist' => $idList]);
        }
        // func. saveDataAction
    }

    // class gallery
}