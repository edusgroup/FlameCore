<?php

namespace buildsys\library\event\utils\sitemap;

//ORM
use ORM\event\eventBuffer;
use ORM\sitemaps as sitemapOrm;
use ORM\tree\compContTree as compContTreeOrm;

//Engine
use core\classes\filesystem;
use core\classes\render;
use core\classes\DB\tree;

// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;
use admin\library\mvc\comp\spl\objItem\event as eventObjitem;

// Event
use admin\library\mvc\utils\sitemap\event as eventSitemap;

// Model
use buildsys\library\event\comp\spl\objItem\model as eventModelObjitem;

/**
 * Обработчик событий для каталога URL
 *
 * @author Козленко В.Л.
 */
class event {


    public static function createFile($pListerUserData, $pOwnUserDataList, $pEventList) {
        $eventBuffer = new eventBuffer();
        // Если ли вообще какая то активность по списку
        $isData = $eventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        $sitemapOrm = new sitemapOrm();
        $childList = $sitemapOrm->selectList('contId', 'contId');
        if (!$childList) {
            return;
        }
        $handleObjitem = eventModelObjitem::objItemChange(
            $eventBuffer,
            [articleOrm::TABLE],
            $sitemapOrm,
            new compContTreeOrm(),
            $childList
        );
        if ($handleObjitem && $handleObjitem->num_rows == 0) {
            print "ERROR(" . __METHOD__ . "() | Not found Data" . PHP_EOL;
            return;
        }
        // Загружаем шаблон sitemap и производим построение списки
        $buildTpl = DIR::CORE . 'buildsys/tpl/';
        $host = 'http://' . SITE_CONF::NAME;
        ob_start();
        (new render($buildTpl, ''))
            ->setContentType(null)
            ->setVar('host', $host)
            ->setVar('handleArt', $handleObjitem)
            ->setMainTpl('sitemap.tpl.php')
            ->render();
        $codeData = ob_get_clean();

        $path = DIR::getSiteRoot();
        // Запись готового sitemap в файл
        filesystem::saveFile($path, 'sitemap.xml', $codeData);

        //echo 'sitemap.xml createFile END' . PHP_EOL;
        // func. createFile
    }

    // class event
}