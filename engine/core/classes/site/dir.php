<?php

namespace core\classes\site;

// Conf
use \site\conf\DIR as DIR_SITE;
use \site\conf\SITE as SITE_SITE;

class dir {

    public static function getSiteCompTplPath($isOut, $pNsPath){
        if ( $isOut ){
            return DIR_SITE::SITE_CORE . 'tpl/site/comp/' . $pNsPath;
        }else{
            return DIR_SITE::TPL . SITE_SITE::THEME_NAME . '/comp/' . $pNsPath;
        }
        // func. getSiteCompTplPath
    }
    // class dir
}