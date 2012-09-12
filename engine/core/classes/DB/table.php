<?php
namespace core\classes\DB;
/**
 * ORM таблицы
 */
class table extends adapter\adapter {
    //private static $aFields = [];
    private $sTable = '';
    //private static $aReturn = [];
    const ASC = 1, DESC = 2;
    const FIELD_WHERE = 1,/*' AND '*/ FIELD_SET = 2/*','*/;
    private $isUserUnion = false;
        
    //public $exception = false;
    
    public function __construct($pTable=''/*, $pDBHandle=null*/){
       // self::setConnect($pDBHandle);
       self::setTable($pTable);
    }

    public function setTable($pTable){
        $this->sTable = $pTable ? $pTable : $this::TABLE;
        // func. setTable
    }
    
    /**
     * Очистка буффера с SQL
     */
    public function clear(){
        $this->sSQL = '';
        $this->isUserUnion = false;
    }
    
    public static function toNullInt($pVal){
        return $pVal == 'null' ? NULL : (float)$pVal;
    }
    
    public static function toNullStr($pVal){
        return $pVal == 'null' ? NULL : $pVal;
    }
    
    /**
     * Установка sql
     * @param string $pSQL sql строка
     * @return table ссылка на самого себя 
     */
    public function sql($pSQL){
        $this->sSQL .= $pSQL;
        return $this;
        // func. sql
    }

    public function drop($pTable=null){
        $this->sSQL = 'DROP TABLE '.($pTable ?: $this->sTable);
        $this->query();
        // func. drop
    }
    
    /**
     * Выборка данных из таблицы
     * @param mixed $pFields выбераемые поля<br/>
     * array[0..n] $pFields - преобразуются в строку<br/>
     * array[string_key] $pFields - преобразуются в строку с алиансами<br/>
     * string $pFields просто прибовление строки. Небезопастно<br/>
     * $pFields = null выборка всех полей
     * @param string $pAlias алиас на таблицу
     * @return table ссылка на самого себя 
     */
    public function select($pFields=null, $pAlias=''){
        $this->sSQL .= 'SELECT ';
        // Если массив, то нужно преобразовать с строку
        if ( is_array($pFields)){
            // Если первый ключ массива число, то 1 иначе 0
            $isKeyTypeNumeric = \is_numeric(key($pFields));
            // Если тип числовой, то тут хранятся просто поля
            if ( $isKeyTypeNumeric ){
                $this->sSQL .= implode(',', $pFields);
            }else{ 
                // Иначе, хранятся поля с алиансами
                $tmp = '';
                foreach($pFields as $key=>$val){
                    $tmp .= ',' . $val . ' as `'.$key.'`';
                }
                $this->sSQL .= substr($tmp, 1);
            }
        }else // Если уже строка, то просто добавляем. Небезопастный способ
        if ( $pFields ){
            $this->sSQL .= $pFields;
        }else{ // ничего вообще нет, значит выбераем все поля
            $this->sSQL .= '*';
        }
        
        $this->sSQL .= ' FROM '.$this->sTable.' '.$pAlias;
        return $this;
    }
    
    /**
     * Выборка всех данных и таблицы. Возвращает ассоацивный массив
     * @param mixed $pSelectFields поля выборки ( SELECT )
     * @param mixed $pWhereFields условие выборки ( WHERE )
     * @param mixed $pOrderFields поля сортировки ( ORDER BY )
     * @return array
     * @example 
     * $t = new table();<br/>$t->selectAll('name', array('id'=>$id), 'id');
     */
    public function selectAll($pSelectFields, $pWhereFields=null, $pOrderFields=null){
        return self::select($pSelectFields)
                ->where($pWhereFields)
                ->order($pOrderFields)
                ->fetchAll();
    }

