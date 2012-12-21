<?php
namespace core\classes\component\abstr\admin;

interface table{
    /**
     * Возврашает список табличных данных, пренадлежащех категории $pContId
     * Может быть пустым. Нужно только если onlyFolder=1
     * @param integer $pContId ID родителя(категории)
     */
    //public function getTableData($pContId);

    /**
     * Возврашает имя записи в таблице
     * Может быть пустым. Нужно только если onlyFolder=1
     * @param integer $pTableId ID таблицы
     */
    public function getTableOrm();

    // interface table
}