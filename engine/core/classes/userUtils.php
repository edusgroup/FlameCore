<?php

namespace core\classes;

// Conf
use \DIR;

// Engine
use core\classes\filesystem;

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
        $path = DIR::getSiteUploadPathData() . $pPathPrefix;

        filesystem::rmdir($path);
        // Удаляем пресью в админке
        $path = DIR::getPreviewImgPath($pPathPrefix);
        filesystem::rmdir($path);
        // Удаляем маштабируемые изображения
        $path = DIR::getSiteImgResizePath() . $pPathPrefix;
        filesystem::rmdir($path);
        // Удаляем данные по компоненту
        $path = DIR::getSiteDataPath($pPathPrefix);
        filesystem::rmdir($path);
        // func. rmFolder
    }

// class userUtils
}