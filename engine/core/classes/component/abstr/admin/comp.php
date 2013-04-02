<?php

namespace core\classes\component\abstr\admin;

// Engine
use core\classes\request;
use core\classes\filesystem;
use core\classes\comp as compCore;

// Conf
use \DIR;
use \CONSTANT;

//ORM
use ORM\tree\componentTree;
use ORM\tree\compContTree;

abstract class comp extends \core\classes\mvc\controllerAbstract {

    /**
     * Настройки компонента
     * @var array
     */
    public $objProp;

    /**
     * ID контента
     * @var integer
     */
    public $contId;

    /**
     * ID компоннета
     * @var integer
     */
    public $compId;

    /**
     * Имя файла шаблона
     * @var string
     */
    public $tplFile;

    public function __call($pName, $pArgs) {
        call_user_func_array([$this->controller, $pName], $pArgs);
        // func. __call
    }

    // class comp
}