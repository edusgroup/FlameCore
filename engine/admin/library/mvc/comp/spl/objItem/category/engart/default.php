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

// Model
use admin\library\mvc\comp\spl\objItem\category\engart\model as engartModel;

// Event
use admin\library\mvc\comp\spl\objItem\category\article\event;

/**
 * Логика по управлению обычной статьёй
 * @see http://fancyapps.com/fancybox/
 * @author Козленко В.Л.
 */
trait category{

    /**
     * Внешний вид страница по управлению статьями
     * @throws \Exception
     */
    public function itemAction() {

        $contId = $this->contId;
        self::setVar('contId', $contId);
        $compId = $this->compId;

        // ID статьи
        $objItemId = self::getInt('id');
        self::setVar('objItemId', $objItemId, -1);

        $engartText = engartModel::html2data();
        self::setVar('engartText', $engartText, false);

        // Получаем шаблон админки, который нужно отобразить.
        // Шаблоны можно задавать в настройках компонента
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

        /*$contId = $this->contId;
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
        (new articleOrm())->update(['prevImgUrl' => $prevImgUrl,
                            'seoKeywords' => $seoKeywords,
                            'isCloaking' => trim($cloakingText) != '',
                            'seoDescr' => $seoDescr]
            , 'itemObjId=' . $objItemId);

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
        filesystem::saveFile($saveDir, 'minidescr.txt', $miniDescr);*/
        // func. saveDataAction
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
        // func. blockItemShowAction
    }

    // class category ( itemObj->engart )
}