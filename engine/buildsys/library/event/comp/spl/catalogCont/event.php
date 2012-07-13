<?php

namespace buildsys\library\event\comp\spl\catalogCont;

// ORM
use ORM\event\eventClass;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\event\eventBuffer;
use ORM\tree\compContTree as compContTreeOrm;
use ORM\comp\spl\catalogCont\catalogCont as catalogContOrm;
use ORM\comp\spl\catalogCont\catalogContProp as catalogContPropOrm;

// Event comp
use core\classes\filesystem;

// Conf
use \DIR;

/**
 * Обработчик событий для меню
 *
 * @author Козленко В.Л.
 */
class event {

    public static function createFile($pListerUserData, $pEventBuffer, $pEventList) {
        // Если ли вообще какая то активность по списку
        $isData = $pEventBuffer->selectFirst('id', 'eventName in (' . $pEventList . ')');
        if (!$isData) {
            return;
        }

        $compId = (new componentTree())->get('id', 'sysname="catalogCont"');

        // Получаем все переиенованные каталоги и удалённые
        $contListTmp1 = $pEventBuffer->select('userId')
            ->where('eventName in ("tree:rename", "tree:delete")')
            ->group('userId')
            ->toList('userId');

        // Получаем добавленные каталоги
        $contListTmp2 = $pEventBuffer->select('cc.id', 'eb')
            ->join(compContTreeOrm::TABLE . ' cc', 'cc.tree_id=eb.userId AND eb.eventName = "tree:diradd"')
            ->group('id')
            ->toList('id');

        $contList = array_merge($contListTmp1, $contListTmp2);
        unset($contListTmp1, $contListTmp2);
        $contList = array_unique($contList);

        // Получаем все ID catalogCat
        $catalogContPropOrm = new catalogContPropOrm();
        $contListTmp1 = $catalogContPropOrm->selectList('contId', 'contId',
                                                        'contId in ("' . implode('","', $contList) . '")');
        // Получаем все вновь созданные или сохранёные catalogCat
        $contListTmp2 = $pEventBuffer->select('userId')
            ->where('eventName = "catalogcont:save"')
            ->group('userId')
            ->toList('userId');

        $contList = array_merge($contListTmp1, $contListTmp2);
        unset($contListTmp1, $contListTmp2);
        $contList = array_unique($contList);

        // Директория хранения блоков
        $saveDir = 'comp/' . $compId . '/';
        $saveDir = DIR::getSiteDataPath($saveDir);

        //echo 'catalogCont createFile START' . PHP_EOL;
        $catalogContOrm = new catalogContOrm();
        foreach ($contList as $contId) {
            $data = $catalogContOrm->select('cc.name, cc.seoName', 'catc')
                ->join(compContTreeOrm::TABLE . ' cc', 'cc.id=catc.selContId')
                ->where('catc.contId=' . $contId)
                ->comment(__METHOD__)
                ->fetchAll();
            $prop = $catalogContPropOrm->selectFirst('urltpl', 'contId=' . $contId);

			if ( !$prop['urltpl'] || $prop['urltpl'] == 'null' ){
				echo 'Error: urltpl not found ('.__METHOD__.')'.PHP_EOL;
				continue;
			}
			
            foreach ($data as &$item) {
                $item['url'] = sprintf($prop['urltpl'], $item['seoName']);
                unset($item['seoName']);
            } // foreach

            $data = \serialize($data);
            filesystem::saveFile($saveDir . $contId . '/', 'list.txt', $data);
            //echo "\tPath: ".$saveDir . $contId . '/list.txt'.PHP_EOL;
        } // foreach

        //echo 'catalogCont createFile END' . PHP_EOL;
    }

    // class event
}