    /**
     * Производит операцию SELECT в виде списка.<br />
     * Пример: <br/>
     * <table border="1px">
     *      <tr><th>userId</th></tr>
     *      <tr><td>1</td></tr>
     *      <tr><td>2</td></tr>
     * </table><br />
     * $data = $orm->selectList('userId', 'userId')<br />
     * Результат: <br/>
     * $data = [1, 2]
     * @param mixed $pSelectFields Выбераемые поля.
     * Можно задвать с помощью массива [userid, data] или строки 'userid,data'
     * @param string $pField поле которые пойдёт в формирование списка
     * @param mixed $pWhereFields выборка по условию.
     * Можно задвать с помощью массива [userid, data] или строки 'userid,data'
     * @param mixed $pOrderFields поле для сортировки
     * Можно задвать с помощью массива [userid, data] или строки 'userid,data'
     * @return array
     */
    public function selectList($pSelectFields, $pField, $pWhereFields=null, $pOrderFields=null ){
        return self::select($pSelectFields)
                ->where($pWhereFields)
                ->order($pOrderFields)
                ->toList($pField);
    }
    

    /**
     * Выборка одной строки из поля. Возвращает ассоацивный массив
     * @example $t = new table(); $t->selectFirst('name', array('id'=>$id), new Exception('Error'));
     * @param mixed $pSelectFields поля выборки ( SELECT )
     * @param mixed $pWhereFields условие выборки ( WHERE )
     * @param mixed $pException исключение если строка не найдена
     * @return array
     */
    public function selectFirst($pSelectFields, $pWhereFields=null, $pException=null){
        $data = self::select($pSelectFields)
                ->where($pWhereFields)
                ->fetchFirst();
        if ($pException && !$data) {
            throw $pException;
        }
        return $data;
    }
    
    public function where($pFields=null){
        if ( !$this->sSQL ){
            self::select();
        }
        if ( $pFields ){
            $this->sSQL .= ' WHERE '.self::_where($pFields);
        }
        return $this;
    }

    public function union($pTable=''){
        $this->sSQL .= ') UNION (';
        $this->isUserUnion = true;
        self::setTable($pTable);
        return $this;
    }
    
    // TODO: переписать. Нужно более компактное решение
    public function order($pFields=null){
        if ( !$pFields )
            return $this;
        $this->sSQL .= ' ORDER BY ';
        if ( is_array($pFields)){
            $tmp = '';
            foreach($pFields as $key=>$val){
                $val = $val==self::ASC ? 'ASC' : 'DESC';
                $tmp .= ',' . $key . ' '.$val.'';
            }
            $this->sSQL .= substr($tmp, 1);
        }else{
            $this->sSQL .= $pFields;
        }
        return $this;
    }
    
    /**
     * Обновление данных в таблице
     * @param mixed $pFSet поля которые будут обнавленны
     * @param mixed $pFWhere поля для ограничений
     * @return table ссылка на самого себя 
     * Пример:<br/>
     * TODO: добавить пример вызова
     */
    public function update($pFSet, $pFWhere=null, $pExec=true, $pAliace=''){
        $fields = is_array($pFSet) ? self::_fieldSet($pFSet, self::FIELD_SET) : $pFSet;
        $this->sSQL = 'UPDATE `'.$this->sTable.'` '.$pAliace.' SET '.$fields;
        if ( $pFWhere ){
            self::where($pFWhere);
        }
        return $pExec ? self::query() : $this;
    }
    
    /**
     * Удаление данных в таблице
     */
    public function delete($pFWhere=null, $pExec=true, $pAliace=''){
        $this->sSQL = 'DELETE '.$pAliace.' FROM `'.$this->sTable.'` '.$pAliace;
        self::where($pFWhere);
        return $pExec ? self::query() : $this;
    }
    
    /**
     * Группировка данных
     * @param mixed $pFields поля для ограничений
     * @return table ссылка на самого себя 
     */
    public function group($pFields=null){
        if ( !$pFields )
            return $this;
        $this->sSQL .= ' GROUP BY ' . (is_array($pFields) ? implode(',', $pFields) : $pFields);
        
        return $this;
    }
    
    public function limit($pCountVal=null){
        if ( !$pCountVal )
           return $this;
        $this->sSQL .= ' LIMIT '.$pCountVal;
        return $this;
    }
    
