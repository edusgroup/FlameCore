<?php

namespace core\classes\admin;

// Conf
use \site\conf\DIR as DIR_SITE;
use \site\conf\SITE as SITE_SITE;
use \DIR;
use \SITE;
use CONSTANT;


class dirFunc {
    // =============================== Methods =====================================

    public static function getAdminCompClassPath() {
        return DIR::CORE . '/admin/library/mvc/comp/';
        // func. getAdminCompClassPath
    }

    public static function getTplPath($pType) {
        return sprintf(DIR::CORE . CONSTANT::TPL_ADMIN . $pType . '/', SITE::THEME_NAME);
        // func. getTplPath
    }

    /** @var string Хранение превью картинок для админки */
    public static function getPreviewImgPath($pPostfix) {
        return DIR::PREVIEW_IMG_PATH . SITE_SITE::NAME . '/' . $pPostfix;
        // func. getPreviewImgPath
    }

    /** @var string Хранение превью картинок для админки */
    public static function getPreviewImgUrl($pPostfix) {
        return DIR::PREVIEW_IMG_URL . SITE_SITE::NAME . '/' . $pPostfix;
        // func. getPreviewImgPath
    }

    /**
     * Возвращает полный путь к директории с шаблонами сайта
     * @param string $pSiteTheme название темы
     * @return string
     */
    public static function getSiteTplPath() {
        return DIR_SITE::TPL . SITE_SITE::THEME_NAME . '/site/';
    }

    // ================== Каст логика сайта ================
    /**
     * Возвращает пусть для кастомной логики компонентов для сайта
     * @static
     * @param $pNsPath
     * @return string
     */
    public static function getSiteClassCore($pNsPath) {
        return DIR_SITE::SITE_CORE . 'core/site/comp/' . $pNsPath;
    }

    // ================== Каст Логика адмники ================
    /**
     * Возвращает пусть для кастомной логики компонентов для админки
     * @static
     * @param $pNsPath
     * @return string
     */
    public static function getSiteClassCoreAdmin($pNsPath) {
        return DIR_SITE::SITE_CORE . 'core/admin/comp/' . $pNsPath;
    }

    // ================== Каст Шаблон админки ================
    /**
     * Возвращает пусть для кастомной шаблоны компонентов для админки
     * @static
     * @param $pNsPath
     * @return string
     */
    public static function getTplAdminOuter($pNsPath) {
        return DIR_SITE::SITE_CORE . 'tpl/admin/comp/' . $pNsPath;
    }


    public static function getSiteDataPath($pType) {
        return DIR_SITE::SITE_CORE . 'data/' . $pType;
    }

    public static function getSiteUploadPathData() {
        return DIR_SITE::FILE_UPLOAD_DATA;
    }

    public static function getSiteUploadUrlData() {
        return DIR_SITE::URL_FILE_DIST;
    }

    public static function getSiteImgResizePath() {
        return DIR_SITE::IMG_RESIZE_DATA;
    }

    public static function getSiteImgResizeUrl() {
        return DIR_SITE::URL_IMG_RESIZE_PUBLIC;
    }

    public static function getSiteRoot() {
        return DIR_SITE::SITE_CORE . 'www/';
        // func. getSiteRoot
    }

    public static function getSiteResUrl() {
        return DIR_SITE::SITE_RES_URL;
        // func. getSiteResUrl
    }

    public static function getNLogFile() {
        return DIR_SITE::SITE_NGINX_LOG . 'access.log';
    }

    public static function getSiteNginxLog() {
        return DIR_SITE::SITE_NGINX_LOG;
    }

    public static function getCoreScript() {
        return DIR::CORE . 'core/';
    }
    // class confdir
}