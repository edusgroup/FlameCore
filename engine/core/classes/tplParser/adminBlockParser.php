<?php

namespace core\classes\tplParser;

// Core
use core\classes\filesystem;

/**
 * Description of tplBlockParser
 *
 * @author Козленко В.Л.
 */
class adminBlockParser {

    const RETURN_TYPE_NONE = null;
    const RETURN_TYPE_ECHO = 'echo';
    const RETURN_TYPE_RETURN = 'return';

    private $_returnType = null;

    private $_varibleHiddenList;
	
	private $_endHeadData = '';
    private $_bodyEndData = '';
    private $_dataJson = [];

    private $_currentName = '';
    private $_tplPath = '';
    private $_adminPath = '';

    public function __construct(string $pTplName, $isPrint=false) {
        self::parseBlock($pTplName, $isPrint);
        // func. __construct
    }
	
	public function blockdata($data){
		if ( isset($data['div'])){
            $obj = json_decode($data['div'], true);

            $style = isset($data['style'])?'style="'.$data['style'].'"' : '';

            if ( $this->_returnType != self::RETURN_TYPE_RETURN ){
			    echo "<div class='block-edit' block-param='{$data['div']}' $style>";
            }

            $nameTmp = $this->_currentName;
            $this->_currentName = $this->_currentName.'/'.$obj['name'];

            $fullname = substr($this->_currentName, 1);
            if ( isset($this->_dataJson['block'][$fullname])){
                $data = $this->_dataJson['block'][$fullname];

                if ( $data['type'] == 'text' ){
                    echo $data['html'];
                }else
                if ( $data['type'] == 'file'){
                    include($this->_tplPath.$data['file']);
                }

            }
            $this->_currentName = $nameTmp;

            if ( $this->_returnType != self::RETURN_TYPE_RETURN ){
                echo "</div>";
            }
		} // if ( isset($data['div']))
		// func. blockinfo
	}

    private function _echoVarible($pName, $pCaption, $pVal, $pAttrs=''){
        if ( $this->_returnType == self::RETURN_TYPE_RETURN ){
            echo $pVal;
        }else{
            echo "<span class='varible-edit' id='varEdit-$pName' name='$pName' caption='$pCaption' $pAttrs>".$pVal."</span>";
        }
        // func. echoVarible
    }

    public function varible($pName, $pTitle = '', $isHidden=false){
        $caption = $pTitle ?: $pName;
        if ( !$isHidden || $this->_returnType == self::RETURN_TYPE_RETURN  ){
            $varVal = null;
            if ( isset($this->_dataJson['varible']) ){
                foreach($this->_dataJson['varible'] as $varList){
                    foreach( $varList as $name=>$val){
                        if ( $name == $pName ){
                            $varVal = $val;
                            break;
                        } // if
                    } // foreach
                    if ( $varVal != null ){
                        break;
                    } // if
                } // foreach Поиск значения переменной
            } // if ( isset($this->_dataJson['varible']) )
            self::_echoVarible($pName, $caption, $varVal);
        }else{
           $this->_varibleHiddenList[] = ['name' => $pName, 'caption' => $caption ];
        }
        // func. varible
    }

    public function setBlockJson($pJson){
        $this->_dataJson = $pJson;
        // func. setBlockJson
    }

    public function parseBlock(string $pTplName, $pReturnType=self::RETURN_TYPE_NONE){
        if ( !$pTplName ){
            return;
        }
        $this->_returnType = $pReturnType;
        $this->_varibleList = [];
        if (is_readable($pTplName)) {
            switch($pReturnType){
                case self::RETURN_TYPE_NONE:
                    ob_start();
                    include($pTplName);
                    ob_clean();
                    return;
                case self::RETURN_TYPE_ECHO:
                    include($pTplName);
                    return;
                case self::RETURN_TYPE_RETURN:
                    ob_start();
                    include($pTplName);
                    return ob_get_clean();
            } // switch
        } // if
        // func. parseBlock
    }


    public function __get($pName) {
        
    }

    public function __call($pName, $pParam) {
        
    }
	
	public function setEndHeadData($data){
		$this->_endHeadData = $data;
	}

    public function setBodyEndData($data){
        $this->_bodyEndData = $data;
    }

    public function setTplPath($path){
        $this->_tplPath = $path;
    }

    public function setAdminResPath($path){
        $this->_adminPath = $path;
    }

    public function block($pName, $pTitle = '') {
        if (in_array($pName, ['::bodyBegin', '::headBegin'])) {
            return;
        }
		switch( $pName ){
            case '::headEnd':
			    echo $this->_endHeadData;
                break;
            case '::bodyEnd':
                echo $this->_bodyEndData;
                foreach($this->_varibleHiddenList as $data ){
                    self::_echoVarible($data['name'], $data['caption'], '', 'class="hidden"');
                }
                break;
            case '::adminHead':
                if ( $this->_returnType != self::RETURN_TYPE_RETURN ){
                    filesystem::printFile($this->_adminPath.'adminHead.html');
                }
                break;
            case '::adminBody':
                if ( $this->_returnType != self::RETURN_TYPE_RETURN ){
                    filesystem::printFile($this->_adminPath.'adminBody.html');
                }
                break;
		} // switch
        // func. block
    }
// class tplBlockParser
}