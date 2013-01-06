<?php

namespace core\classes\DB\adapter;

use core\classes\DB\DB as DBCore;

//TODO: Переписать на подготовленные запросы
class adapter extends adapterAbstract {
    protected $sSQL;
    private $functionSelect;
    // Handle на коннект базы данных
    //private $dbHandle;

    const FETCH_ASSOC = MYSQLI_ASSOC;
    const FETCH_NUM = MYSQLI_NUM;
    const FETCH_BOTH = MYSQLI_BOTH;
    const FETCH_OBJECT = 10;
    
    /**
     * Комментарий к запросу
     * @var string 
     */
    protected $_comment;

    /**
     * Производит дисконнект от БД
     */
    public function disconnect() {
        if ($this->getHandle()){
            $this->getHandle()->close();
        }
        // func. disconnect
    }

    public function setHandleName($pHandleName){
        $this->_handleName = $pHandleName;
        // func. setHandle
    }
    
    public function getHandle(){
        $handleName = $this->_handleName ?: 'site';
        $handle = DBCore::getHandle($handleName);
        if ( !$handle ){
            $param = DBCore::getParam($handleName);
            if ( !$param ){
                throw new \Exception('Not use param: '.$handleName.' in DB');
            }
            $handle = self::connect(
                    $param[self::HOST], 
                    $param[self::PORT],
                    $param[self::USER],
                    $param[self::PWD], 
                    $param[self::NAME],  
                    $param[self::CHARSET],
                    $param[self::SOCKET]
               );
            DBCore::addHandle($handleName, $handle);
        } // if
        return $handle;
        // func. getHandle
    }
    
    public function affectedRows(){
        return $this->getHandle()->affected_rows;
    }

    /**
     * Производит коннект к БД
     * @param \string $p_host Адрес БД
     * @param \string $p_user Имя пользователя
     * @param \string $p_pwd  Пароль пользователя
     * @param \string $p_db_name Название БД
     * @param \string $p_charset Используемая кодировка
     * @return DBHandle 
     */
    public static function connect($pHost,$pPort, $pUser, $pPwd, $pDbName, $p_charset, $pSocket) {
        $handle = \mysqli_connect($pHost, $pUser, $pPwd, $pDbName, $pPort, $pSocket);
        if ( !$handle ){
            throw new DBException('DB not connect', 1);
        }
        if ($handle->connect_error){
           throw new DBException($handle->connect_error, $handle->connect_errno);
        }
        $handle->set_charset($p_charset);
        return $handle;//$this->dbHandle = $handle;
    }
    
    public function parseFunction(string $pSQL){
        $this->sSQL = 'SET @result = '.$pSQL.';';
        $this->functionSelect = ',@result as result';
        return $this;
    }
    
    public function parseProcedure(string $pSQL){
        $this->functionSelect = '';
        $this->sSQL = 'CALL '.$pSQL.';';
        return $this;
        
    }
    
    public function bindOut($pName){
        $pNameOut = '@'.substr($pName, 1);
        $this->functionSelect .= ','.$pNameOut.' AS '.substr($pName, 1);
        $this->sSQL = str_replace($pName, $pNameOut, $this->sSQL);
        return $this;
    }
    
    public function bindInOut($pName, $pValue){
        $value = self::addQuote($pValue);
        $pNameOut = '@'.substr($pName, 1);
        $this->sSQL = "SET $pNameOut=$value;". $this->sSQL;
        $this->functionSelect .= ','.$pNameOut.' AS '.substr($pName, 1);
        $this->sSQL = str_replace($pName, $pNameOut, $this->sSQL);
        return $this;
    }
    
    public function bindIn(string $pName, $pValue){
        $value = self::addQuote($pValue);
        $this->sSQL = str_replace($pName, $value, $this->sSQL);
        return $this;
    }
    
    public function exec(){
        $this->functionSelect = substr($this->functionSelect, 1);
        $this->sSQL .= 'SELECT '.$this->functionSelect;
        $dbHandle = $this->getHandle();
        $res = $dbHandle->multi_query($this->sSQL);
        if (!$res){
            throw new DBException($dbHandle->error . "\nSQL: " . $this->sSQL, $dbHandle->errno);
        }
        $this->sSQL = '';
        $dbHandle->next_result();
        $result = $dbHandle->store_result();
        if ($result){
            return $result->fetch_object();
        }
    }

    /**
     * Получает одну запись из БД
     * @param string $pSQL SQL для выполнения
     * @param type $pFetchType типо возращаего значения. По умолчанию OBJECT
     * @return array
     */
    public function fetchFirst($pFetchType = self::FETCH_ASSOC) {
        $result = self::query();
        if ( $pFetchType != self::FETCH_OBJECT ){ 
            return $result->fetch_array($pFetchType);
        }
        return $result->fetch_object();
    }

    /**
     * Экранирует ковычки
     * @see function addQuote($pValue)
     * @param \string $str
     * @return type 
     */
    public function escape(\string $str) {
        return $this->getHandle()->real_escape_string($str);
    }

    public function addQuote($pValue) {
        return is_string($pValue) ?  '\'' . self::escape($pValue) . '\'' : $pValue;
    }

    /**
     * Получаем все данные по SQL запросу
     * @param string $p_sql SQL зарпос
     * @param integer $pFetchType тип возвращаемых значений
     * @return mixed данные ввиде массива или объекта 
     */
    public function fetchAll($pFetchType=self::FETCH_ASSOC) {
        // Получение всех данных
        if ( $pFetchType != self::FETCH_OBJECT ){
            return self::query()->fetch_all($pFetchType);
        }
        return self::_fetchAllObj();
    }
    
    private function _fetchAllObj(){
        $result = self::query();
        $return = [];
        if ( $result ){
            while($tmp = $result->fetch_object()){
                if ( !$tmp )
                    break;
                $return[] = $tmp;
            }
        }
        return $return;
    }
    
        
    public function comment(string $pText){
        $this->_comment = $pText;
        return $this;
    }

    /**
     * Выполнение запроса в БД
     * @throw DBException 
     * @return type 
     */
    public function query($pSql=null) {
        $sql = $pSql ?: $this->sSQL;
       
        if ( $this->_comment ){
            $sql .= '#' . $this->_comment;
        }
        $result = $this->getHandle()->query($sql);
        if (!$result){
            throw new DBException($this->getHandle()->error . "\nSQL: " . $sql, $this->getHandle()->errno);
        }
        $this->_comment = '';
        $this->sSQL = '';
        return $result;
    }
    
    /**
     * Возвращает созданный ID
     * @return integer
     */
    public function insertId(){
        return (int)$this->getHandle()->insert_id;
    }

}