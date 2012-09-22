<?php
namespace admin\library\mvc\comp\spl\objItem\help\model\gallery;

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
    public static function fileRm($pPathPostfix, $pNameList) {
        $fileDistPath = dirFunc::getSiteUploadPathData() . $pPathPostfix;
        $filePreviewPath = dirFunc::getPreviewImgPath($pPathPostfix);
        $fileResizePath = dirFunc::getSiteImgResizePath() . $pPathPostfix;
        foreach ($pNameList as $name) {
            fileValid::isSafe($name, new \Exception('Неверное имя файла', 234));

            filesystem::unlink($fileDistPath . $name);
            filesystem::unlink($filePreviewPath . $name);
            filesystem::unlink($fileResizePath . 'o-' . $name);
            filesystem::unlink($fileResizePath . 's-' . $name);
        } // foreach
        // func. fileRm
    }
    // class model
}