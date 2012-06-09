<?php
namespace admin\library\mvc\comp\spl\objItem\category;

// Engine
use core\classes\render;

// Conf
use \DIR;

trait category{
    public function itemAction() {

        $tplFile = self::getTplFile();

        $this->view->setBlock('panel', $tplFile);
        $this->view->setTplPath(DIR::getTplPath('manager'));
        $this->view->setMainTpl('main.tpl.php');
        //$this->view->setMainTpl('main.tpl.php');
    }

}