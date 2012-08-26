<?php

namespace admin\library\mvc\comp\spl\objItem\category\engart;


/**
 * Логика по управлению английской статьёй
 * @author Козленко В.Л.
 */
class model{
    public static function html2data(){
        $htmlData = file_get_contents('c:/test/data.txt');

        $tagCount = 0;
        // Вставка слов
        $htmlData = preg_replace_callback('/(<[^>]+>)?([\w\']+)(<[^>]+>)?/si', function($matches)use(&$tagCount){
            ++$tagCount;
            $lastMatches = isset($matches[3])? $matches[3] : '';
            return $matches[1].'<span class="word" num="'.$tagCount.'">'.$matches[2].'</span>'.$lastMatches;
        }, $htmlData);

        // Обработка предложений
        $tagCount = 0;
        $htmlData = preg_replace_callback('/([^.?!()]+[.?!()]?)/si', function($matches)use(&$tagCount){
            ++$tagCount;
            return '<span class="sentence" id="s'.$tagCount.'">'.$matches[1].'</span>';
        }, $htmlData);

        $htmlData = str_replace(["\n\r", "\n"], '<br/>', $htmlData);
        return $htmlData;
        // func. loadHtmlData
    }

    // class model ( engart )
}