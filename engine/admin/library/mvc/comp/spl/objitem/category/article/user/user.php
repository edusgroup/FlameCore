<?php
namespace admin\library\mvc\comp\spl\objItem\category;
use core\classes\render;

trait category{
    public function itemAction() {
        $this->view->setRenderType(render::NONE);
        echo 'user logic class small';

    }
}