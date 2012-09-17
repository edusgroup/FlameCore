<?php

namespace buildsys\library\event\utils\robots;

//ORM
use ORM\robots as robotsOrm;
use ORM\tree\routeTree;
use ORM\event\eventBuffer;

//Engine
use core\classes\filesystem;
use core\classes\render;
use core\classes\admin\dirFunc;

// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;

/**
 * Обработчик событий для каталога URL
 *
 * @author Козленко В.Л.
 */
class event {

    public static function createFile($pListerUserData, $pOwnUserDataList, $pEventList) {
        $eventBuffer = new eventBuffer();
        $isData = $eventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }
        $robotsOrm = new robotsOrm();
        $robotsData = $robotsOrm->selectAll('*', null, 'priority');

        $routeTree = new routeTree();
        $actionList = $routeTree->selectAll('id, robots', 'robots != "none"', 'brunchNum DESC');
        ob_start();
        foreach ($robotsData as $robots) {
            echo 'User-agent: ' . $robots['userAgent'] . PHP_EOL;
            echo 'Crawl-delay: ' . $robots['crawlDelay'] . PHP_EOL;
            if ($robots['cleanParam']) {
                echo 'Clean-param: ' . $robots['cleanParam'] . PHP_EOL;
            }
            /*if (!$actionList) {
                echo 'Allow: /' . PHP_EOL;
            }*/
            
        } // foreach robots

        echo 'Disallow:'. PHP_EOL;
		echo 'Host: ' . SITE_CONF::NAME . PHP_EOL . PHP_EOL;

        foreach ($actionList as $actionItem) {
            $url = $routeTree->getActionUrlById((int)$actionItem['id']);
            $url = array_map(function($pItem) {
                return $pItem['name'];
            }, $url);
            $url = array_reverse($url);
            echo $actionItem['robots'] == 'disallow' ? 'Disallow' : 'Allow';
            $url = ': /' . implode('/', $url) . '/';
            echo $url . PHP_EOL;
        } // foreach actionList

        echo 'Sitemap: http://' . SITE_CONF::NAME . '/sitemap.xml';
        $codeData = ob_get_clean();

        $path = dirFunc::getSiteRoot();
        filesystem::saveFile($path, 'robots.txt', $codeData);

        // func. createFile
    }

    // class event
}