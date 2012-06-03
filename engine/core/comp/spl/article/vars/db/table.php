<?php
namespace core\comp\spl\article\vars\db;

use ORM\comp\spl\article\article;
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
        $article = new article();
        return $article->selectFirst(
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