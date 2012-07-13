<?php

namespace admin\library\mvc\plugin\dhtmlx;

/**
 * Description of
 *
 * @author Козленко В.Л.
 */
class model {

    /**
     * Загрузка данных с помощью ORM класса<br/>
     * Вызывает $pOrmObj->getList($pVars) или другой метод.<br/>
     * Можно переопределить с помощью $pVars[method]
     * @param mixed $pVars даные для класса
     * @param object $pOrmObj Объект ORM 
     * @return mixed 
     */
    public static function loadData($pVars, $pOrmObj) {
        if (!is_array($pVars)) {
            return;
        }
        $methodName = isset($pVars['method']) ? $pVars['method'] : 'getList';
        if (!method_exists($pOrmObj, $methodName)) {
            return;
        }
        return $pOrmObj->{$methodName}($pVars);
        // func. loadData
    }

// class dhtmlx
}