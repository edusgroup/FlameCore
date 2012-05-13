<?php

namespace admin\library\mvc\comp\spl\article;

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
use ORM\comp\spl\article\article as articleOrm;
use ORM\comp\spl\article\compArticleProp;

/**
 * Description of article
 * @see http://fancyapps.com/fancybox/
 * @author Козленко В.Л.
 */
class article extends \core\classes\component\abstr\admin\comp {

    public function __construct(string $pTplPath, string $pThemeResUrl) {
        parent::__construct($pTplPath, $pThemeResUrl);
    }

    public function init() {

    }

    public function indexAction() {
        $contId = $this->contId;
        self::setVar('contId', $contId);

        $data = model::getList($contId);
        $listXML = dhtmlxGrid::createXMLOfArray($data, null, null);
        $listXML = addslashes($listXML);

        self::setVar('listXML', $listXML, false);

        $this->view->setBlock('panel', 'table/table.tpl.php');
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    public function itemAction() {
        $contId = $this->contId;
        self::setVar('contId', $contId);
        $compId = $this->compId;

        // TODO: Вставить проверку на существование $articleId
        $articleId = self::getInt('id');
        self::setVar('articleId', $articleId, -1);

        $articleOrm = new articleOrm();
        $articleData = $articleOrm->selectFirst('prevImgUrl, caption, seoKeywords, seoDescr, isCloaking', 'id=' . $articleId);

        self::setJson('articleData', $articleData);
        self::setVar('caption', $articleData['caption']);

        // Загружаем текст статьи. '' - значаение по умолчанию, false - без десериализации
        $loadDir = model::getPath($compId, $contId, $articleId);
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
        if ($articleData['isCloaking']) {
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
     * id - ID статьи. см ORM comp/spl/article<br/>
     * Входящие POST параметры:<br/>
     * article - текст статьи
     */
    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);

        $contId = $this->contId;
        $compId = $this->compId;
        // ID статьи
        $articleId = self::postInt('id');

        eventCore::callOffline(
            event::NAME,
            event::ACTION_SAVE,
            ['compId' => $compId, 'contId' => $contId],
            $articleId
        );

        // Директория с данными статьи
        $saveDir = model::getPath($compId, $contId, $articleId);
        $saveDir = DIR::getSiteDataPath($saveDir);
        $seoKeywords = self::post('seoKeywords');
        $seoDescr = self::post('seoDescr');

        // Статья клоакинга
        $cloakingText = self::post('cloakingText');

        // Сохраняем превью изображения для статьи
        $prevImgUrl = self::post('prevImgUrl');
        $articleOrm = new articleOrm();
        $articleOrm->update(['prevImgUrl' => $prevImgUrl,
                            'seoKeywords' => $seoKeywords,
                            'isCloaking' => trim($cloakingText) != '',
                            'seoDescr' => $seoDescr]
            , 'id=' . $articleId);


        //$saveDir = model::saveDataInfo($articleId, $articleOrm, $compId, $contId);
        // TODO: добавить настройку фильтрации кода HTML
        //class_exists('admin\library\comp\spl\article\htmlvalid\full');
        //htmlValid::validate($data);

        // Текст статьи
        $srcData = self::post('article');
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

    /**
     * Расширенные настройки для компонента
     */
    public function compPropAction() {
        $contId = $this->contId;
        self::setVar('contId', $this->contId);

        $sizeList = (new imgSizeList())->selectAll('name, val, type, id', 'contid=' . $this->contId) ? : [];
        self::setJson('sizeList', $sizeList);

        $compArticleProp = new compArticleProp();
        $url = $compArticleProp->get('url', 'contId=' . $contId);
        self::setVar('url', $url);

        $this->view->setBlock('panel', 'prop/article.tpl.php');

        $this->view->setTplPath(DIR::getTplPath('manager'));
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

    public function fileManagerAction() {
        //$className = $this->objProp['classname'];
        $id = self::getInt('id');
        $compId = $this->compId;
        $contId = $this->contId;

        $fileManager = new fileManager();

        $pathPrefix = model::getPath($compId, $contId, $id);
        // Получаем директорию куда будет заливать файл
        $fileDistPath = DIR::getSiteUploadPathData() . $pathPrefix;
        $filePublicUrl = DIR::getSiteUploadUrlData() . $pathPrefix;
        $filePreviewUrl = DIR::getPreviewImgUrl($pathPrefix);

        $sizeList = model::getSizeList($contId);
        $sizeList['list'] = model::makeSelect($sizeList);

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
        /*$isNew = model::isDublFile($fileTmpName, $contId);
        if ($isNew) {
            self::setVar('json', array('dubl' => 1));
            return;
        }*/

        $fileName = fileManagerModel::getFileNewName($upload, $varName);
        $pathPrefix = model::getPath($compId, $contId, $id);
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
        model::fileRm($contId, $compId, $id, $nameList);
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

        $pathPrefix = model::getPath($compId, $contId, $id);

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

    public function rmTableItemAction() {
        $this->view->setRenderType(render::JSON);
        $rowsId = self::post('rowsId');
        $compId = $this->compId;
        $contId = $this->contId;

        $userData = explode(',', $rowsId);
        $userData = array_map('intVal', $userData);

        eventCore::callOffline(
            event::NAME,
            event::ACTION_DELETE,
            ['itemsId' => $userData,
            'compId' => $compId],
            $contId
        );

        $articleOrm = new articleOrm();
        $where = implode(',', $userData);
        $articleOrm->update('isDel=1', 'id in (' . $where . ')');
        //$list = dhtmlxGrid::rmRows($rowsId, new articleOrm());
        self::setVar('json', [0 => 'ok', 'list' => $userData]);
        // func. rmTableItemAction
    }

    /**
     * Сохранение заголовка, системного имени и публикации.
     * т.е. сохранение в общей таблице статей
     */
    public function saveTableItemDataAction() {
        $this->view->setRenderType(render::JSON);
        $contId = $this->contId;
        //$compId = $this->compId;

        $data = self::post('data');

        $listId = model::saveTableItemData($data, $contId);
        $json = ['newId' => $listId];
        self::setVar('json', $json);
        // func. saveTableItemDataAction
    }

    public function getTableOrm() {
        //$articleOrm = new articleOrm();
        //return $articleOrm->get('caption','id='.$pTableId);
        return new articleOrm();
        // func. getTableItemName
    }

    /**
     * Возврашает список статей пренадлежащей категории
     * @param integer $pContId ID родителя(категории)
     */
    public function getTableData($pContId) {
        $articleOrm = new articleOrm();
        return $articleOrm->select('id, caption')
            ->where('treeId=' . $pContId . ' AND isPublic="yes" AND isDel=0')
            ->comment(__METHOD__)
            ->fetchAll();
        // func. getTableData
    }

    public function savePropDataAction() {
        $this->view->setRenderType(render::JSON);
        $url = self::get('url');
        $contId = $this->contId;

        $compArticleProp = new compArticleProp();
        if ($url) {
            $compArticleProp->saveExt(
                ['contId' => $contId],
                ['url' => $url]);
            $type = 'save';
        } else {
            $compArticleProp->delete('contId=' . $contId);
            $type = 'del';
            //$urlList = (new tree())->getTreeUrlById(compContTree::TABLE, $contId);
            //var_dump($urlList);
        }

        eventCore::callOffline(
            event::NAME,
            event::ACTOIN_CUSTOM_PROP_SAVE,
            $type,
            $contId
        );

        // savePropDataAction 
    }

    public function blockItemShowAction() {
        $this->view->setRenderType(render::NONE);
        echo 'Нет данных';
    }

    // class article
}

?>