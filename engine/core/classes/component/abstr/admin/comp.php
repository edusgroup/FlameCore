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

    public function __call($pName, $pArgs) {
        call_user_func_array(array($this->controller, $pName), $pArgs);
        // func. __call
    }

    public function getTplFile() {
        $tplType = $this->objProp['tplType'];
        $category = $this->objProp['category'];
        $categoryDir = '';
        if ( $category ){
            $categoryDir = 'category/'.$category.'/';
        }
        switch ($tplType) {
            case compCore::DEFAULT_VALUE:
                $defaultName = !$category ? $this->objProp['classname'] : ('category/'.$category.'/'.$category);
                  return $defaultName . '.tpl.php';
            case 'user':
                return $categoryDir.'user/' . $this->objProp['tplUserFile'];
            case 'ext':
                return $categoryDir.'ext/' . $this->objProp['tplExtFile'];
            case 'builder':
                throw new \Exception('builder no create');
        }
        throw new \Exception('Не известнный тип tplType');
        // func. getTplFile
    }

    /**
     * Возврашает список табличных данных, пренадлежащех категории $pContId
     * Может быть пустым. Нужно только если onlyFolder=1
     * @param integer $pContId ID родителя(категории)
     */
    public abstract function getTableData($pContId);

    /**
     * Возврашает имя записи в таблице
     * Может быть пустым. Нужно только если onlyFolder=1
     * @param integer $pTableId ID таблицы
     */
    public abstract function getTableOrm();

    //public abstract function blockItemShowAction();

    // class comp
}