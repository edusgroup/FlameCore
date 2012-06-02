<?php

namespace buildsys\library\mvc\event;

// ORM
use ORM\event\eventListener;
use ORM\event\eventClass;
use ORM\event\eventBuffer;
use ORM\tree\compContTree;

// Conf
use \SITE;

/**
 * Description of event
 * Параметры запуска:<br/>
 * run.php cmd=event method=run
 *
 * @author Козленко В.Л.
 */
class event {

    public function run() {

        // Выбераем всех слушателей
        $eventListener = new eventListener();
        $methodList = $eventListener
            ->select('m.method, m.eventList, m.userData,' .
                         'm.classOwnId, c.classname', 'm')
            ->join(eventClass::TABLE . ' c', 'm.classListenerId=c.id')
            ->order('m.priority')
            ->comment(__METHOD__)
            ->fetchAll();
        $eventBuffer = new eventBuffer();
        $idMax = $eventBuffer->select('max(id) as id')->fetchFirst();
        $idMax = $idMax['id'];
        if ( !$idMax ){
            return;
        }

        // Бегаем по слушателям
        $methodCount = count($methodList);
        for ($i = 0; $i < $methodCount; $i++) {
            $lMethod = $methodList[$i]['method'];
            $lEventList = $methodList[$i]['eventList'];
            $lUserData = $methodList[$i]['userData'];
            $lClassOwnId = $methodList[$i]['classOwnId'];
            $lClassName = $methodList[$i]['classname'];

            // Убираем проблемы, возводим в ковычки
            $l_eventList = explode(',', $lEventList);
            $l_eventList = array_map('trim', $l_eventList);
            $l_eventList = '\'' . implode('\',\'', $l_eventList) . '\'';

            // Смотрим, если что для нас
            //print $lClassName."\n";
            if (method_exists($lClassName, $lMethod)) {
                //$microtime = microtime(true);
                //echo PHP_EOL."Class: $lClassName::$lMethod();".PHP_EOL;
                $lClassName::$lMethod($lUserData, $eventBuffer, $l_eventList);
                //echo (microtime(true) - $microtime).PHP_EOL;
            } else {
                print "Error: $lClassName::$lMethod not found" . PHP_EOL;
            } // if method_exists
        } // for ( $i )

        //$eventBuffer->delete('id<='.$idMax);
        // func. run
    }
    // class. event
}

?>