    public function toList($pField){
        $list = $this->fetchAll();
        return array_map(function($pData)use($pField){
            return $pData[$pField];
        }, $list);
    }
    
    /**
     * Объединение таблиц
     * @param string $pTable таблица
     * @param mixed $pFWhere поля для объединения и выборки
     * @return table ссылка на самого себя 
     */
    public function join(string $pTable, $pFWhere){
        if ( !$pFWhere )
            return $this;
        $this->sSQL .= ' JOIN '.$pTable.' ON '. self::_where($pFWhere);
        return $this;
    }
    
    /**
     * Объединение таблиц
     * @param string $pTable таблица
     * @param mixed $pFWhere поля для объединения и выборки
     * @return table ссылка на самого себя 
     */
    public function joinLeftOuter(string $pTable, $pFWhere){
        if ( !$pFWhere ){
            return $this;
        }
        $this->sSQL .= ' LEFT OUTER JOIN '.$pTable.' ON '. self::_where($pFWhere);
        return $this;
    }

    /**
     * Выборка данных по определённым параметрам
     * @param mixed $pFSelec имя полей для полученния данных
     * @param mixed $pFWhere поля для ограничений
     * @return array 
     */
    public function selectWhere($pFSelec, $pFWhere){
        return self::select($pFSelec)->where($pFWhere);
    }
    
    /**
     * Получает одну запись из БД
     * @param string $pSQL SQL для выполнения
     * @param type $pFetchType типо возращаего значения. По умолчанию OBJECT
     * @return array
     */
    public function fetchFirst($pFetchType = self::FETCH_ASSOC) {
        self::limit('1');
        if ( $this->isUserUnion ){
            $this->sSQL = '('.$this->sSQL. ')';
            $this->isUserUnion = false;
        }
        return parent::fetchFirst($pFetchType);
    }

    public function fetchAll($pFetchType = self::FETCH_ASSOC){
        if ( $this->isUserUnion ){
            $this->sSQL = '('.$this->sSQL. ')';
            $this->isUserUnion = false;
        }
        return parent::fetchAll($pFetchType);
    }
    
    /**
     * TODO: прооптимизировать. Избавится от count(*)
     * Проверка существования данных
     * @param mixed $pFWhere условие отоборка
     * @param DBException $pException Поумол: null. Исключение в случае отсуствия объекта
     * @return boolean
     */
    public function isExists($pFWhere, $pException=null){
        $result = self::selectWhere('count(*) count', $pFWhere)
                       ->fetchFirst(self::FETCH_OBJECT)
                       ->count != 0;
        if ( $pException && !$result){
            throw $pException;
        }
        return $result;
    }
    
    public function count($pFWhere, $pException=null){
        $result = self::selectWhere('count(*) count', $pFWhere)
                       ->fetchFirst(self::FETCH_OBJECT)
                       ->count;
        if ( $pException && !$result){
            throw $pException;
        }
        return $result;
    }
    
    /**
     * Получаем единичное значение. Если значение не найденно возвращает null
     * @param string $pField какое поле получаем
     * @param mixid $pFWhere условия для выборки
     * @param Exception $pException. По умолчанию NULL
     * @return mixed|null
     */
    public function get(string $pField, $pFWhere, $pException=null){
        $data = self::selectFirst($pField, $pFWhere, $pException);
        return isset($data[$pField])?$data[$pField]:null;
    }

    protected function _where($pFWhere){
        if ( !$pFWhere )
            return '';
        return is_array($pFWhere) ?  self::_fieldSet($pFWhere, self::FIELD_WHERE) : $pFWhere;
    }
    
