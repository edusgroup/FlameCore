<?php

namespace buildsys\library\event\comp\spl\artPopular;

// ORM
use ORM\event\eventBuffer;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\comp\spl\artPopular\artPopular as artPopularOrm;
use ORM\comp\spl\artPopular\artPopularProp as artPopularPropOrm;

// Event comp
use admin\library\mvc\comp\spl\artPopular\event as eventartPopular;
use ORM\tree\compContTree as compContTreeOrm;
use core\classes\filesystem;
use core\classes\image\resize;
use core\classes\word;
// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;

// Model
use buildsys\library\event\comp\spl\article\model as eventModelArticle;
use admin\library\mvc\comp\spl\article\model as articleModel;

/**
 * Обработчик событий для меню
 *
 * @author Козленко В.Л.
 */
class event {

    public static function createArtPopular($pUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        $articleCompId = (new componentTree())->get('id', 'sysname="article"');

        $contList = (new artPopularPropOrm())
            ->select('alp.contId, alp.itemsCount, alp.imgWidth, cc.comp_id', 'alp')
            ->join(compContTree::TABLE . ' cc', 'cc.id=alp.contId')
            ->fetchAll();

        // Бегаем по сохранённым группам
        foreach ($contList as $item) {

            // Директория к данным группы
            $saveDir = 'comp/' . $item['comp_id'] . '/' . $item['contId'] . '/';
            $saveDir = DIR::getSiteDataPath($saveDir);

            $itemsCount = $item['itemsCount'];
            $imgWidth = (int)$item['imgWidth'];

            // Получаем список детей в выбранной группе
            $artPopularOrm = new artPopularOrm();
            $childList = $artPopularOrm->selectList('selContId as contId', 'contId', 'contId=' . $item['contId']);
            $handleArticle = eventModelArticle::articleChange(
                $pEventBuffer,
                $artPopularOrm,
                new compContTreeOrm(),
                $childList,
                ['order' => 'dayCount desc, RAND()',
                'limit' => $itemsCount]
            );
            if ($handleArticle && $handleArticle->num_rows == 0) {
                return;
            }

            $miniDescrHead = '';
            $miniDescrData = '';
            $listArr = [];
            $i = 1;
            while ($artItem = $handleArticle->fetch_object()) {
                // TODO: Надо как то реализовать через общее хранилище картинок
                // Что бы всё было через апи, что бы было межсерверно

                // Обработка превью картинок. Отсекаем http://{hostname}/
                // TODO: Тут костыль, надо переделать хранение картинок для статей, превью
                if ($artItem->prevImgUrl) {
                    $imgFile = substr($artItem->prevImgUrl, 7 + 1 + strlen(SITE_CONF::NAME));

                    // Формируем имя файла, в который будет сохранять картинку
                    $resizeFile = 'comp/' . $articleCompId . '/'.$artItem->treeId.'/'.word::idToSplit($artItem->id).'artpopular/';
                    $fileResizePath = DIR::getSiteImgResizePath();
                    filesystem::mkdir($fileResizePath . $resizeFile);
                    $resizeFile .= $i . '.' . filesystem::getExt($imgFile);
                    $resize = new resize();
                    $resize->setWidth($imgWidth);
                    $resize->resize(DIR::getSiteRoot() . $imgFile, $fileResizePath . $resizeFile);
                    $artItem->prevImgUrl = DIR::getSiteImgResizeUrl() . $resizeFile.'?'.time();
                } // if $artItem->prevImgUrl

                // ----------------------------------------
                $url = sprintf($artItem->urlTpl, $artItem->seoName, $artItem->seoUrl);
                $listArr[] = [
                    'caption' => $artItem->caption,
                    'url' => $url,
                    'prevImgUrl' => $artItem->prevImgUrl
                ];
                // ----------------------------------------
                // Теперь нужно сгенерить файл со списком новостей и их мини описаниями
                // Будем всё упаковывать бинарно
                // Директория с данными статьи
                $articleDir = articleModel::getPath($artItem->compId, $artItem->treeId, $artItem->id);
                $miniDescrFile = DIR::getSiteDataPath($articleDir) . 'minidescr.txt';
                if (is_readable($miniDescrFile)) {
                    $data = file_get_contents($miniDescrFile);
                    $miniDescrHead .= pack('i', strlen($data));
                    $miniDescrData .= $data;
                } else {
                    $miniDescrHead .= pack('i', 0);
                } // if
                $i++;
            } // while
            $miniDescrHead = pack('c', $i - 1) . $miniDescrHead;
            //var_dump(unpack("c1d/i*int", $miniDescrHead));
            $data = $miniDescrHead . $miniDescrData . serialize($listArr);
            filesystem::saveFile($saveDir, 'data.txt', $data);
        } // foreach

        // func. createartPopular
    }

    // class event
}