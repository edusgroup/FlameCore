<?php

namespace core\classes\seo\blog;

/**
 * Description of weblogUpdates
 * @see Протокол Weblogs.Com XML-RPC interface
 *
 * @see http://api.yandex.ru/blogs/doc/indexation/appendices/weblogping-sample.xml
 * Каждый раз при появлении новой записи на блогохостинге нужно посылать Яндексу специальное сообщение по протоколу Weblogs.Ping.
 * Адрес: http://ping.blogs.yandex.ru/RPC2
 * Метод: weblogUpdates.ping (weblogname, weblogurl, changesurl=weblogurl, categoryname="none") returns struct;
 *
 * @see http://support.google.com/webmasters/bin/answer.py?hl=ru&answer=70950
 * Адрес: http://blogsearch.google.com/ping/RPC2
 * weblogUpdates.extendedPing
 *
 * @author Козленко В.Л.
 */
class weblogUpdates {

    private static $methodName = 'weblogUpdates.ping';

    public function setMethodName($pMethodName){
        self::$methodName = $pMethodName;
        // func. setMethodName
    }

    private static function _prepare($pName, $pUrl, $pUrlRss){
        // For google - methodName = weblogUpdates.extendedPing
        return '<?xml version="1.0"?><methodCall>'
               .'<methodName>'.self::$methodName.'</methodName>'
        .'<params><param><value>'.$pName.'</value></param>'
        .'<param><value>'.$pUrl.'</value></param>'
        .'<param><value>'.$pUrl.'</value></param>'
        .'<param><value>'.$pUrlRss.'</value></param></params></methodCall>';
    }


    public static function ping($url, $pName, $pUrl, $pRssUrl){
        $xmlData = self::_prepare($pName, $pUrl, $pRssUrl);
        // Что бы не использовать curl, сделаем всё нативно
        // Не надо подключать целый модуль
        $target=parse_url($url);
        // Коннектимся
        $sockHandle = fsockopen($target["host"], 80);
        // Говорим что будем делать POST запрос
        $query = isset($target["query"])?$target["query"]:'';
        fputs($sockHandle, "POST " . $target["path"] . $query . " HTTP/1.1\r\n");
        // Говорим какой хотим видеть хост
        fputs($sockHandle, "Host: " . $target["host"] . "\r\n");
        // Выставляем User-Agent
        fputs($sockHandle, "User-Agent: IloveYou\r\n");
        // Тип передаваемого контента
        fputs($sockHandle, "Content-Type: text/xml\r\n");
        // Длинна этого контента
        fputs($sockHandle, "Content-length: " . strlen($xmlData) . "\r\n");
        // Закрываем соединение после получения контента ( в конце 2 \r\n ставить нужно)
        fputs($sockHandle, "Connection: close\r\n\r\n");
        // и конкатенируем наш XML
        fputs($sockHandle, $xmlData);

        // Данные ушли, ждём  ответа и считывам его
        $response = '';
        while (!feof($sockHandle)) {
            $response .= fgets($sockHandle, 128);
        }
        fclose($sockHandle);
        // TODO: нужно где то сделать обработку результата и куда то сохранять
        // что бы потом где то можно было посмотреть результат ping-а
        //print $response;
        //strpos($response, '<error>0</error>') ? $return = true : $return = false;
        //return $return;
        // func. ping
    }
// class. weblogUpdates
}
