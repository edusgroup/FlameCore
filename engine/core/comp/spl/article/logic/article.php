<?php

namespace core\comp\spl\article\logic;

// conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\word;
use core\classes\userUtils;
use core\classes\dbus;
use core\classes\render;

/**
 * Description of article
 *
 * @author Козленко В.Л.
 */
class article {

    public static $urlTplList = array(
        'category' => null
    );

    public static function renderAction($pName) {
        $comp = dbus::$comp[$pName];

        $infoData = $comp['data'];

        $infoData['isCloaking'] = $infoData['isCloaking'] && preg_match('/Google|Yandex/', $_SERVER['HTTP_USER_AGENT']);
        $dir = $comp['dir'];
        // Получаем шаблон для статьи
        $tpl = userUtils::getCompTpl($comp);

        $nsPath = $comp['nsPath'];
        // Директорию, где храняться шаблоны компонента
        $tplFile = DIR::SITE_CORE . 'tpl/' . SITE::THEME_NAME . '/comp/' . $nsPath;
        $render = new render($tplFile, '');
        // Настройки статьи
        $render->setVar('infoData', $infoData);
        if (isset($comp['urlTpl']['category'])) {
            $render->setVar('categoryUrlTpl', $comp['urlTpl']['category']);
        }
        $render->setVar('dir', $dir);
        $render->setMainTpl($tpl)
            ->setContentType(null)
            ->render();
        // func. run3
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

        if (isset(dbus::$vars[$comp['varTableName']]['seoUrl'])) {
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
        // func. setSeo
    }

    // class article
}