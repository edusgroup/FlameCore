<?php
namespace admin\library\mvc\comp\spl\imgGallery;

// Engine
use core\classes\word;
use core\classes\filesystem;
use core\classes\validation\filesystem as fileValid;
use core\classes\admin\dirFunc;

// Conf
use \DIR;

/**
 * @author Козленко В.Л.
 */
class model {
    public static function fileRm($pCondId, $pCompId, $pNameList) {
        $pathPrefix = 'comp/' . $pCompId . '/' . $pCondId . '/';

        $fileDistPath = dirFunc::getSiteUploadPathData() . $pathPrefix;
        $filePreviewPath = dirFunc::getPreviewImgPath($pathPrefix);
        $fileResizePath = dirFunc::getSiteImgResizePath() . $pathPrefix;
        foreach ($pNameList as $name) {
            fileValid::isSafe($name, new \Exception('Неверное имя файла', 234));

            //$where['md5'] = md5_file($pathDist.$name);
            //$contFile->delete($where);

            filesystem::unlink($fileDistPath . $name);
            filesystem::unlink($filePreviewPath . $name);
            filesystem::unlink($fileResizePath . 'o-' . $name);
            filesystem::unlink($fileResizePath . 's-' . $name);
            //filesystem::rUnlink($fileResizePath, filesystem::ALL_NO_FILTER_FOLDER, $name);
        }
        // func. fileRm
    }
    // class model
}