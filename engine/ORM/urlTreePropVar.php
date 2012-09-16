<?php

namespace ORM;

/**
 * Доступк к 
 *
 * @author Козленко В.Л.
 */
class urlTreePropVar extends \core\classes\DB\table {

    const TABLE = 'pr_urltree_prop_var';
    
    public function getWFId(integer $pId) {
        return $pId == 0 ? '' : (int) self::get('wf_id', 'acId=' . $pId);
    }
    // class urlTreePropVar
}