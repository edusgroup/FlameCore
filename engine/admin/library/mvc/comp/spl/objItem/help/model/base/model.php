<?php

namespace admin\library\mvc\comp\spl\objItem\help\model\base;

// ORM
use ORM\comp\spl\objItem\objItem as objItemOrm;
use ORM\tree\compContTree;
use ORM\imgSizeList;

// Model
use admin\library\mvc\manager\complist\model as complistModel;

// Engine
use core\classes\DB\tree;
use core\classes\word;
use core\classes\filesystem;
use core\classes\validation\filesystem as fileValid;
use core\classes\component\abstr\admin\comp as compAbs;
use core\classes\event as eventCore;

// Conf
use \DIR;

// Event
use admin\library\mvc\comp\spl\objItem\help\event\base\event;

class model {


    public static function getSizeList(integer $contId) {
        $imgSizeList = new imgSizeList();
        $compContTree = new compContTree();
        // TODO: Заменить на treeUrl у objItem
        $contList = $compContTree->getTreeUrlById(compContTree::TABLE, $contId);
        foreach ($contList as $item) {
            $list = $imgSizeList->selectAll('*', 'contid=' . $item['id']);
            if ($list) {
                return $list;
            } // if
        } // foreach
        // func. getSizeList
    }

    /**
     * Формирует список размеров изображений
     * @static
     * @param $pSizeList
     * @return array|null
     */
    public static function makeSelect($pSizeList) {
        $return = [];
        $return[] = ['id' => 'noResize', 'name' => 'Исходное'];
        if (!$pSizeList) {
            return $return;
        } // if

        foreach ($pSizeList as $item) {
            $name = $item['name'] . '(' . $item['type'] . ':' . $item['val'] . ')';
            $return[] = ['id' => $item['id'], 'name' => $name];
        } // foreach
        return $return;
        // func. makeSelect
    }

    public static function isDublFile($pFileTmpName, $pContId) {
        // Создаем ORM
        $contFile = new contFile();
        // Получаем md5 суммуы
        $fileMd5 = md5_file($pFileTmpName);
        // Записываем или смотрим есть ли запись
        $where = ['md5' => $fileMd5, 'contid' => $pContId];
        $isNew = $contFile->save($where, $where);
        return $isNew === null;
        // func. isDublFile
    }

    public static function getPath($pCompId, $pContId, $pObjitem) {
        return 'comp/' . $pCompId . '/' . $pContId . '/' . word::idToSplit($pObjitem);
        // func. getPath
    }

    public static function fileRm($pCondId, $pCompId, $pArtId, $pNameList) {
        //$contFile = new contFile();
        //$where['contid'] = $pCondId;

        $pathPrefix = self::getPath($pCompId, $pCondId, $pArtId);

        $fileDistPath = DIR::getSiteUploadPathData() . $pathPrefix;
        $filePreviewPath = DIR::getPreviewImgPath($pathPrefix);
        $fileResizePath = DIR::getSiteImgResizePath() . $pathPrefix;

        foreach ($pNameList as $name) {
            fileValid::isSafe($name, new \Exception('Неверное имя файла', 234));

            //$where['md5'] = md5_file($pathDist.$name);
            //$contFile->delete($where);

            filesystem::unlink($fileDistPath . $name);
            filesystem::unlink($filePreviewPath . $name);
            //filesystem::rUnlink($filePreviewPath, filesystem::ALL_NO_FILTER_FOLDER, $name);
            filesystem::rUnlink($fileResizePath, filesystem::ALL_NO_FILTER_FOLDER, $name);
        }
        // func. fileRm
    }


    // class model
}