    // TODO: разнести WHERE и UPDATE. В данный момент один код на всё
    protected function _fieldSet(array $pFields, integer $pFieldSet) {
        $fields = self::_prepare($pFields);
        foreach($fields as $pKey => &$pItem){
            // Обработка в выражении WHERE field is NULL
            $eq = ($pFieldSet == self::FIELD_WHERE) && ($pItem === 'null') ? ' is ' : '=';
            if ( is_array($pItem)){
                $pItem = array_map(function($data){
                                    return is_null($data)?' is null':'='.$data;
                                }, $pItem);
                $pItem = '('.$pKey.implode(' or '.$pKey, $pItem).')';
            }else{
                /*if ( $pItem[0] == '!' ){
                    $pItem = substr($pItem, 1);
                    $eq = '!'.$eq;
                }*/
                $pItem = $pKey.$eq.$pItem;
            }
        }
        $delem = $pFieldSet == self::FIELD_WHERE ? ' AND ' : ',';
        $result = implode($delem, $fields);
        return $result;
    }

    public function __toString(){
        if ( $this->isUserUnion ){
            $this->sSQL = '('.$this->sSQL. ')';
        }
        return $this->sSQL;
    }
    
    /**
     * Вставка данных в таблицу
     * @param array $pField поля для вставки<br/>
     * Пример:<br/>
     * insert(array('data'=>123)) преобразует в<br/>
     * INSERT INTO _table_(`data`)VALUES(123)
     * @return table 
     */
    public function insert(array $pField, $pExec=true){
        $this->sSQL = 'INSERT INTO `'.$this->sTable.'`';
        $this->sSQL .= '(`'.implode( '`,`', array_keys($pField) ).'`)';
        $field = self::_prepare($pField);
        $this->sSQL .= 'VALUES('.implode( ',', $field).')';
        return $pExec ? self::query() : $this;
    }
    
    public function insertMulti(array $pField, $pExec=true){
        $this->sSQL = 'INSERT INTO `'.$this->sTable.'`';
        $this->sSQL .= '(`'.implode( '`,`', array_keys($pField) ).'`)';
        $insert = [];
        
        $count = current($pField);
        $count = count($count);
        for( $i = 0; $i < $count; $i++ ){
            foreach( $pField as $item){
                 $insert[$i][] = $item[$i];
            } // foreach
        } // for

        
        for( $i = 0; $i < $count; $i++ ){
            $insert[$i] = self::_prepare($insert[$i]);
            $insert[$i] = implode(',', $insert[$i]);
        } // for
        
        $insert = '('.implode('),(', $insert).')';
        
        $this->sSQL .= 'VALUE'.$insert;
        return $pExec ? self::query() : $this;
    }
    
    // TODO: возможно нужно прооптимизировать
    public function save($pWhere, $pUpdate, $pInsert = NULL ){
        if (self::isExists($pWhere)){
            self::update($pUpdate, $pWhere, true);
            return null;
        }
        $insert = !$pInsert ? $pUpdate : array_merge($pInsert, $pUpdate);
        self::insert($insert, true);
        return self::insertId();
    }

    // TODO: Сделать замену всех save на saveExt, выставить ключи в таблицах
    public function saveExt(array $pKey, array $pData, $isMerge=true){
        $insert = array_merge($pKey, $pData);
        $sql = self::insert($insert, false);
        $sql .= ' ON DUPLICATE KEY UPDATE ';
        $sql .= self::_fieldSet($pData, self::FIELD_SET);
        self::query($sql);
        return self::insertId();
        // func. saveExt
    }
    
    /**
     * Подготовка массива к формату БД<br/>
     * Преобразование boolean к строчному виду('true', 'false)<br/>
     * Добавление ковычек к строкам
     * @param array $pData данные для подготовки
     * @return array подготовленный массив
     */
    protected function _prepare(array $pData){
        $result = $pData;
        array_walk($result, function(&$pValue, $pKey, $self){
            if ( is_null($pValue) ){
                $pValue = 'null';
            }else{
                //$pValue = is_bool($pValue) ? ( $pValue ? 'true':'false') : $self->addQuote($pValue);
                if ( is_bool($pValue) ){
                    $pValue = $pValue ? 'true' : 'false';
                }else
                if ( is_array($pValue) ){
                    //$pValue = 
                    
                }else{
                    $pValue = $self->addQuote($pValue);
                }
            }
        }, $this);
        return $result;
    }
    
    public function values($pField){
        return $this;
    }
    

}