<?php

namespace admin\library\mvc\plugin\dhtmlx\model;

use core\classes\DB\table;

/**
 * Класс для работы с dhtmlxGrid. 
 * Позволяет производить операции удаления, сохранения, изменения и загрузки данных
 *
 * @author Козленко В.Л.
 */
class grid {
    // Base Type
    // Ячейка для чтения

    const CELL_READ_ONLY = 'ro';
    // Ячейка для редактирования. Обычная строка
    const CELL_EDIT = 'ed';
    // Ячейка для редактирования. Большое поле для текста
    const CELL_TEXT = 'txt';
    // Ячейка с checkbox
    const CELL_CHECKBOX = 'ch';
    // Ячейка для выпадающим списком
    const CELL_COMBOBOX = 'co';
    // Ячейка для radiobutton
    const CELL_RADIO = 'ra';
    // Дерево
    const CELL_STREE = 'stree';

    // Extends Type
    // Ячейка c ссылкой
    const CELL_LINK = 'link';
    const CELL_IMAGE = 'img';
    const CELL_PRICE = 'price';
    const CELL_DYN = 'dyn';

    /**
     * Сохранение/изменение данных в таблице<br/>
     * Возвращает список новых ID, если был Insert в таблицу
     * @param array $pData даные преобразованные в array<br/>
     * Пример: array( 'id'=>-1, 
     *                'data'=>array(
     *                      'field1'=>'value1', 
     *                     'field2'=>'value2'));<br/>
     * Если id меньше 0, то это данные на insert, если равен или больше, то это
     * данные на изменение
     * @param type $pInsertField
     * @param type $pOrmObj ORM объект
     * @return array
     * @throws \Exception 
     */
    /*public static function saveRows($pData, $pInsertField, $pOrmObj) {
        if (!is_array($pData)) {
            throw new \Exception('Неверный тип данных', 11);
        }
        // Дополнительное поля для Insert-a
        $insert = array();
        if ($pInsertField && isset($pOrmObj->colInsert)) {
            $insert = $pInsertField;
            // производим валидацию данных
            foreach ($insert as $fieldName => &$fieldValue) {
                $fieldName = trim($fieldName);
                // Разрешены ли поля для изенений
                if (!$fieldName || !in_array($fieldName, $pOrmObj->colInsert)) {
                    unset($insert[$fieldName]);
                    continue;
                }
                if (!is_float($fieldValue) && !is_int($fieldValue)) {
                    $fieldValue = strip_tags($fieldValue);
                }
            } // foreach
        } // if($pInsertField)
        
        $data = $pData;
        $newId = array();
        // Количество строк, по которым будут изменения
        $iCount = count($data);
        for ($i = 0; $i < $iCount; $i++) {
            // Получаем строку
            $row = &$data[$i];
            // Если в строке ID
            if (!isset($row['id'])) {
                continue;
            }
            $id = (int) $row['id'];
            // Бегаем по данным и производим валидацию данных
            foreach ($row['data'] as $fieldName => &$fieldValue) {
                $fieldName = trim($fieldName);
                // Разрешены ли поля для изенений
                if (!$fieldName || !in_array($fieldName, $pOrmObj->colUpdate)) {
                    unset($row['data'][$fieldName]);
                    continue;
                }
                if (!is_float($fieldValue) && !is_int($fieldValue)) {
                    $fieldValue = strip_tags($fieldValue);
                }
            } // foreach
            
            // Если ли что сохранять
            if ( !$row['data'] ){
                continue;
            }

            // Если ID больше или равен нулю, то такой элемент уже существует
            // тогда обновляем строку, иначе добавляем новую
            if ($id >= 0) {
                $pOrmObj->update($row['data'], 'id=' . $id);
            } else {
                $row['data'] = array_merge($row['data'], $insert);
                $pOrmObj->insert($row['data']);
                $newId[$id] = $pOrmObj->insertId();
            } // if($id)else
        } // for($i)
        return $newId;
        // func. saveRows
    }*/

    /**
     * 
     * @param type $pName Название колонки
     * @param type $pId Системной название колонки
     * @param type $pType тип данных
     * @param type $pWidth ширина колонки
     * @param type $pAlign выравнивание в колонке
     * @param type $pColor цвет колонки
     * @param type $pSort  сортировка колонки
     * @see http://docs.dhtmlx.com/doku.php?id=dhtmlxgrid:configuration_from_xml
     * Possible attributes for <column> tag are:
     *
     * width - width in px;
     * type - type of column (eXcell);
     * align - align of the text inside;
     * color - background color for the column;
     * sort - sorting type;
     * id - id of the column;
     * hidden - if the attribute is set, the column will be rendered in hidden state.
     */
    public static function createColumn($pName = '', $pId = null, $pType = null, $pWidth = null, $pAlign = null, $pColor = null, $pSort = null) {
        $return = array();
        if ($pType !== null) {
            $return['attr']['type'] = $pType;
        }
        if ($pWidth !== null) {
            $return['attr']['width'] = $pWidth;
        }
        if ($pAlign !== null) {
            $return['attr']['align'] = $pAlign;
        }
        if ($pColor !== null) {
            $return['attr']['color'] = $pColor;
        }
        if ($pSort !== null) {
            $return['attr']['sort'] = $pSort;
        }
        if ($pId !== null) {
            $return['attr']['id'] = $pId;
        }
        $return['name'] = $pName;
        return $return;
        // func. createColumn
    }

