<?php

namespace core\classes;

class upload {
    const FILE_SINGLE = 1;
    const FILE_MULTI = 2;

    public $typeUpload = self::FILE_SINGLE;
    public $maxFileSize = 0;
    public $fileName = null;
    public $distPath = '';
    public $isCallException = true;
    public $fileCount = 0;
    public $filter = null;
    
    public function __construct(integer $pType){
        self::setType($pType);
    }

    // TODO: удалить метод, сделать проверку $_FILES на array
    public function setType(integer $pType) {
        $this->typeUpload = $pType;
        return $this;
    }

// function setType

    public function setFileMaxSize(integer $pFileSize) {
        $this->maxFileSize = $pFileSize;
        return $this;
    }

// function setFileMaxSize

    /**
     * Установка имени файла или файлов
     * @param type $pFilename
     * @return \core\classes\upload 
     * @throws exception\upload
     */
    public function setFileName($pFilename) {
        if (is_array($pFilename) && $this->typeUpload == self::FILE_SINGLE) {
            throw new exception\upload('Нельзя задавать имя массивом и ставить self::FILE_SINGLE', 20);
        }

        if (is_string($pFilename) && $this->typeUpload == self::FILE_MULTI) {
            throw new exception\upload('Нельзя задавать имя строкой и ставить self::FILE_MULTI', 23);
        }
        $this->fileName = trim($pFilename);
        return $this;
    }

// function setFileName

    protected function isFileMaxSize(integer $pSize) {
        if ($this->maxFileSize && $this->maxFileSize < $pSize) {
            throw new \Exception('Превышен максимальный размер', 233);
        }
        return $this;
    }

// function isFileMaxSize

    public function setDistPath(string $pDistPath, $pDirRuels = 0777) {
        $this->distPath = $pDistPath;
        filesystem::mkdir($pDistPath, $pDirRuels);
        return $this;
    }

// function setDistPath

    public function _getFileParam(string $pVarName, $pParamName, $pListNum){
        
        if ($this->typeUpload == self::FILE_MULTI) {
            // Если нам надо получить конкретный файл
            if ($pListNum !== null) {
                return $_FILES[$pVarName][$pParamName][$pListNum];
            } else {
                // Количество файлов
                $iFileCount = count($_FILES[$pVarName][$pParamName]);
                // Бегаем по названиям
                for ($i = 0; $i < $iFileCount; $i++) {
                    $return[] = $_FILES[$pVarName][$pParamName][$i];
                }
                return $return;
            } // if ( $pListNum )
        } else { // if($this->typeUpload)
            $name = $_FILES[$pVarName][$pParamName];
            return $pListNum !== null ? $name : [0 => $name];
        } // if($this->typeUpload)
    }

    public function getFileOldName(string $pVarName, $pListNum=null) {
        return self::_getFileParam($pVarName, 'name', $pListNum);
    }

// function getFileOldName

    /**
     * Вызывать ли исключение при ошибки загрузки
     * @param boolean $pIsCallExc флаг, если true - будет вызыв Exception
     */
    public function setCallException(boolean $pIsCallExc) {
        $this->isCallException = $pIsCallExc;
    }

    protected function _filter($pFileData) {
        if ($this->filter) {
            $this->filter->run($pFileData);
        }
    }

    public function getFileTmpName(string $pVarName, $pListNum=null){
        return self::_getFileParam($pVarName, 'tmp_name', $pListNum);
    }

// function setCallException

    /**
     * Проверка по фильтрам и перенос файла в новую папку с новым именем
     * @param array $pFileData данные из $_FILES
     * @param string $pFileName Новое имя файла
     * @return type 
     */
    protected function _upload(array $pFileData, string $pFileName) {
        if (self::isUploadError($pFileData['error'])) {
            return;
        }
        // Не превышает ли размер файла допустимый
        self::isFileMaxSize($pFileData['size']);
        self::_filter($pFileData);
        self::moveUploadFile($pFileData['tmp_name'], $pFileName, $this->distPath);
    }

// function _upload

    public function getFileCount(string $pParamName) {
        if (!isset($_FILES[$pParamName]['name'])) {
            return 0;
        }
        if ($this->typeUpload == self::FILE_MULTI) {
            return count($_FILES[$pParamName]['name']);
        }
        return 1;
    }

// function getFileCount

    public function setFileMaxCount(integer $pFileCount) {
        $this->fileCount = $pFileCount;
        return $this;
    }

// end function setFileMaxCount ------------------------------------------------

    public function setFilter($pFilter) {
        $this->filter = $pFilter;
        return $this;
    }

// end function setFilter ------------------------------------------------------

    /**
     * Проверяет на ошибки и производит перемещение файла в директорию
     * @param string $pParamName имя параметра
     * @return boolean 
     */
    public function upload(string $pParamName) {
        if ($this->fileName == null) {
            throw new exception\upload('Не заданно имя файла. См. upload::setFileName', 25);
        }

        if (!isset($_FILES[$pParamName]['name'])) {
            return false;
        }

        $return = array();

        if ($this->typeUpload == self::FILE_MULTI) {

            $fileCount = $this->fileCount ? : self::getFileCount($pParamName);

            //$iFileCount = count($_FILES[$pParamName]['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                $fileData = array();
                foreach ($_FILES[$pParamName] as $key => $val) {
                    $fileData[$key] = $val[$i];
                }
                self::_upload($fileData, $this->fileName[$i]);
                $return[] = $this->fileName[$i];
            } // for($i)
        } else { // if (self::FILE_MULTI)
            self::_upload($_FILES[$pParamName], $this->fileName);
            $return[] = $this->fileName;
        }
        return $return;
    }

// end function upload ---------------------------------------------------------

    /**
     * Были ли при загрузке файла ошибка
     * @param string $pName название параметра
     * @param type $pCallExc вызывать ли Exception
     * @return boolean
     * @throws exception\filesystem в случае ошибок с ФС
     */
    public function isUploadError($pError) {
        $error = (int) $pError;
        if ($error !== UPLOAD_ERR_OK) {
            $uploadError = array(
                UPLOAD_ERR_INI_SIZE => 'Размер файла больше разрешенного директивой upload_max_filesize в php.ini',
                UPLOAD_ERR_FORM_SIZE => 'Размер файла превышает указанное значение в MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'Файл был загружен только частично',
                UPLOAD_ERR_NO_FILE => 'Не был выбран файл для загрузки',
                UPLOAD_ERR_NO_TMP_DIR => 'Не найдена папка для временных файлов',
                UPLOAD_ERR_CANT_WRITE => 'Ошибка записи файла на диск'
            );
            $msg = isset($uploadError[$error]) ? $uploadError[$error] : 'Неопределённая ошибка';
            if ($this->isCallException) {
                throw new exception\filesystem($msg, 88);
            }
            return true;
        }
        return false;
    }

// end function isUploadError --------------------------------------------------

    /**
     * Перемещаем загружаенный файл
     * @param string $pFileTmpName временное имя файла.<br/>
     * См. $_FILE['tmp_file']
     * @param string $pFileName Новое имя файла
     * @param string $pPathDist Директория куда перемещаем файл
     * @throws exception\filesystem 
     */
    public function moveUploadFile(string $pFileTmpName, string $pFileName, string $pPathDist) {
        $pPathDist = $pPathDist . $pFileName;
        if (!move_uploaded_file($pFileTmpName, $pPathDist)) {
            throw new exception\filesystem('Не удалось переместить файл', 142);
        }
    }

// end function moveUploadFile -------------------------------------------------
}