<?php
namespace core\comp\spl\objItem\vars\db;

// ORM
use ORM\comp\spl\objItem\objItem as objItemOrm;

// Engine
use core\classes\dbus;

/**
 * Description of table
 *
 * @author Козленко В.Л.
 */
class table {
    public static function getIdByName($pName, $pPrevVarName) {
        $categoryId = dbus::$vars[$pPrevVarName]['id'];
        return (new objItemOrm())->selectFirst(
            'id, caption, seoUrl',
            ['seoUrl' => $pName,
            'treeId' => $categoryId,
            'isDel' => 0,
            'isPublic' => "yes"]);
        // func. getIdByName
    }
    // class table
}