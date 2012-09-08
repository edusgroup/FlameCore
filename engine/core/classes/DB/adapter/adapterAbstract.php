<?php

namespace core\classes\DB\adapter;

//use \core\classes\Registry;

abstract class adapterAbstract {

    const USER = 0;
    const PWD = 1;
    const HOST = 2;
    const NAME = 3;
    const CHARSET = 4;
    
    //protected $connectionName = 'site';
    protected $_handleName = '';

    /**
     * Разъединение соединение с базой
     */
    public abstract function disconnect();

    public function setConnectionName($pName){
        $this->connectionName = $pName;
    }

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