<?php

namespace admin\library\mvc\plugin\fileManager;

// Conf
use \DIR;
// Engine
use core\classes\filesystem;
use core\classes\image\imageProp;
use core\classes\validation\filesystem as fileValid;
use core\classes\word;
// ORM
use ORM\contFile;
use ORM\tree\compContTree;

/**
 * Description of action
 *
 * @author Козленко В.Л.
 */
class model {
    /** @var Поиск только картинок ( jpg, bmp, png, gif ) */
    const FILTER_IMAGE = '/^.*\.(jpg|jpeg|png|gif)$/';
    /** @var Поиск только флеша ( swf ) */
    const FILTER_FLASH = '/^.*\.swf$/';
    /** @var Поиск всех файлов */
    const FILTER_ALL = null;
    
    const FILTER_TYPE_IMG = 'img';
    const FILTER_TYPE_FLASH = 'flash';
    const FILTER_TYPE_ALL = 'file';

   
    /**
     * Получаем загруженные файлы директории
     * @param integer $pContId
     * @param integer $pCompId 
     */
    public static function getFileList($pFilePath, $pFilter=self::FILTER_ALL) {
        $return = [];
        // Получаем список файлов в виде массива
        $list = filesystem::dir2array($pFilePath, filesystem::FILE, $pFilter);
        for ($i = 0; $i < count($list); $i++) {
            // Получаем свойства файлов
            $return[$i] = self::getFileData($list[$i], $pFilePath);
        }
        return $return;
    }
    
    public static function getFileData(string $pFile, string $pPathDist) {
        $data = [];
        $ext = filesystem::getExt($pFile);
        if (in_array($ext, ['jpg', 'png', 'gif', 'jpeg'])) {
            $data['preview'] = $pFile;
            $data['type'] = 'img';
            $imageProp = new imageProp($pPathDist.$pFile);
            $data['imgsize'] = $imageProp->getWidth().'x'.$imageProp->getHeight();
        } else {
            $extImgPath = '/res/plugin/fileManager/img/ext/';
            $extImgFile = $extImgPath . $ext . '.png';
            $data['preview'] = is_file($extImgFile) ? $extImgFile : $extImgPath . 'default.png';
            $data['type'] = 'file';
            $data['imgsize'] = '';
        }
        $data['name'] = $pFile;
        $data['md5'] = md5_file($pPathDist.$pFile);
        $filesize = filesize($pPathDist.$pFile);
        $data['filesize'] = filesystem::formatBytes( $filesize, 0 );
        return $data;
    }
    
    public static function getFileNewName($upload, $varName ){
        // получаем старое имя файла
        $file = $upload->getFileOldName($varName, 0);
        // Убрать cp1251, поставить что то другое
        //$file = iconv('utf-8', 'cp1251', $file);

        // Получаем чистое имя фйла
        $fileName = filesystem::getName($file);
        // Преобразуем в URL name
        $fileName = word::wordToUrl($fileName);
        // получаем расширение файла
        $fileExt = filesystem::getExt($file);
        if ($fileExt == 'jpeg') {
            $fileExt = 'jpg';
        }
        // Формируем новое имя файла
        $fileName = $fileName . '.' . $fileExt;
        return $fileName;//array($fileName, $fileExt);
    }
    // class fileManager(model)
}