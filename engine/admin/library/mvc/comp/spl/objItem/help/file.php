<?php

namespace admin\library\mvc\comp\spl\objItem\help;

// Conf
use \DIR;
use \SITE;

// Engine
use core\classes\render;
use core\classes\filesystem;
use core\classes\upload;
use core\classes\image\resize;
use core\classes\DB\tree;
use core\classes\image\imageProp;
use core\classes\validation\filesystem as fileValid;

// ORM
use ORM\imgSizeList;

// Plugin
use admin\library\mvc\plugin\fileManager\fileManager;
use admin\library\mvc\plugin\fileManager\model as fileManagerModel;

// Model
use admin\library\mvc\comp\spl\objItem\help\model\base\model as baseModel;

trait file {

    public function uploadFileAction() {
        $this->view->setRenderType(render::JSON);
        // Если это не пост и не загрузка файлов, то выходим
        if (!self::isPost() || !self::isFileUpload()) {
            return;
        }

        $contId = $this->contId;
        $compId = $this->compId;
        $id = self::getInt('id');
        // Имя переменой файла
        $varName = 'files';
        // Принимаем один файла, не массив файлов
        $upload = new upload(upload::FILE_SINGLE);

        // Получаем имя файла, которые пришло от пользователя
        //$fileTmpName = $upload->getFileTmpName($varName, 0);
        // Если запись есть, то это дубликат
        /*$isNew = baseModel::isDublFile($fileTmpName, $contId);
        if ($isNew) {
            self::setVar('json', array('dubl' => 1));
            return;
        }*/

        $fileName = fileManagerModel::getFileNewName($upload, $varName);
        $pathPrefix = baseModel::getPath($compId, $contId, $id);
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

    public function fileManagerAction() {
        $id = self::getInt('id');
        $compId = $this->compId;
        $contId = $this->contId;

        $fileManager = new fileManager();

        $pathPrefix = baseModel::getPath($compId, $contId, $id);
        // Получаем директорию куда будет заливать файл
        $fileDistPath = DIR::getSiteUploadPathData() . $pathPrefix;
        $filePublicUrl = DIR::getSiteUploadUrlData() . $pathPrefix;
        $filePreviewUrl = DIR::getPreviewImgUrl($pathPrefix);

        $sizeList = baseModel::getSizeList($contId);
        $sizeList['list'] = baseModel::makeSelect($sizeList);

        self::setVar('contrName', $contId);
        self::setVar('callType', 'comp');
        self::setVar('userQuery', 'id=' . $id);

        $fileManager->showFile($this, $fileDistPath, $filePreviewUrl, $filePublicUrl, $sizeList);

        $this->view->setTplPath(DIR::getTplPath('plugin'));
        $this->view->setMainTpl('fileManager/ckedit.tpl.php');
        // func. fileManagerAction
    }

    public function fileRmAction() {
        $this->view->setRenderType(render::JSON);
        $contId = $this->contId;
        $compId = $this->compId;
        $id = self::getInt('id');

        $nameList = self::post('f');
        baseModel::fileRm($contId, $compId, $id, $nameList);
        $idList = array_keys($nameList);

        self::setVar('json', ['idlist' => $idList]);
        // func. fileRmAction
    }

    public function makePreviewUrlAction() {
        $this->view->setRenderType(render::JSON);
        $sizeId = self::getInt('sizeid');
        $imgSizeList = new imgSizeList();
        $dataSize = $imgSizeList->selectFirst('*', 'id=' . $sizeId);
        if (!$dataSize) {
            throw new \Exception('Ошибка в полученныи size', 26);
        } // if

        $contId = $this->contId;
        $compId = $this->compId;
        $id = self::getInt('id');

        $name = self::get('name');
        fileValid::isSafe($name, new \Exception('Неверное имя файла', 234));

        $pathPrefix = baseModel::getPath($compId, $contId, $id);

        // Директория, где храняться все файлы и изображения
        $fileDistPath = DIR::getSiteUploadPathData() . $pathPrefix;

        // Директория, куда положим маштабированные изображения
        $pathPrefix .= $dataSize['type'] . '/' . $dataSize['val'] . '/';
        $fileResizePath = DIR::getSiteImgResizePath() . $pathPrefix;
        $fileResizeUrl = DIR::getSiteImgResizeUrl() . $pathPrefix;

        // ===== Проверяем, есть ли уже такое маштабированное изображение
        if (is_file($fileResizePath . $name)) {
            self::setVar('json', ['url' => $fileResizeUrl . $name]);
        } // if

        // Создаём, если нужно, папку для хранения отомаштабированного изображения
        filesystem::mkdir($fileResizePath);

        // ===== Маштабироваие изображения
        $val = (int)$dataSize['val'];
        $resize = new resize();
        $resize->{'set' . $dataSize['type']}($val);
        $resize->resize($fileDistPath . $name, $fileResizePath . $name);

        $imgSizeList->update('is_use=1', 'id=' . $sizeId);

        self::setVar('json', ['url' => $fileResizeUrl . $name]);
        // func. makePreviewUrl
    }
    // trait file
}