    /**
     * Преобразование простого массива в options
     * @param array $pList простой массив для преобразования<br/>
     * @return array
     * Пример массива: array('data', 'full', 3, 2423);
     */
    public static function list2options($pList) {
        $listCount = count($pList);
        $return = array();
        for ($i = 0; $i < $listCount; $i++) {
            $return[$pList[$i]] = $pList[$i];
        } // for
        return $return;
        // func. list2options
    }

    /**
      /**
     * Создаём XML данные по массиву<br/>
     * Структура входящего массива. В массиве обязательно должен быть ID<br/>
     * Пример: array('body'=>array(1=>array('id'=>1, 'name'=>'test')), 'head'=>array())
     * @param array $pGridData табличные данные по которым строится таблица
     * @return string 
     */
    public static function createXMLOfArray(array $pGridData, $pGlobalUserData, $pRowUserData, $pRowsCount = null, $pPosStart = null ) {
        $body = $pGridData['body'];
        $head = isset($pGridData['head']) ? $pGridData['head'] : null;
        
        $rowsCount = count($body);
        $totalCount = $pRowsCount != NULL ? $pRowsCount : $rowsCount;
        
        $posStart = $pPosStart == NULL ? 0 : $pPosStart;
        
        // Начальный XML 
        $xml = '<?xml version="1.0" encoding="UTF-8"?><rows total_count="' . $totalCount . '" pos="'.$posStart.'">';
        if ( $pGlobalUserData ){
            // TODO: сделать
        }
        // Если данные по заголовку
        if ($head) {
            $xml .= '<head>';
            if (isset($head['column'])) {
                $headCount = count($head['column']);
                for ($i = 0; $i < $headCount; $i++) {
                    $column = $head['column'][$i];

                    $xml .= '<column';

                    if (isset($column['attr'])) {
                        foreach ($column['attr'] as $key => $val) {
                            $xml .= ' ' . $key . '="' . $val . '"';
                        }
                    }
                    $xml .= '>';
                    $xml .= $column['name'] . '</column>';
                }
            }

            $xml .= '</head>';
        } // if $head
        // Есть ли данные
        if ($body) {
            // Бегаем по строчкам
            for ($i = 0; $i < $rowsCount; $i++) {
                $row = $body[$i];
                // Создаём строку
                $xml .= '<row id="' . $row['id'];
                unset($row['id']);
                // Стиль строки
                $xml .= isset($row['style']) ? ' style="' . $cell['style'] . '"' : '';
                // Класс строки
                $xml .= isset($row['class']) ? ' class="' . $cell['class'] . '"' : '';

                $xml .= '">';
                
                // ===== Блок UserData
                if ( $pRowUserData ){
                    foreach( $pRowUserData as $udVal){
                        if ( isset($row[$udVal])){
                            $xml .= '<userdata name="'.$udVal.'">'.$row[$udVal].'</userdata>';
                        } // if
                    } // foreach
                } // if

                // Бегаем по ячейкам строки
                foreach ($row as $cell) {
                    // Если это строка
                    if (is_string($cell)) {
                        // То ячейка, это значение
                        $xml .= '<cell>' . $cell . '</cell>';
                        continue;
                    }
                    // Иначе это массив
                    $xml .= '<cell';

                    if (isset($cell['attr'])) {
                        foreach ($cell['attr'] as $key => $val) {
                            $xml .= ' ' . $key . '="' . $val . '"';
                        }
                    }

                    if (isset($cell['options']) && $cell['options']) {
                        $xml .= ' xmlcontent="true"';
                    }

                    $xml .= '>';
                    // Значение ячейки
                    $xml .= isset($cell['val']) ? $cell['val'] : '';

                    if (isset($cell['options']) && $cell['options']) {
                        foreach ($cell['options'] as $key => $val) {
                            $xml .= '<option value="' . $key . '">' . $val . '</option>';
                        } // foreach($cell['options'])
                    } // if($cell['options'])

                    $xml .= '</cell>';
                }// foreach $rows
                $xml .= '</row>';
            } // for($i)
        } // if $body
        $xml .= '</rows>';
        return $xml;
        // func. createXMLOfArray
    }

    /**
     * Удаление строки из таблицы
     * @param string $pRowsId ID строки. Строка вида: 3,1,-3,4
     * @param table $pOrmObj ORM объект
     * @return array массив удалённых ID
     */
    public static function rmRows(string $pRowsId, table $pOrmObj) {
        // Получаем из строки массив ID 
        $rowsId = explode(',', $pRowsId);
        $iCount = count($rowsId);
        $list = array();
        // Бегае по ID-кам
        for ($i = 0; $i < $iCount; $i++) {
            // Если ID < 0, то это новый ID, удалять нечего
            if ($rowsId[$i] < 0) {
                continue;
            }
            // Преобразуем ID в число, защита
            $list[$i] = (int) $rowsId[$i];
        } // for ID list
        // Есть ли данные, которые нужно удалять
        if ($list) {
            // Удаляем из таблицы данные
            $pOrmObj->delete('id in (' . implode(',', $list) . ')');
        }
        // Исходные данные, которые посылались на удаление
        return $rowsId;

        // func. rmRows
    }

// class dhtmlxGrid
}

?>