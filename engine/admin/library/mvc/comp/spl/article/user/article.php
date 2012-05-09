<?php

namespace admin\library\mvc\comp\spl\article\user;

use core\classes\render;


class article extends \core\classes\component\abstr\admin\comp {

    public function __construct(string $pTplPath, string $pThemeResUrl) {
        parent::__construct($pTplPath, $pThemeResUrl);
    }

    public function indexAction() {
        $this->view->setRenderType(render::NONE);
        print 'article';
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