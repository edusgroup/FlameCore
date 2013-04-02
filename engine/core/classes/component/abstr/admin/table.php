<?php
namespace core\classes\component\abstr\admin;

interface table{

    /**
     * Возврашает имя записи в таблице
     * Может быть пустым. Нужно только если onlyFolder=1
     * @param integer $pTableId ID таблицы
     */
    public function getTableOrm();

    // interface table
}