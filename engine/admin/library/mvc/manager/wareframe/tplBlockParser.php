<?php

namespace admin\library\mvc\manager\wareframe;

/**
 * Description of tplBlockParser
 *
 * @author Козленко В.Л.
 */
class tplBlockParser {

    public $list;

    public function __construct(string $pTplName) {
        $this->list = array();
        if (is_readable($pTplName)) {
            ob_start();
            include($pTplName);
            ob_clean();
        }
        // func. __construct
    }

    public function __get($pName) {
        
    }

    public function __call($pName, $pParam) {
        
    }

    public function block($pName, $pTitle = '') {
        if (in_array($pName, array('bodyBegin', 'head', 'bodyEnd', 'scriptStatic', 'scriptDyn'))) {
            return;
        }
        $this->list[$pName] = $pTitle;
        // func. block
    }
// class tplBlockParser
}

?>