<?php

namespace core\comp\spl\ioList\vars\db;

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
            $ioListProp = \unserialize($data);
            
            if ( $num <= $ioListProp['fileCount'] && $num > 1 ){
                $ioListProp['num'] = $num;
                $ioListProp['id'] = $pContId;
                $ioListProp['prevVarName'] = $pPrevVarName;
                $ioListProp['caption'] = 'Страница '.$num;
                return $ioListProp;
            } // if
        } // if $data        
        return false;
        // func. checkByNum
    }

// class number
}

?>