<?php

namespace admin\library\mvc\comp\spl\article\ext;

use core\classes\render;


class ext extends \core\classes\component\abstr\admin\comp {

    public function __construct(string $pTplPath, string $pThemeResUrl) {
        parent::__construct($pTplPath, $pThemeResUrl);
    }

    public function indexAction() {
        $this->view->setRenderType(render::NONE);
        print 'ext';
    }

    /**
     * Удаляем контент
     */
    public function rmItem() {
    
    }


    public function init() {
        
    }
	

    public function getTableData($pContId) {
        
    }

    public function getTableOrm() {
        
    }


}

?>