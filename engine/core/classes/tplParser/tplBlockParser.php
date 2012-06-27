<?php

namespace core\classes\tplParser;


/**
 * Description of tplBlockParser
 *
 * @author Козленко В.Л.
 */
class tplBlockParser {
    // Список блоков
    private $_blockList;
    // Список переменных
    private $_varibleList;

    public function __construct(string $pTplName) {
        self::parseBlock($pTplName);
        // func. __construct
    }

    public function varible($pName, $pTitle = ''){
        $this->_varibleList[$pName] = $pTitle;
    }

    public function parseBlock(string $pTplName){
        if ( !$pTplName ){
            return;
        }
        $this->_blockList = [];
        $this->_varibleList = [];
        if (is_readable($pTplName)) {
            ob_start();
            include($pTplName);
            ob_clean();
        }
    }

    public function getBlockList(){
        return $this->_blockList;
    }

    public function getVaribleList(){
        return $this->_varibleList;
    }

    public function __get($pName) {
        
    }

    public function __call($pName, $pParam) {
        
    }

    public function block($pName, $pTitle = '') {
        if (in_array($pName, ['bodyBegin', 'head', 'bodyEnd', 'scriptStatic', 'scriptDyn'])) {
            return;
        }
        $this->_blockList[$pName] = $pTitle;
        // func. block
    }
// class tplBlockParser
}

?>