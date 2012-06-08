<?php

namespace buildsys\library\mvc\log;

// Conf
use \SITE;
use \DIR;

// Model
use buildsys\library\event\comp\spl\objItem\logparse;

// Engine
use core\classes\filesystem;


/**
 * Description of event
 * Параметры запуска:<br/>
 * run.php cmd=log method=run
 *
 * @author Козленко В.Л.
 */
class log {
    /** Позиция времение в Regexp логе */
    const TIME = 3;
    /** Позиция URL в Regexp логе */
    const URL = 5;

    /**
     * Обработка лога nginx
     * @see http://www.nginx.org/ru/docs/http/ngx_http_log_module.html
     */
    public function run() {
        $fileNLog = DIR::getNLogFile();
        if (!is_readable($fileNLog)) {
            print "Error: file $fileNLog no reading" . PHP_EOL;
        } // if
        /* Ниже объяснение что за лог, как строить regexp
            '$remote_addr| $remote_user| $time_iso8601| $request| $status|$body_bytes_sent| "$http_referer"| "$http_user_agent"| "$http_x_forwarded_for"'
            $request ~= GET /blog/python/ HTTP/1.1
            $time_iso8601 ~= 2012-05-04T22:49:43+04:00
            $status = 200, 404, 301 и т.п
        */
        //           1:rAddr   2:rUser   3:time    4:G|P   5:url                 6:status 7:size    8:referer   9:UsAg      10:forward-for
        $strTpl = '#^([^|]*)\| ([^|]*)\| ([^|]*)\| ([^|]+) ([^|]+) HTTP/\d\.\d\| ([^|]+)\|([^|]*)\| "([^|]+)"\| "([^|]+)"\| "([^|]+)"#';

        $tmpConfDir = 'tmpconf/';
        $tmpConfFile = 'conf.conf';
        $fileTime = stat($fileNLog);
        if (!is_readable($tmpConfDir . $tmpConfFile)) {
            echo 'ERROR: file no found: ' . $tmpConfFile . PHP_EOL;
            $fseek = 0;
        } else {
            $data = \file_get_contents($tmpConfDir . $tmpConfFile);
            $data = \unserialize($data);
            $fseek = $fileTime['ctime'] == $data['last'] ? $data['fseek'] : 0;
        } // if
        unset($data);
        $configData = ['fseek' => filesize($fileNLog), 'last' => $fileTime['ctime']];

        // Создаём вспомогательную таблицу для статей
        logparse::createTable();
        // URL шаблона статьи
        logparse::setTitleRegexp('#/blog/[^/]+/([^/]+)/#');

        // Открываем файл лога на парсинг
        $fr = fopen($fileNLog, 'r');
        fseek($fr, $fseek);
        while ($line = fgets($fr)) {
            //print $line;
            if (preg_match_all($strTpl, $line, $arr, PREG_SET_ORDER)) {
                /* Формат массива $arr, после выполнения слудующий
                   $arr = [ 0=> [ 0 => '{fullstring}',
                                  1 => '{\1},
                                  2 => '{\2}'...] ]
                    Обращение можно делать с помощью констант self::URL, self::TIME
                */
                logparse::insertUrl($arr[0][self::URL]);
            } // if

        } // while
        fclose($fr);

        // Обработку файла закончили, теперь нужно произвести разбор статистики

        // Обработка статей
        logparse::update();

        $configData = \serialize($configData);
        filesystem::saveFile($tmpConfDir, $tmpConfFile, $configData);
        // func. run
    }
    // class. event
}