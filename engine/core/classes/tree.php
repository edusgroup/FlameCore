<?php

namespace core\classes;


class tree {

    const FOLDER = 0;
    const FILE = 1;
    
    public static $fields = array();

    /**
     * Получаем все объекты в виде дерева
     * @param array $pData список объектов дерева
     * @param integer $pID начальный ID
     * @return array массив объектов в виде дерева 
     */
    public static function all(array $pData, $pID, $pParam = NULL) {
        $return = array();
        $pos = 0;
        //print "<br/><br/><pre>"; var_dump($pData); print '</pre><br/><br/><br/>';
        self::_rTreeTableArray($return, $pData, $pID, $pos, $pParam);
        $return['item'] = isset($return['item']) ? $return['item'] : array();
        return array('id' => $pID, 'item' => $return['item']);
    }

    public static function setField(array $pFields) {
        self::$fields = $pFields;
    }

    /**
     * Возвращает массив данных для DHTMLX
     * @param table $pTable ORM объект таблицы
     * @param mixed $pWhere условие отбора
     * @return array 
     */
    public static function createTreeOfTable($pTable, $pWhere = array()) {
        if ( ! self::$fields ){
            return;
        }
        if (\is_string($pWhere)) {
            $where = $pWhere . ' AND id!=0';
        } else {
            $where = $pWhere;
            $where['id!'] = '0';
        }

        $data = $pTable->select(self::$fields)
                ->where($where)
                ->order('tree_id, id')
                ->comment(__METHOD__)
                ->fetchAll();
        return self::all($data, 0);
        // func. createTreeOfTable
    }

    /**
     * Построение дерева из списка
     * @param array $pDist массив назначения
     * @param array $pSource исходный массив-список
     * @param integer $pId ID текущей ветки(начальной)
     * @param integer $pPos позиция в массиве $pSource
     * @return void 
     */
    private static function _rTreeTableArray(array &$pDist, array $pSource, $pId, integer $pPos, $pParam = NULL) {
        if (!isset($pSource[$pPos])) {
            return;
        }
        $id = (string) $pId;
        // Бегаем по дереву
        $pos = 0;
        //TODO: посмотреть, можно ли прооптимизировать  for ($i = $pPos; $i < $sourceCount; $i++)
        $sourceCount = count($pSource);
        for ($i = $pPos; $i < $sourceCount; $i++) {
            $sTreeId = (string) $pSource[$i]['tree_id'];
            if ($sTreeId == $id) {
                foreach (self::$fields as $key) {
                    if ( $key == 'tree_id' ){
                        continue;
                    } // if
                    $pDist['item'][$pos][$key] = $pSource[$i][$key];
                } // foreach
                $dist = &$pDist['item'][$pos];
                self::_rTreeTableArray($dist, $pSource, $dist['id'], $i + 1);
                ++$pos;
            } // if
        } // for
        // func. _rTreeTableArray
    }

// class tree
}