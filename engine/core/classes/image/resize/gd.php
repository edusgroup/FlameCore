<?php
namespace core\classes\image;

/**
 * @author Козленко В.Л.
 */
class resize {
    // TODO: Добавить описание констант
    const PROPORTIONAL = 1;
    const SQUARE = 2;
    const FREE = 3;
    const PROPORTIONAL_SQUARE = 4;

    public $width = 0;
    public $height = 0;
    public $type = self::PROPORTIONAL;
    
    /**
     * Параметры изображения
     * @var imageProp
     */
    protected $imageProp;

    /**
     * Цвет заливки
     * @var integer 
     */
    public $fillColor = 0x000000;

    /**
     * Установка желаемой ширины
     * @param integer $pWidth необходимая ширина
     */
    public function setWidth($pWidth) {
        $this->width = $pWidth;
        return $this;
        // func. setWidth
    }

    /**
     * Установка желаемой высоты
     * @param integer $pHeight необходимая длина
     */
    public function setHeight($pHeight) {
        $this->height = $pHeight;
        return $this;
        // func. setHeight
    }

    public function setWidthHeight($pWidth, $pHeight) {
        $this->width = $pWidth;
        $this->height = $pHeight;
        return $this;
        // func. setWidthHeight
    }

    public function setFillColor($pFillColor) {
        $this->fillColor = $pFillColor;
        return $this;
        // func. setFillColor
    }

    /**
     * Установка типа маштабирования<br/>
     * @param type $pType тип маштабирования.<br/>
     * PROPORTIONAL - пропорционально<br/>
     * SQUARE - подгонка и <b>резка</b> до квардрата<br/>
     * FREE - жёсткая подготка по размеры 
     * @return ссылку на самого себя 
     */
    public function setType($pType) {
        $this->type = $pType;
        return $this;
        // func. setType
    }

    protected function _math() {

        // Если не заданы ширина и длинна, то это плохо
        if (!$this->height && !$this->width) {
            throw new \Exception('Задайте длину и ширину');
        }


        if ($this->height && $this->width && $this->type != self::SQUARE) {
            $this->type = self::FREE;
        }
        
        $widthOld = $this->data->widthOld;
        $heightOld = $this->data->heightOld;

        // TODO: Переписать на функции
        if ($this->type == self::PROPORTIONAL || $this->type == self::PROPORTIONAL_SQUARE ) {
            // Если задана ширина, но не задана высота
            if (!$this->height){
                $this->data->heightNew = floor($heightOld * $this->width / $widthOld);
            }

            // Если задана высота, но не задана ширина
            if (!$this->width) {
                $this->data->widthNew = floor($widthOld * $this->height / $heightOld);
            }
            
            if ( $this->type == self::PROPORTIONAL_SQUARE){
                if (!$this->height){
                    
                }
            }
            
            return true;
        }//if(self::PROPORTIONAL)
        else
        if ($this->type == self::SQUARE){
            $this->data->widthNew = $this->width ? : $this->height;
            $this->data->heightNew = $this->height ? : $this->width;

            $this->data->widthOld = $widthOld > $heightOld ? $heightOld : $widthOld;
            $this->data->heightOld = $widthOld > $heightOld ? $heightOld : $widthOld;
            
            // Если изображение меньше, чем требуемые параметры
            if ($this->data->widthNew > $this->data->widthOld 
                    && $this->data->heightNew > $this->data->heightOld){
                return false;
            }

            $this->data->xOld = $widthOld > $heightOld ? floor(( $widthOld - $heightOld ) >> 1) : 0;
            $this->data->yOld = $widthOld < $heightOld ? floor(( $heightOld - $widthOld ) >> 1) : 0;
            return true;
        }//if(self::SQUARE)
        else
        if ($this->type == self::FREE) {
            $koefWidth = $widthOld / $this->width;
            $koefHeight = $heightOld / $this->height;
            if ($koefWidth > $koefHeight) {
                $realHeigth = $heightOld / $koefHeight;
                $this->data->yNew = floor(( $this->data->heightNew - $realHeigth ) >> 1);
                $this->data->heightNew = $realHeigth;
            } else {
                $realWidth = $widthOld / $koefWidth;
                $this->data->xNew = floor(( $this->data->widthNew - $realWidth ) >> 1);
                $this->data->widthNew = $realWidth;
            }
            return true;
        } // if(self::FREE)

        throw new \Exception('Неизвестный тип. См. метод setType($type)');
        // func. _math
    }
    
    /**
     * Ресайз изображения
     * @param string $pFileDist полное имя файла, где храним изображение
     */
    public function _resize(string $pFileDist) {
        // Ширана нового изображения
        $width = $this->data->widthNew;// + $this->data->xNew << 1;
        // Высота нового изображения
        $height = $this->data->heightNew;// + $this->data->yNew << 1;
        // Создаём новое изображение по размерам
        $imgDist = imageCreateTrueColor($width, $height);
        // Заливка выбранным цветом. По умолчанию чёрный
        imagefilledrectangle($imgDist, 0, 0, $width - 1, $height - 1, $this->fillColor);
        
        //imageCopyReSized && imageCopyReSampled
        // Маштабируем
        $imgSource = $this->imageProp->getImage(); 
        imageCopyReSampled($imgDist, $imgSource, $this->data->xNew, $this->data->yNew, 
                    $this->data->xOld, $this->data->yOld, 
                    $width, $height, 
                    $this->data->widthOld, $this->data->heightOld);
        // TODO: сделать универсально, любой тип данных
        //imageJpeg($imgDist, $pFileDist);
        $this->imageProp->save($pFileDist, $imgDist);
        imageDestroy($imgDist);
        // func. _resize
    }

    protected function _initSize($pFileSource) {
        $this->imageProp = new imageProp($pFileSource);
        $width = $this->imageProp->getWidth();// imagesx($img);
        $height = $this->imageProp->getHeight();
        
        $this->data = (object) ['xNew' => 0, 'yNew' => 0
                    , 'widthNew' => $this->width, 'heightNew' => $this->height
                    , 'xOld' => 0, 'yOld' => 0
                    , 'widthOld' => $width, 'heightOld' => $height];
        // func. _initSize
    }

    public function resize($pFileSource, $pFileDist) {
        self::_initSize($pFileSource);
        if ( ! self::_math() ){
            copy($pFileSource, $pFileDist);
        }else{
            self::_resize($pFileDist);
        }
        return true;
        // func. resize
    }
// class resize
}