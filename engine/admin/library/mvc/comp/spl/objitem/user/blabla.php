<?php

namespace admin\library\mvc\comp\spl\objItem\user;

use core\classes\render;


class blabla extends \core\classes\component\abstr\admin\comp {

    public function __construct(string $pTplPath, string $pThemeResUrl) {
        parent::__construct($pTplPath, $pThemeResUrl);
    }

    public function indexAction() {
        $this->view->setRenderType(render::NONE);
        print 'blabla';
    }

    /**
     * Удаляем контент
     */
    public function rmItem() {
    
    }



    public function init() {
        
    }
	
	public function getTableOrm() {
    }

    public function getTableData($pContId) {
	}

}

?>