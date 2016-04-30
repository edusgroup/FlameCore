<?php

namespace buildsys\library\event\utils\sitemap;

//ORM
use ORM\event\eventBuffer;
use ORM\sitemaps as sitemapOrm;
use ORM\tree\compContTree as compContTreeOrm;
use ORM\comp\spl\objItem\article\article as articleOrm;

//Engine
use core\classes\filesystem;
use core\classes\render;
use core\classes\DB\tree;
use core\classes\admin\dirFunc;

// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;
use \site\conf\DIR as SITE_DIR;
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

		echo 'Sitemap init'.PHP_EOL;
		
        $handleObjitem = eventModelObjitem::objItemChange(
            $eventBuffer,
            [articleOrm::TABLE],
            $sitemapOrm,
            new compContTreeOrm(),
            $childList,
            $childList
        );
		
        if (!$handleObjitem || $handleObjitem->num_rows == 0) {
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
            ->setMainTpl('sitemap-xml.tpl.php')
            ->render();
        $codeData = ob_get_clean();
		
		$path = dirFunc::getSiteRoot();
        // Запись готового sitemap в файл
        filesystem::saveFile($path, 'sitemap.xml', $codeData);
		
		
		$handleObjitem->data_seek(0);
		ob_start();
        (new render($buildTpl, ''))
            ->setContentType(null)
            ->setVar('host', $host)
            ->setVar('handleArt', $handleObjitem)
            ->setMainTpl('sitemap-html.tpl.php')
            ->render();
        $codeData = ob_get_clean();

		$path = SITE_DIR::APP_DATA.'sitemap/';
		filesystem::saveFile($path, 'sitemap.html', $codeData);
		
		echo 'Sitemap rebuild'.PHP_EOL;

        // func. createFile
    }

    // class event
}