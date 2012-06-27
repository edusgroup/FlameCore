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
    //private $blockItemInitList = null;
    private $blockId = -1;
    // Буффер Конечного созданного кода
    private $codeBuffer = '';
    // Данные блока Head
    private $headData = '';
    // Данные блока script
    private $scriptStaticData = '';
    private $scriptDynData = '';
    private $_varibleList = [];

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
    }

    /*public function setBlockItemInitList($pBlockItemInitList){
        $this->blockItemInitList = $pBlockItemInitList;
    }*/

    public function __get($pName) {

    }

    public function __call($pName, $pParam) {

    }

    public function varible($pName, $pTitle = ''){
        echo isset($this->_varibleList[$pName])?$this->_varibleList[$pName]:'';
        // func. varible
    }

    protected function block($pName, $pTitle = '') {
        if ($pName == 'head') {
            echo $this->headData;
            echo '<script>var dbus={};</script>';
            return;
        } else
            if ($pName == 'scriptStatic') {
                echo $this->scriptStatData;
                return;
            } else
                if ($pName == 'scriptDyn') {
                    echo $this->scriptDynData;
                    return;
                }

        $key = $pName . ':' . $this->blockId;
        //echo '<!--BEGIN['.$key.']-->';
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

?>