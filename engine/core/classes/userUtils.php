<?php

namespace core\classes;

// Conf
use \DIR;
use site\conf\DIR as SITE_DIR;

// Engine
use core\classes\filesystem;
use core\classes\admin\dirFunc;

/**
 * @author Козленко В.Л.
 */
class userUtils {

    public static function getCompTpl($pComp) {
        $tpl = $pComp['tpl'];
        // Есть ли доступы по пользователю
        if (isset($pComp['tplAccess']) && dbus::$user) {
            // Есть ли ограничение по группам
            if (isset($pComp['userGroup'])) {
                if (array_intersect($pComp['userGroup'], $_SESSION['userGroupId'])) {
                    $tpl = $pComp['tplAccess'];
                } // if array_intersect
            } else {
                $tpl = $pComp['tplAccess'];
            } // if isset($comp['userGroup'])
        } // if isset($comp['tplAccess'])
        return $tpl;
        // func. getCompTpl
    }

    public static function rmFolder($pPathPrefix) {
        //$path = dirFunc::getSiteUploadPathData() . $pPathPrefix;
        $path = SITE_DIR::IMG_RESIZE_DATA.$pPathPrefix;
        filesystem::rmdir($path);

        // Удаляем превью в админке
        $path = dirFunc::getPreviewImgPath($pPathPrefix);
        filesystem::rmdir($path);

        // Удаляем маштабируемые изображения
        //$path = dirFunc::getSiteImgResizePath() . $pPathPrefix;
        //filesystem::rmdir($path);
        // Удаляем данные по компоненту
        $path = dirFunc::getSiteDataPath($pPathPrefix);
        filesystem::rmdir($path);


        // func. rmFolder
    }

// class userUtils
}