<?php

namespace core\classes\DB\adapter;

//use \core\classes\Registry;

abstract class adapterAbstract {

    const USER = 0;
    const PWD = 1;
    const HOST = 2;
    const NAME = 3;
    const CHARSET = 4;
    
    protected $connectionName = 'site';

    /**
     * Разъединение соединение с базой
     */
    public abstract function disconnect();

    /**
     * Производит коннект к БД
     */
    //public static abstract function connect(\string $p_host, \string $p_user, \string $p_pwd, \string $p_db_name, \string $p_charset);
    
    public function setConnectionName($pName){
        $this->connectionName = $pName;
    }

    /*public function init(array $pParam) {
        $this->connect(
                $pParam[self::HOST], 
                $pParam[self::USER], 
                $pParam[self::PWD], 
                $pParam[self::NAME], 
                $pParam[self::CHARSET]
        );
        return $this;
    }*/

    /*public function setConnect($pDBHandle = NULL) {
       if (!$pDBHandle) {
            $pDBHandle = Registry::get('db', true)->getHandle();
        }
        if (!$pDBHandle)
            throw new \Exception('Нет подключения к БД', 23);
        $this->dbHandle = $pDBHandle;
    }*/


    /**
     * Получает одну запись из БД
     * @param \string $p_sql SQL для выполнения
     * @param integer $p_fetch_type типо возращаего значения
     * @return array
     */
    public abstract function fetchFirst($pFetchType);

    public abstract function fetchAll($pFetchType);

    public abstract function query();

    public abstract function insertId();

    public abstract function escape(\string $str);

    public abstract function addQuote($pValue);

    public abstract function affectedRows();
}

class DBException extends \Exception {
    
}

?>