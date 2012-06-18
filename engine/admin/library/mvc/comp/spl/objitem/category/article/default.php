<?php

namespace admin\library\mvc\comp\spl\objItem\category;

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

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;
// Event
use admin\library\mvc\comp\spl\objItem\category\article\event;

/**
 * Description of objItem
 * @see http://fancyapps.com/fancybox/
 * @author Козленко В.Л.
 */
trait category{

    public function itemAction() {
        $contId = $this->contId;
        self::setVar('contId', $contId);
        $compId = $this->compId;

        // TODO: Вставить проверку на существование $objItemId
        $objItemId = self::getInt('id');
        self::setVar('objItemId', $objItemId, -1);

        $objItemOrm = new objItemOrm();
        $objItemData = $objItemOrm->selectFirst('prevImgUrl, caption, seoKeywords, seoDescr, isCloaking', 'id=' . $objItemId);
        if ( !$objItemData){
            throw new \Exception('Item Id: '.$objItemId.' not found', 234);
        }

        self::setJson('objItemData', $objItemData);
        self::setVar('caption', $objItemData['caption']);

        // Загружаем текст статьи. '' - значаение по умолчанию, false - без десериализации
        $loadDir = objItemModel::getPath($compId, $contId, $objItemId);
        $loadDir = DIR::getSiteDataPath($loadDir);

        //$text = storage::loadVar($loadDir, 'data', '', false);
        $textData = '';
        if (is_readable($loadDir . 'data.txt')) {
            $textData = file_get_contents($loadDir . 'data.txt');
        }
        if (is_readable($loadDir . 'kat.txt')) {
            $textKat = file_get_contents($loadDir . 'kat.txt');
            if ($textKat) {
                $textData = $textKat . '<hr />' . $textData;
            } // if
        } // if
        self::setVar('text', $textData);

        if (is_readable($loadDir . 'minidescr.txt')) {
            self::setVar('miniDescrText', file_get_contents($loadDir . 'minidescr.txt'));
        } // if

        // Загружаем данные клоакинга
        if ($objItemData['isCloaking']) {
            self::setVar('cloakingText', file_get_contents($loadDir . 'cloak.txt'));
        }

        $tplFile = self::getTplFile();

        $this->view->setBlock('panel', $tplFile);
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. itemAction
    }

    /**
     * Сохранение данных компонента.<br/>
     * Входящие GET параметры:<br/>
     * id - ID статьи. см ORM comp/spl/objItem<br/>
     * Входящие POST параметры:<br/>
     * objItem - текст статьи
     */
    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);

        $contId = $this->contId;
        $compId = $this->compId;
        // ID статьи
        $objItemId = self::postInt('id');

        eventCore::callOffline(
            event::NAME,
            event::ACTION_SAVE,
            ['compId' => $compId, 'contId' => $contId],
            $objItemId
        );

        // Директория с данными статьи
        $saveDir = objItemModel::getPath($compId, $contId, $objItemId);
        $saveDir = DIR::getSiteDataPath($saveDir);
        $seoKeywords = self::post('seoKeywords');
        $seoDescr = self::post('seoDescr');

        // Статья клоакинга
        $cloakingText = self::post('cloakingText');

        // Сохраняем превью изображения для статьи
        $prevImgUrl = self::post('prevImgUrl');
        $objItemOrm = new objItemOrm();
        $objItemOrm->update(['prevImgUrl' => $prevImgUrl,
                            'seoKeywords' => $seoKeywords,
                            'isCloaking' => trim($cloakingText) != '',
                            'seoDescr' => $seoDescr]
            , 'id=' . $objItemId);


        //$saveDir = objItemModel::saveDataInfo($objItemId, $objItemOrm, $compId, $contId);
        // TODO: добавить настройку фильтрации кода HTML
        //class_exists('admin\library\comp\spl\objItem\htmlvalid\full');
        //htmlValid::validate($data);

        // Текст статьи
        $srcData = self::post('objItem');
        $distData = $srcData;
        $katData = '';

        $miniDescr = self::post('miniDescrText');

        $pos = strpos($srcData, '<hr />');
        if ($pos !== false) {
            $katData = substr($srcData, 0, $pos);
            $distData = substr($srcData, $pos + 6);
        }
        filesystem::saveFile($saveDir, 'kat.txt', $katData);
        filesystem::saveFile($saveDir, 'data.txt', $distData);
        filesystem::saveFile($saveDir, 'cloak.txt', $cloakingText);
        filesystem::saveFile($saveDir, 'minidescr.txt', $miniDescr);
        // func. saveDataAction
    }

    public function fileManagerAction() {
        //$className = $this->objProp['classname'];
        $id = self::getInt('id');
        $compId = $this->compId;
        $contId = $this->contId;

        $fileManager = new fileManager();

        $pathPrefix = objItemModel::getPath($compId, $contId, $id);
        // Получаем директорию куда будет заливать файл
        $fileDistPath = DIR::getSiteUploadPathData() . $pathPrefix;
        $filePublicUrl = DIR::getSiteUploadUrlData() . $pathPrefix;
        $filePreviewUrl = DIR::getPreviewImgUrl($pathPrefix);

        $sizeList = objItemModel::getSizeList($contId);
        $sizeList['list'] = objItemModel::makeSelect($sizeList);

        self::setVar('contrName', $contId);
        self::setVar('callType', 'comp');
        self::setVar('userQuery', 'id=' . $id);

        $fileManager->showFile($this, $fileDistPath, $filePreviewUrl, $filePublicUrl, $sizeList);

        $this->view->setTplPath(DIR::getTplPath('plugin'));
        $this->view->setMainTpl('fileManager/ckedit.tpl.php');

        // func. fileManagerAction
    }

    public function uploadFileAction() {
        // TODO: Перенести в PHP 5.4 в трейд
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
        /*$isNew = objItemModel::isDublFile($fileTmpName, $contId);
        if ($isNew) {
            self::setVar('json', array('dubl' => 1));
            return;
        }*/

        $fileName = fileManagerModel::getFileNewName($upload, $varName);
        $pathPrefix = objItemModel::getPath($compId, $contId, $id);
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

    public function fileRmAction() {
        $this->view->setRenderType(render::JSON);
        $contId = $this->contId;
        $compId = $this->compId;
        $id = self::getInt('id');

        $nameList = self::post('f');
        objItemModel::fileRm($contId, $compId, $id, $nameList);
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
        }

        $contId = $this->contId;
        $compId = $this->compId;
        $id = self::getInt('id');

        $name = self::get('name');
        fileValid::isSafe($name, new \Exception('Неверное имя файла', 234));

        $pathPrefix = objItemModel::getPath($compId, $contId, $id);

        // Директория, где храняться все файлы и изображения
        $fileDistPath = DIR::getSiteUploadPathData() . $pathPrefix;

        // Директория, куда положим маштабированные изображения
        $pathPrefix .= $dataSize['type'] . '/' . $dataSize['val'] . '/';
        $fileResizePath = DIR::getSiteImgResizePath() . $pathPrefix;
        $fileResizeUrl = DIR::getSiteImgResizeUrl() . $pathPrefix;

        // ===== Проверяем, есть ли уже такое маштабированное изображение
        if (is_file($fileResizePath . $name)) {
            self::setVar('json', ['url' => $fileResizeUrl . $name]);
        }

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

    public function blockItemShowAction() {
        $this->view->setRenderType(render::NONE);
        echo 'article::blockItemShowAction() | No settings in this';
    }

    // class objItem
}

?>