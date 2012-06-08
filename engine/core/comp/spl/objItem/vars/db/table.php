<?php
namespace core\comp\spl\objItem\vars\db;

use ORM\comp\spl\objItem\objItem;
// Engine
use core\classes\dbus;

/**
 * Description of table
 *
 * @author Козленко В.Л.
 */
class table {
    public static function getIdByName($pName, $pPrevVarName){
        $categoryId = dbus::$vars[$pPrevVarName]['id'];
        $objItem = new objItem();
        return $objItem->selectFirst(
                'id, caption, seoUrl',
                ['seoUrl'=>$pName,
                'treeId' => $categoryId,
                'isDel' => 0,
                'isPublic' => "yes"]);
        // func. getIdByName
    }
// class table
}

?>