<?php

namespace buildsys\library\event\comp\spl\objItem\gallery;

// ORM
use ORM\tree\compContTree;
use ORM\event\eventBuffer;
use ORM\imgSizeList;
use ORM\comp\spl\objItem\objItem as objItemOrm;

// Event comp
use core\classes\filesystem;

// Engine
use core\classes\image\resize;
use core\classes\admin\dirFunc;

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
        // в userId пишется objItemId
        $contIdList = $pEventBuffer
            ->select('cc.comp_id compId, oi.treeId contId, oi.id', 'eb')
            ->join(objItemOrm::TABLE.' oi', 'oi.id = eb.userId')
            ->join(compContTree::TABLE . ' cc', 'cc.id = oi.treeId')
            ->where('eventName in (' . $pEventList . ')')
            ->group('userId')
            ->fetchAll();


        $imgSizeList = new imgSizeList();

        foreach ($contIdList as $contItem) {
            // Загружаем файл с данными по конкретному contId и objItemId
            $pathPrefix = 'comp/' . $contItem['compId'] . '/' . $contItem['contId'] . '/'.$contItem['id'].'/';
            $contFileData = dirFunc::getSiteDataPath($pathPrefix) . 'data.txt';
            // Если файла нет, то идёт к следующему contId
            /*if (!is_file($contFileData)) {
                continue;
            }
            $contData = file_get_contents($contFileData);
            $contData = unserialize($contData);*/

            $contData = filesystem::loadFileContentUnSerialize($contFileData);
            if ( $contData === null ){
                continue;
            }

            // Получаем данные для ресайза изображений. Для превью
            $idSize = $contData['size']['prevSize'] ? : -1;
            $prevData = $imgSizeList->selectFirst('type, val', 'id=' . $idSize);
            // Для больших картинок
            $idSize = $contData['size']['origSize'] ? : -1;
            $origData = $imgSizeList->selectFirst('type, val', 'id=' . $idSize);

            // Директория, где храняться все файлы и изображения
            $fileDistPath = dirFunc::getSiteUploadPathData() . $pathPrefix;
            // Директория, куда положим маштабированные изображения
            $fileResizePath = dirFunc::getSiteImgResizePath() . $pathPrefix;

            // Список отмеченных файлов, после проверки на существовование
            $fileClearList = [];
            // Список отмеченных файлов

            // Буффер для пометки какие файлы уже были под изменением размера, а какие поменялись
            $fileMd5Buffer['old'] = filesystem::loadFileContentUnSerialize($fileResizePath.'md5.txt') ?: [];
            $fileMd5Buffer['new'] = [];

            $fileNameList = $contData['data'];
            foreach ($fileNameList as $file) {
                // ===== Проверяем, есть ли уже такое маштабированное изображение
                // $file['file'] - может быть, пустым, если он не выделен(не выбран) в админке
                $fileSource = $fileDistPath . $file['file'];
                if ( !$file['file'] || !is_file($fileSource)) {
                    continue;
                }
                // Превью файла
                $previewFile = $fileResizePath . 's-' . $file['file'];
                // Большой файл
                $origFile = $fileResizePath . 'o-' . $file['file'];

                // Получаем md5 файла
                $fileMd5 = md5_file($fileSource);
                // Если есть, значит файл не изменился, добавляем в новый массив,
                // вдруг количество файлов изменилось, обновим список
                $fileMd5Buffer['new'][] = $fileMd5;
                // Есть ли он в старом массиве( старый, значит от прошлой генерации )
                if ( in_array($fileMd5, $fileMd5Buffer['old']) && (is_file($previewFile) || is_file($origFile))){
                    continue;
                } // if


                $resize = new resize();

                // Создаём, если нужно, папку для хранения отомаштабированного изображения
                filesystem::mkdir($fileResizePath);

                // Маштабироваие превью изображения
                if ($prevData && $contData['isCrPreview']) {
                    $val = (int)$prevData['val'];
                    $resize->{'set' . $prevData['type']}($val);
                    $resize->resize($fileSource, $previewFile);
                } // if ( $prevData )
                //echo $fileResizePath . 's-' . $file['file']."\n";

                // Маштабироваие большого изображения
                if ($origData) {
                    $val = (int)$origData['val'];
                    $resize->{'set' . $origData['type']}($val);
                    $resize->resize($fileSource, $origFile);
                } // if ($origData)

                $fileClearList[] = [
                    'file' => $file['file'],
                    'capt' => $file['caption']
                ];
            } // foreach $file
            unset($fileNameList);

            $data = \serialize($fileMd5Buffer['new']);
            filesystem::saveFile($fileResizePath, 'md5.txt', $data);

            // Сохраняем список файлов
            $saveDir = dirFunc::getSiteDataPath($pathPrefix);
            $data = \serialize(array_values($fileClearList));
            filesystem::saveFile($saveDir, 'list.txt', $data);
        } // foreach

        // func. createFile
    }

    /*public function createListOnly($pListerUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        // Получаем активность по компоненту
        // в userId пишется compId
        $contIdList = $pEventBuffer
            ->select('cc.comp_id compId, userId contId', 'eb')
            ->join(compContTree::TABLE . ' cc', 'cc.id = igp.contId')
            ->where('eventName in (' . $pEventList . ')')
            ->group('userId')
            ->fetchAll();

        foreach ($contIdList as $contItem) {

            $pathPrefix = 'comp/' . $contItem['compId'] . '/' . $contItem['contId'] . '/';
            $contFileData = dirFunc::getSiteDataPath($pathPrefix) . 'data.txt';
            if (!is_file($contFileData)) {
                continue;
            }
            $contData = file_get_contents($contFileData);
            $contData = unserialize($contData);

            /// $pathPrefix = 'comp/'.$contItem['compId'] . '/' . $contItem['contId'] . '/';
            $fileDistPath = dirFunc::getSiteUploadPathData() . $pathPrefix;

            // Список отмеченных файлов, после проверки на существовование
            $fileClearList = [];
            // Список отмеченных файлов
            //$fileNameList = (new imgGalleryOrm())->selectList('filename', 'filename', 'contId='.$contItem['contId']);
            foreach ($contData['data'] as $file) {
                // ===== Проверяем, есть ли уже такое маштабированное изображение
                if (!is_file($fileDistPath . $file['file'])) {
                    continue;
                }
                $fileClearList[] = ['file' => $file['file'], 'capt' => $file['caption']];
            } // foreach $filename
            unset($fileNameList);

            // Сохраняем список файлов
            $saveDir = dirFunc::getSiteDataPath($pathPrefix);
            $data = \serialize(array_values($fileClearList));
            filesystem::saveFile($saveDir, 'list.txt', $data);
        } // foreach
    }*/

    // class event
}