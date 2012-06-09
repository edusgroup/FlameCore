<?php

namespace core\comp\spl\oiList\vars\db;

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
            $oiListProp = \unserialize($data);
            
            if ( $num <= $oiListProp['fileCount'] && $num > 1 ){
                $oiListProp['num'] = $num;
                $oiListProp['id'] = $pContId;
                $oiListProp['prevVarName'] = $pPrevVarName;
                $oiListProp['caption'] = 'Страница '.$num;
                return $oiListProp;
            } // if
        } // if $data        
        return false;
        // func. checkByNum
    }

// class number
}

?>