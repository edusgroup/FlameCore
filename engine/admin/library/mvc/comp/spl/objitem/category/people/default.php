<?php
namespace admin\library\mvc\comp\spl\objItem\category;

// Engine
use core\classes\render;

trait category{
    public function itemAction() {
        $this->view->setRenderType(render::JSON);
        print 'trait category people';
        //$this->view->setMainTpl('main.tpl.php');
    }

}