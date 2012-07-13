<?php

namespace buildsys\library\event\comp\spl\imgGallery;

// ORM
use ORM\event\eventClass;
use ORM\tree\compContTree;
use ORM\event\eventBuffer;
use ORM\imgSizeList;
// Event comp
use core\classes\filesystem;
// Engine
use core\classes\image\resize;

// Conf
use \DIR;

/**
 * Обработчик событий для меню
 *
 * @author Козленко В.Л.
 */
class event {

    public static function createFile($pListerUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        // Получаем активность по компоненту
        // в userId пишется compId
        $contIdList = $pEventBuffer
            ->select('cc.comp_id compId, cc.id contId', 'eb')
            //->join(imgGalleryPropOrm::TABLE.' igp', 'igp.contId=eb.userId')
            ->join(compContTree::TABLE.' cc', 'cc.id = eb.userId')
            ->where('eventName in (' . $pEventList . ')')
            ->group('userId')
            ->fetchAll();

        $imgSizeList = new imgSizeList();

        foreach( $contIdList as $contItem){
            $pathPrefix = 'comp/'.$contItem['compId'] . '/' . $contItem['contId'] . '/';
            $contFileData = DIR::getSiteDataPath($pathPrefix).'data.txt';
            if ( !is_file($contFileData)){
                continue;
            }
            $contData = file_get_contents($contFileData);
            $contData = unserialize($contData);

            // Получаем данные для ресайза изображений
            // Для превью
            $prevData = $imgSizeList->selectFirst('type, val', 'id='.$contData['size']['prevSize']);
            // Для больших картинок
            $origData = $imgSizeList->selectFirst('type, val', 'id='.$contData['size']['origSize']);

            // Директория, где храняться все файлы и изображения
            $fileDistPath = DIR::getSiteUploadPathData() . $pathPrefix;
            // Директория, куда положим маштабированные изображения
            $fileResizePath = DIR::getSiteImgResizePath() . $pathPrefix;

            // Список отмеченных файлов, после проверки на существовование
            $fileClearList = [];
            // Список отмеченных файлов
            //$fileNameList = (new imgGalleryOrm())->selectList('filename', 'filename', 'contId='.$contItem['contId']);
            $fileNameList = $contData['data'];
            foreach( $fileNameList as $file ){
                //print $fileDistPath . $file."\n";
                // ===== Проверяем, есть ли уже такое маштабированное изображение
                if (!is_file($fileDistPath . $file['file'])) {
                    continue;
                }

                // Создаём, если нужно, папку для хранения отомаштабированного изображения
                filesystem::mkdir($fileResizePath);

                // Маштабироваие превью изображения
                $val = (int)$prevData['val'];
                $resize = new resize();
                $resize->{'set' . $prevData['type']}($val);
                $resize->resize($fileDistPath . $file['file'], $fileResizePath . 's-'.$file['file']);

                // Маштабироваие большого изображения
                $val = (int)$origData['val'];
                $resize = new resize();
                $resize->{'set' . $origData['type']}($val);
                $resize->resize($fileDistPath . $file['file'], $fileResizePath . 'o-'.$file['file']);

                $fileClearList[] = ['file' => $file['file'], 'capt' => $file['caption'] ];
            } // foreach $file
            unset($fileNameList);

            // Сохраняем список файлов
            $saveDir = DIR::getSiteDataPath($pathPrefix);
            $data = \serialize(array_values($fileClearList));
            filesystem::saveFile($saveDir, 'list.txt', $data);
        } // foreach

        //echo 'catalogCont createFile END' . PHP_EOL;
    }

    public function createListOnly($pListerUserData, $pEventBuffer, $pEventList){
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        // Получаем активность по компоненту
        // в userId пишется compId
        $contIdList = $pEventBuffer
            ->select('cc.comp_id compId, userId contId', 'eb')
            ->join(compContTree::TABLE.' cc', 'cc.id = igp.contId')
            ->where('eventName in (' . $pEventList . ')')
            ->group('userId')
            ->fetchAll();

        foreach( $contIdList as $contItem){

            $pathPrefix = 'comp/'.$contItem['compId'] . '/' . $contItem['contId'] . '/';
            $contFileData = DIR::getSiteDataPath($pathPrefix).'data.txt';
            if ( !is_file($contFileData)){
                continue;
            }
            $contData = file_get_contents($contFileData);
            $contData = unserialize($contData);

           /// $pathPrefix = 'comp/'.$contItem['compId'] . '/' . $contItem['contId'] . '/';
            $fileDistPath = DIR::getSiteUploadPathData() . $pathPrefix;

            // Список отмеченных файлов, после проверки на существовование
            $fileClearList = [];
            // Список отмеченных файлов
            //$fileNameList = (new imgGalleryOrm())->selectList('filename', 'filename', 'contId='.$contItem['contId']);
            foreach( $contData['data'] as $file ){
                // ===== Проверяем, есть ли уже такое маштабированное изображение
                if (!is_file($fileDistPath . $file['file'])) {
                    continue;
                }
                $fileClearList[] = ['file' => $file['file'], 'capt' => $file['caption'] ];
            } // foreach $filename
            unset($fileNameList);

            // Сохраняем список файлов
            $saveDir = DIR::getSiteDataPath($pathPrefix);
            $data = \serialize(array_values($fileClearList));
            filesystem::saveFile($saveDir, 'list.txt', $data);
        } // foreach
    }

    // class event
}