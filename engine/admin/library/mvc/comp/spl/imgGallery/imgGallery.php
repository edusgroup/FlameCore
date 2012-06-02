<?php

namespace admin\library\mvc\comp\spl\imgGallery;

// Engine
use core\classes\render;
use core\classes\filesystem;
use core\classes\validation\filesystem as fileValidation;
use core\classes\upload;
use core\classes\image\resize;
use core\classes\image\imageProp;
use core\classes\event as eventCore;
//use core\classes\filesystem;
// Conf
use \DIR;
use \SITE;
// Plugin
use admin\library\mvc\plugin\fileManager\fileManager;
use admin\library\mvc\plugin\fileManager\model as fileManagerModel;
// Orm
use ORM\imgSizeList;
// Model
use admin\library\mvc\comp\spl\article\model as articleModel;

/**
 * @author Козленко В.Л.
 */
class imgGallery extends \core\classes\component\abstr\admin\comp {

    public function init() {
        
    }

    public function indexAction() {
        $contId = $this->contId;
        $compId = $this->compId;

        self::setVar('contId', $contId, -1);
        self::setVar('compId', $compId, -1);

        $fileManager = new fileManager();

        $pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
        // Получаем директорию куда будет заливать файл
        $fileDistPath = DIR::getSiteUploadPathData() . $pathPrefix;
        $filePublicUrl = DIR::getSiteUploadUrlData() . $pathPrefix;
        $filePreviewUrl = DIR::getPreviewImgUrl($pathPrefix);

        $sizeList = articleModel::getSizeList($contId);
        $sizeList['list'] = articleModel::makeSelect($sizeList);

        self::setVar('contrName', $contId);
        self::setVar('callType', 'comp');

        $contDir = 'comp/' . $compId . '/' . $contId . '/data.txt';
        $contFileData = DIR::getSiteDataPath($contDir);
        if ( is_file($contFileData)){
            $data = file_get_contents($contFileData);
            $data = unserialize($data);
            self::setJson('fileData', $data);
        } // if

        $fileManager->showFile($this, $fileDistPath, $filePreviewUrl, $filePublicUrl, $sizeList);

        $tplFile = self::getTplFile();
        $this->view->setBlock('panel', $tplFile);

        $this->view->setTplPath(DIR::getTplPath('plugin'));
        $this->view->setBlock('imgGallery', 'fileManager/imgGallery.tpl.php');

        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function fileManagerAction() {
        //$className = $this->objProp['classname'];
        $id = self::getInt('id');
        $compId = $this->compId;
        $contId = $this->contId;

        $fileManager = new fileManager();

        $pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
        // Получаем директорию куда будет заливать файл
        $fileDistPath = DIR::getSiteUploadPathData() . $pathPrefix;
        $filePublicUrl = DIR::getSiteUploadUrlData() . $pathPrefix;
        $filePreviewUrl = DIR::getPreviewImgUrl($pathPrefix);

        $sizeList = articleModel::getSizeList($contId);
        $sizeList['list'] = articleModel::makeSelect($sizeList);

        self::setVar('contrName', $contId);
        self::setVar('callType', 'comp');

        $fileManager->showFile($this, $fileDistPath, $filePreviewUrl, $filePublicUrl, $sizeList);

        $this->view->setTplPath(DIR::getTplPath('plugin'));
        $this->view->setMainTpl('fileManager/imgGallery.tpl.php');

        // func. fileManagerAction
    }

    /**
     * Расширенные настройки для компонента
     */
    public function compPropAction() {
        $contId = $this->contId;
        self::setVar('contId', $this->contId);

        $sizeList = (new imgSizeList())->selectAll('name, val, type, id', 'contid=' . $this->contId) ? : [];
        self::setJson('sizeList', $sizeList);

        $this->view->setBlock('panel', 'prop/gallery.tpl.php');

        $this->view->setTplPath(DIR::getTplPath('manager'));
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
        // Имя переменой файла
        $varName = 'files';
        // Принимаем один файл, не массив файлов
        $upload = new upload(upload::FILE_SINGLE);

        $fileName = fileManagerModel::getFileNewName($upload, $varName);
        $pathPrefix = 'comp/' . $compId . '/' . $contId . '/';
        // Получаем директорию куда будет заливать файл
        $fileDistPath = DIR::getSiteUploadPathData() . $pathPrefix;
        $filePreviewPath = DIR::getPreviewImgPath($pathPrefix);

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
        //$imgGalleryOrm = new imgGalleryOrm();
        //$imgGalleryOrm->delete('contId='.$contId);

        $dataBuffer = [];
        $captionList = self::post('caption');
        $sFileList = self::post('s');
        if ( $captionList && is_array($captionList) ){
            foreach( $captionList as $md5 => $caption ){
                $file = isset($sFileList[$md5]) ? $sFileList[$md5] : null;
                $file = htmlspecialchars($file);
                $caption = htmlspecialchars($caption);
                $dataBuffer['data'][$md5] = ['caption'=>$caption, 'file' => $file];
            } // foreach
        } // if*/

        $origSize = self::postInt('origSize');
        $origSize = $origSize == -1 ? null : $origSize;
        $prevSize = self::postInt('prevSize');
        $prevSize = $prevSize == -1 ? null : $prevSize;
        $dataBuffer['size'] = ['origSize' => $origSize, 'prevSize' => $prevSize ];

        $saveDir = 'comp/' . $compId . '/' . $contId . '/';
        $saveDir = DIR::getSiteDataPath($saveDir);
        filesystem::saveFile($saveDir, 'data.txt', \serialize($dataBuffer));

        // Получаем список файлов, для удаления
        $rFileList = self::post('r');
        // Есть ли что удалять
        if ( $rFileList ){
            // Этот список должен приходить массивом
            if ( !is_array($rFileList)){
                throw new \Exception('r - must be array');
            } // if is_array
            // Если сохранение небыло, то надо поставить
            // событие на перегенрацию списка картинок
            if ( !$sFileList){
                eventCore::callOffline(
                    event::NAME,
                    event::ACTION_RM,
                    ['compId' => $compId],
                    $contId
                );
            } // if
            // Удаляем файлы
            model::fileRm($contId, $compId, $rFileList);
            // Отправляем на страницу какие файлы удалили
            $idList = array_keys($rFileList);
            self::setVar('json', ['idlist' => $idList]);
        }
        // func. saveDataAction
    }

    public function getTableData($pContId) {
        // Не исплользуется
    }

    public function getTableOrm() {
        // Не исплользуется
    }
    
    public function blockItemShowAction(){
        $this->view->setRenderType(render::NONE);
        echo 'Нет данных';
    }

}