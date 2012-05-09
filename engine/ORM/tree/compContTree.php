<?php

namespace ORM\tree;

/**
 * Description of routeTree
 *
 * @author Виталий Козленко
 */
class compContTree extends \core\classes\DB\tree {

    const TABLE = 'pr_compcont_tree';

    public function getCompId(integer $pContId){
        return (int)parent::get('comp_id', 'id='.$pContId, new \Exception('Контент '.$pContId.' не найден', 234));
        // func. getCompId
    }
// class. compContTree
}

?>