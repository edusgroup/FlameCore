<?php

namespace core\classes\image;

/**
 * Description of filter
 *
 * @author Козленко В.Л.
 */
class filter implements \core\classes\interfaces\filter {

    public $maxWidth = null, $maxHeight = null;

    public function setMaxWidth($pWidth) {
        $this->maxWidth = $pWidth;
        return $this;
    }

    public function setMaxHeight($pHeight) {
        $this->maxHeight = $pHeight;
        return $this;
    }

    public function run($pFileData) {
        $imgProp = new imageProp();
                
        $imgProp->load($pFileData['tmp_name']);

        if ($this->maxWidth != NULL && $this->maxWidth < $imgProp->getWidth()) {
            throw new \Exception('Ширина не допустима', 23);
        }
        
        if ($this->maxHeight != NULL && $this->maxHeight < $imgProp->getHeight()) {
            throw new \Exception('Высота не допустима', 29);
        }
    }

}