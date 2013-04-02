<?php
namespace core\classes;

use \SITE;
use \DIR;

/**
 * Шаблонизатор
 *
 * @author Козленко В.Л.
 */
class render extends html\element{

    /** @var список переменных */
    protected $arVarible = [];
    
    protected $contentType = 'Content-Type: text/html; charset=UTF-8';

    /** @var список блоков */
    private $arBlock = [];

    //Виды рендеринга Битовая матрица
    /**
     * Рендеринг JSON данных. Результативные данные
     * необходимо записать следующим образом 
     * self::setVar('json', array());
     */
    const JSON = 1;
    /**
     * Пасинг шаблона 
     */
    const PARSE = 2;
    /**
     * Без рендеринг 
     */
    const NONE = 4;
    
    // Тип где находится файл с шаблонам
    /**
     * Шаблон находится в папке со всеми шаблона
     */
    const P_SITE = 1;
    /**
     * Шаблона находится где угодно. Необходимо полное имя файла
     */
    const P_FREE = 2;

    // Путь до главного шаблона
    protected $renderType = self::PARSE;
    protected $pathType = self::P_SITE;
    protected $siteTplPath = '';
    protected $themeResUrl = '';
    
    /**
     * Имя главного файла шаблона
     * @var string 
     */
    protected $mainTplFile = '';

    public function __construct(string $pSiteTplPath, string $pThemeResUrl) {
        //self::setPath($pSiteTplPath, $pThemeResUrl);
        self::setTplPath($pSiteTplPath);
        self::setThemeResUrl($pThemeResUrl);
    }

    public function setTplPath(string $pTplPath){
        $this->siteTplPath = $pTplPath;
    }
    
    public function getTplPath(){
        return $this->siteTplPath;
    }
    
    public function setThemeResUrl(string $pThemeResUrl){
        $this->themeResUrl = $pThemeResUrl;
    }

    public function setRenderType(integer $pType) {
        $this->renderType = $pType;
    }

    public function clear() {
        $this->arVarible = [];
        $this->arBlock = [];
        return $this;
    }

    public function res($pFile) {
        return $this->themeResUrl. $pFile;
    }

    /**
     * Устанавливает переменную для шаблона. 
     * По умолчанию string переменная обрабатываеся функцией<br/>
     * htmlspecialchars т.е. экранирует спец. символы
     * @param string $pName название переменной
     * @param mixed $pValue значение переменной
     * @param boolean $pSafe нужно ли экранировать строки, 
     * если переменная строка
     */
    public function setVar(string $pName, $pValue, $pSafe=true) {
        if ($pSafe && is_string($pValue)) {
            $pValue = htmlspecialchars($pValue);
        }
        $this->arVarible[$pName] = $pValue;
        return $this;
    }

    public function get($pName, $pDefault='') {
        if (isset($this->arVarible[$pName])){
            return $this->arVarible[$pName];
        }
        return $pDefault;
    }

    public function setJSON(string $pName, $pValue) {
        $this->arVarible[$pName] = \json_encode($pValue);
        return $this;
    }

    public static function url(string $pURL, $pQuery='') {
        $arr = \explode('/', $pURL);

        $return = '?cmd=' . $arr[0]; //
        $arrCount = count($arr);
        for ($i = 1; $i < $arrCount; $i++) {
            $return .= '&a' . $i . '=' . $arr[$i];
        }
        $return .= ($pQuery ? '&' . $pQuery : '');
        return $return;
    }
	
	public static function tplVarible($pName){
		return isset(dbus::$tplVarible[$pName])?dbus::$tplVarible[$pName]:'';
		// func. tplVarible
	}

    /**
     * Заносит соотвествие в какой блок какой файл грузить<br/>
     * Если имя блока начинается со знака @, то не обходимо задать абсалютное имя файла
     * @see includeBlock Для подзгрузки блока
     * @param string $pBlockName имя блока
     * @param string $pFile  имя файла
     */
    public function setBlock(string $pBlockName, string $pFile) {
        // Если указано полный путь (windows или linux )
        if ( substr($pFile, 1, 2) == ':/' || $pFile[0] == '/' ){
            $file = $pFile;
        }else{
            $file = $this->siteTplPath . $pFile;
        }
        $this->arBlock[$pBlockName] = $file;
        return $this;
    }

    /**
     * Установка главного шаблона
     * @param string $pFile имя главного шаблона
     * @return render ссылка на самого себя
     */
    public function setMainTpl(string $pFile){
        $this->mainTplFile = $this->siteTplPath . $pFile;
        return $this;
    }

    /**
     * Подгружает указанный блок<br/>
     * Если имя блока начинается со знака @, то не обходимо задать абсалютное имя файла
     * @see setBlock для задания соотвествия между блоком и именем файла
     * @param string $pBlockName 
     */
    protected  function includeBlock(string $pBlockName) {
        // Есть ли такой блок
        if (!(isset($this->arBlock[$pBlockName]) && $this->arBlock[$pBlockName]))
            return;
        $filename = $this->arBlock[$pBlockName];
        // Есть ли такой файл
        //if (!is_file($filename))
        //    throw new \Exception('File: ' . $filename . ' not include', 31);
        include($filename);
    }
    
    /**
     * Ввыводит файл в поток вывода
     * @param string $pName имя файла или имя параметра
     * @param type $pNameIsFile если стоит true, то это имя параметра,
     * иначе имя файла
     * @return void 
     */
    public function loadFile(string $pName, $pNameIsFile = true) {
        $file = $pName;
        if (!$pNameIsFile)
            $file = self::get($pName);
        if (!$file)
            return;
        $fr = fopen($file, 'r');
        if (!$fr)
            return;
        fpassthru($fr);
        fclose($fr);
		return true;
    }

    /**
     * Установка заголовка Content-Type<br />
     * По умолчанию используется Content-Type: text/html; charset=UTF-8<br />
     * если $pValue == null или '' то заголовок не будет установлен<br />
     * @param string $pValue заголовок
     * @return render
     */
    public function setContentType($pValue){
        $this->contentType = $pValue;
        return $this;
    }

    public function renderToFile($pFile, $pPrefix='<?php '){
        self::setContentType(null);
        ob_start();
        $this->render();
        $data = ob_get_clean();

        $fw = fopen($pFile, 'w');
        if ( !$fw ){
            return false;
        }
        if ( !fwrite($fw, $pPrefix.$data) ){
            return false;
        }

        fclose($fw);
        return true;
        // func. renderToFile
    }

    public function render(){
        $renderType = $this->renderType;
        if ($renderType & self::JSON) {
            $json = self::get('json', array());
            $msgbox = self::get('msgbox');
            if ($msgbox){
                $json = \array_merge($json, array('msgbox' => $msgbox));
            }
            echo \json_encode($json);
            return;
        } // if(JSON)
        if ($renderType & self::PARSE) {
            if ( $this->contentType ){
                header($this->contentType);
            }
            $filename = $this->mainTplFile;
            if ( !include($filename) ){
                throw new \Exception('render: File: [' . $filename . '] not include', 31);
            }
        } // if(PARSE)
        // func. render
    }

}