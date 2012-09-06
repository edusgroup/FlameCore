<?php
namespace buildsys\library\event\comp\spl\menu;

/**
 *
 *
 * @author Козленко В.Л.
 */
class model {
    public static function rSortTree(&$pTreeArray){
        foreach( $pTreeArray as $brunchKey=>$brunch ){
            if ($brunchKey == 'item'){
                arsort($pTreeArray[$brunchKey]);
                foreach( $brunch as $itemKey => $item){
                    self::rSortTree($pTreeArray[$brunchKey][$itemKey]);
                } //foreach
            } // if
        } // foreach
        // func. sortTree
    }
}
