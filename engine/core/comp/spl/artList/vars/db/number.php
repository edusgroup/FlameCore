<?php

namespace core\comp\spl\artList\vars\db;

// Conf
use site\conf\DIR;
use site\conf\SITE;
use core\classes\dbus;

/**
 * Description of table
 *
 * @author Козленко В.Л.
 */
class number {

    public static function checkByNum($pNum, $pPrevVarName, $pContId, $pCompId){
        $num = (int)$pNum;
        if ( $num == 1 ){
            redirect('../');
        }
        $categoryId = '';
        if ( $pPrevVarName ){
            $categoryId = dbus::$vars[$pPrevVarName]['id'] . '/';
        }
        $file = DIR::APP_DATA.'comp/'.$pCompId.'/'.$pContId.'/'.$categoryId.'prop.txt';
		//print $file;
        $data = file_get_contents($file);
        if ($data) {
            $artListProp = \unserialize($data);
            
            if ( $num <= $artListProp['fileCount'] && $num > 1 ){
                $artListProp['num'] = $num;
                $artListProp['id'] = $pContId;
                $artListProp['prevVarName'] = $pPrevVarName;
                $artListProp['caption'] = 'Страница '.$num;
                return $artListProp;
            } // if
        } // if $data        
        return false;
        // func. checkByNum
    }

// class number
}

?>