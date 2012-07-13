<?php

namespace core\classes\image;

use core\classes\filesystem;

/**
 * Description of manger
 * TODO: перименовать в properties Image
 *
 * @author Козленко В.Л.
 */
class imageProp {

    private $img;
    public $info;
    
    const IMG_GIF = 1, IMG_JPEG = 2, IMG_PNG = 3;
    const INFO_WIDTH = 0, INFO_HEIGHT = 1, INFO_TYPE = 2;
    
    public function __construct(string $pFile){
        self::load($pFile);
        // func. __construct
    }
    
    public function getImage(){
        return $this->img;
        // func. getImage
    }

    public function load(string $pFile) {
        $imgInfo = getimagesize($pFile);
        $this->info = $imgInfo;

        switch ($imgInfo[self::INFO_TYPE]) {
            case self::IMG_GIF :
                $this->img = imageCreateFromGif($pFile);
                break;
            case self::IMG_JPEG:
                $this->img = imageCreateFromJpeg($pFile);
                break;
            case self::IMG_PNG: 
                $this->img = imageCreateFromPng($pFile);
                break;
            default: 
                throw new \Exception('Тип файла не поддерживается', 26);
        }
        // func. load
    }
    
    public function save(string $pFile, $pImg=null){
        $img = $pImg ?: $this->img;
        switch ($this->info[self::INFO_TYPE]) {
            case self::IMG_GIF :
                imageGif( $img, $pFile);
                break;
            case self::IMG_JPEG:
                imageJpeg( $img, $pFile);
                break;
            case self::IMG_PNG: 
                imagePng( $img, $pFile);
                break;
        }
        // func. save
    }
    
    public static function isImage(string $pFile){
        $type = getImageSize($pFile);
        return in_array($type[self::INFO_TYPE], array(self::IMG_GIF, self::IMG_JPEG, self::IMG_PNG) );
        // func. isImage
    }

    public function getWidth() {
        return $this->info[self::INFO_WIDTH];
        // func. getWidth
    }

    public function getHeight() {
        return $this->info[self::INFO_HEIGHT];
        // func. getHeight
    }
// class imageProp
}
