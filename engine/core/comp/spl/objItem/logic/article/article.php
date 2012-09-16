<?php

namespace core\comp\spl\objItem\logic\article;

// conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\word;
use core\classes\userUtils;
use core\classes\dbus;
use core\classes\render;
use core\classes\site\dir as sitePath;

/**
 * Description of article
 *
 * @author Козленко В.Л.
 */
class article {

    public static $urlTplList = [
        'category' => null
    ];

    public static function renderAction($pName) {
        $comp = dbus::$comp[$pName];
        $infoData = $comp['data'];
        $infoData['isCloaking'] = $infoData['isCloaking'] && preg_match('/Google|Yandex/', $_SERVER['HTTP_USER_AGENT']);

        // Получаем шаблон для статьи
        $tpl = userUtils::getCompTpl($comp);

        // Директорию, где храняться шаблоны компонента
        // Кастомный ли это шаблон или нет
        $tplPath = sitePath::getSiteCompTplPath($comp['isTplOut'], $comp['nsPath']);
        $render = new render($tplPath, '');
        // Настройки статьи

        if (isset($comp['urlTpl']['category'])) {
            $render->setVar('categoryUrlTpl', $comp['urlTpl']['category']);
        }

        $render->setMainTpl($tpl)
            ->setVar('dir', $comp['dir'])
            ->setVar('infoData', $infoData)
            ->setContentType(null)
            ->render();
        // func. renderAction
    }

    public static function init($pName) {
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];

        if (!isset($comp['varName'])) {
            $contId = $comp['contId'];
        } else {
            $contId = dbus::$vars[$comp['varName']]['id'];
        }

        $isCompGetDatainVar = isset($comp['varTableName']);

        // Если есть varTableName, то это статья получает данные из переменной
        if ($isCompGetDatainVar) {
            $tableId = dbus::$vars[$comp['varTableName']]['id'];
        } else {
            // Если varTableName нет, то статья установленна статически
            $tableId = $comp['tableId'];
        }

        $idSplit = word::idToSplit($tableId);
        $dir = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/' . $idSplit;

        // Настроки статьи
        $infoData = file_get_contents($dir . 'info.txt');
        $infoData = unserialize($infoData);
        dbus::$comp[$pName]['data'] = $infoData;
        dbus::$comp[$pName]['dir'] = $dir;

        // func. init
    }

    /**
     * @static
     * Установка параметров SEO
     */
    public static function setDataSeo($pName, $pParam) {
        $infoData = dbus::$comp[$pName]['data'];
        $linkNextTitle = $pParam['linkNextTitle'];

        $comp = dbus::$comp[$pName];

        $isCanonical = isset($comp['varTableName']);
        $isCanonical =  $isCanonical && isset(dbus::$vars[$comp['varTableName']]['seoUrl']);
        if ($isCanonical) {
            echo '<link rel="canonical" href="' . $infoData['canonical'] . '" />';
        } // if
        if (isset($infoData['prev'])) {
            echo '<link rel="prev" '
                . 'title="' . sprintf($linkNextTitle, $infoData['prev']['caption']) . '" '
                . 'href="' . $infoData['prev']['url'] . '" />';
        } // if
        if (isset($infoData['next'])) {
            echo '<link rel="next" '
                . 'title="' . sprintf($linkNextTitle, $infoData['next']['caption']) . '" '
                . 'href="' . $infoData['next']['url'] . '" />';
        } // if
        // func. setDataSeo
    }

    // class article
}