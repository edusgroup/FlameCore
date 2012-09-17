<?php

namespace buildsys\library\event\utils\rss;

//ORM
use ORM\event\eventBuffer;
use ORM\utils\rss as rssOrm;
use ORM\utils\rssProp as rssPropOrm;
use ORM\tree\compContTree as compContTreeOrm;
use ORM\seo\weblogDb;
use ORM\comp\spl\objItem\article\article as articleOrm;

//Engine
use core\classes\filesystem;
use core\classes\render;
use core\classes\DB\tree;
use core\classes\seo\blog\weblogUpdates;
use core\classes\admin\dirFunc;

// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;
use admin\library\mvc\comp\spl\objItem\event as eventObjitem;
use admin\library\mvc\comp\spl\objItem\help\model\base\model as baseModel;


// Model
use buildsys\library\event\comp\spl\objItem\model as eventModelObjitem;
use admin\library\mvc\comp\spl\objItem\model as objItemModel;

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
        $rssOrm = new rssOrm();
        $childList = $rssOrm->selectList('contId', 'contId');
        if (!$childList) {
            return;
        }
        $handleObjitem = eventModelObjitem::objItemChange(
            $eventBuffer,
            [articleOrm::TABLE],
            $rssOrm,
            new compContTreeOrm(),
            $childList,
            $childList,
            ['limit' => 10]
        );
        if (!$handleObjitem || $handleObjitem->num_rows == 0) {
            print "ERROR(" . __METHOD__ . "() | Not found Data" . PHP_EOL;
            return;
        }

        $list = [];
        while ($item = $handleObjitem->fetch_object()) {
            // Директория с данными статьи
            $saveDir = baseModel::getPath($item->compId, $item->treeId, $item->id);
            $saveDir = dirFunc::getSiteDataPath($saveDir);

            // Если файл есть, то получаем первых 50 слов
            $filename = $saveDir . 'kat.txt';
            $data = is_file($filename) ? file_get_contents($filename) : '';
            $filename = $saveDir . 'data.txt';
            $data .= is_file($filename) ? file_get_contents($filename) : '';
            $data = strip_tags($data);
            $data = preg_split('~[^\p{L}\p{N}\']+~u', $data);
            $data = array_slice($data, 0, 50);
            $descr = implode(' ', $data);
            unset($data);

            $list[] = [
                'url' => sprintf($item->urlTpl, $item->seoName, $item->seoUrl),
                'category' => $item->category,
                'date_add' => $item->date_add,
                'caption' => $item->caption,
                'descr' => $descr];
        } // while

        // Загружаем шаблон rss и производим построение списки
        $buildTpl = DIR::CORE . 'buildsys/tpl/';
        $host = 'http://' . SITE_CONF::NAME;
        $render = (new render($buildTpl, ''))->setContentType(null);

        $propData = (new rssPropOrm())->selectAll('');
        // Title используется ниже, при отправке ping
        // $render->get('title')
        foreach ($propData as $item) {
            $render->setVar($item['key'], $item['val']);
        } // foreach
        $render
            ->setVar('list', $list)
            ->setVar('host', $host)
            ->setMainTpl('rss.tpl.php');
        ob_start();
        $render->render();
        $codeData = ob_get_clean();

        $path = dirFunc::getSiteRoot() . 'res/';
        // Запись готового rss в файл
        filesystem::saveFile($path, 'main.rss', $codeData);

        // Делаем Blog ping
        $weblogUpdates = new weblogUpdates();
        $weblogDbList = (new weblogDb())->selectAll('url, methodName');
        foreach ($weblogDbList as $item) {
            $weblogUpdates->setMethodName($item['methodName']);
            /*$weblogUpdates->ping(
                $item['url'],
                $render->get('title'),
                $host,
                $host.'res/main.rss'
            );*/
        } // foreach

        // func. createFile
    }

    // class event
}