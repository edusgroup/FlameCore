<?php

namespace ORM\tree;

/**
 * Description of routeTree
 *
 * @author Козленко В.Л.
 */
class routeTree extends \core\classes\DB\tree {

    const TABLE = 'pr_url_tree';
    
    public function getActionUrlById(integer $pId) {
        self::parseProcedure('getActionUrlById(:id, :json)')
                ->bindIn(':id', $pId)
                ->bindOut(':json');
        return json_decode(self::exec()->json, true);
    }
    
    public function getActVarCountById(integer $pId) {
        self::parseProcedure('getActVarCountById(:id, :count)')
                ->bindIn(':id', $pId)
                ->bindOut(':count');
        return self::exec()->count;

        // func. getActVarCountById
    }

    public function getAllChildrenFromUrlTree(integer $pId){
        self::parseProcedure('getAllChildrenFromUrlTree(:id, :result)')
            ->bindIn(':id', $pId)
            ->bindOut(':result');
        // Убрием лишнюю "," слева
        return ltrim(self::exec()->result, ',');
        // func. getAllChildrenFromUrlTree
    }

// class routeTree
}