<?php

namespace core\classes\builder;

/**
 * Description of tplBlockCreator
 *
 * @author Козленко В.Л.
 */
class tplBlockCreator {

    private $siteTplPath = '';
    private $themeResUrl = '';
    private $blockFileList = null;
    private $blockItemList = null;
    // Список ссылок на другие блоки. Формат [{Текущий блок}]=[{Куда ссылается}]
    private $blockLinkList = null;
    private $blockId = -1;
    // Буффер Конечного созданного кода
    private $codeBuffer = '';
    // Данные блока Head
    private $headData = '';
    // Данные блока script
    private $scriptStaticData = '';
    private $scriptDynData = '';
    private $_varibleList = [];
	private $afterBody = '';

    public function setHeadData($pData) {
        $this->headData = $pData;
        // func. setHeadData
    }

    public function setVaribleList($pVarList){
        $this->_varibleList = $pVarList;
    }

    public function setScriptData($pStaic, $pDyn) {
        $this->scriptStaticData = $pStaic;
        $this->scriptDynData = $pDyn;
    }

    public function __construct(string $pSiteTplPath, string $pThemeResUrl, $pBlockId) {
        $this->siteTplPath = $pSiteTplPath;
        $this->themeResUrl = $pThemeResUrl;
        $this->blockId = $pBlockId;
    }

    public function setBlockFileList($pBlockFileList) {
        $this->blockFileList = $pBlockFileList;
        // func. setBlockFileList
    }

    public function setBlockItemList($pBlockItemList) {
        $this->blockItemList = $pBlockItemList;
        // func. setBlockItemList
    }

    public function setBlockLinkList($pBlockLinkList){
        $this->blockLinkList = $pBlockLinkList;
        // func. setBlockLinkList
    }

    public function __get($pName) {

    }

    public function __call($pName, $pParam) {

    }

    public function varible($pName, $pTitle = ''){
        return isset($this->_varibleList[$pName])?$this->_varibleList[$pName]:'';
        // func. varible
    }
	
	public function setBodyBeginHtml($pBodyBegin){
		$this->bodyBegin = $pBodyBegin;
		// func. setAfterBody
	}

    protected function block($pName, $pTitle = '') {
		switch($pName){
			case 'head':
				echo $this->headData;
				echo '<script>var dbus={};var importResList={"js":[],"css":[]};</script>';
				return;
			case 'scriptStatic':
				echo $this->scriptStatData;
				return;
			case 'scriptDyn':
				echo $this->scriptDynData;
				return;
			case 'bodyBegin': 
				echo $this->bodyBegin;
				return;
		}
 
        $key = $pName . ':' . $this->blockId;
        // Проверяем может блок является ссылкой на другой блок, если да, то получаем новый номер блока
        $key = isset($this->blockLinkList[$key])?$this->blockLinkList[$key]:$key;
        // echo '<!--BEGIN['.$key.']-->';
        if (isset($this->blockFileList[$key])) {
            $oldId = $this->blockId;
            $this->blockId = $this->blockFileList[$key]['id'];
            self::render($this->blockFileList[$key]['file']);
            $this->blockId = $oldId;
        } else
            if (isset($this->blockItemList[$key])) {
                foreach ($this->blockItemList[$key] as $item) {
                    echo '<?php ' . $item['class'] . '::' . $item['method'] . $item['callParam'] . ' ?>' . PHP_EOL;
                }
            } // if
        // func. block
    }

    private function render($pTplName) {
        $filename = $this->siteTplPath . substr($pTplName, 1);
        if (is_readable($filename)) {
            include($filename);
        } else {
            ob_end_clean();
            throw new \Exception('Not read: ' . $this->siteTplPath . $pTplName, 239);
        }
        // func. render
    }

    public function getCodeBuffer() {
        return $this->codeBuffer;
    }

    public function start($pTplName) {
        ob_start();
        self::render($pTplName);
        $this->codeBuffer .= ob_get_clean();
        // func. start
    }

    // class tplBlockCreator
}