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
        $where = ['seoUrl' => $pName, 'isDel' => 0, 'isPublic' => "yes"];

        if ( isset(dbus::$vars[$pPrevVarName]) ){
            $where['treeId'] = dbus::$vars[$pPrevVarName]['id'];
        }

        return (new objItemOrm())->selectFirst('id, caption, seoUrl, treeId', $where);
        // func. getIdByName
    }
    // class table
}