<?php

namespace core\classes;

class htmlValid {
    /** @var string цвет текста */
    const R_COLOR = '^color:#[a-f0-9]{6};$';
    /** @var string цвет фона */
    const R_BGCOLOR = '^background-color:#[a-f0-9]{6};$';
    /** @var string Подчёркивание */
    const R_UNDERLINE = '^text-decoration: underline;$';
    /** @var string Выравнивание */
    const R_ALIGN = '^text-align: (center|right|justify);$';
    /** @var string Направление текста */
    const R_RTL = '^rtl$';
    const R_NAME = '^[\w]+(\[\])?$';
    const R_ALT = '^[^"]*$';
    /** @var string Отступ */
    const R_MARGIN_LEFT = '^margin-left: \d{1,3}px;$';
    /** @var string Ссылка на Якорь * */
    const R_ANCHOR = '^#\w+$';
    const R_FONT_SIZE = '^font-size:\d{1,2}px;$';
    const R_FONT_FAMILY = '^font-family:([\w _-]+(,)?)*;$';
    const R_IMG_STYLE = '^((width: \d{1,4}px;)|(height: {1,4}px;)|(border-width: \d{1,2}px;)|(border-style: solid;)|(margin: \d{1,2}px \d{1,2}px;)|(float: (left|right;))*)$';

    const R_MAIL = '^mailto:[\w@.-_]+(\?)?(subject=\w+)?(&amp;)?(body=\w+)?$';
    const R_HTTP = '^(http|\/){1}[^"]+$';
    const R_TARGET = '^(_blank|_top|_self|_parent)$';

    const A_STYLE = 'style';
    const A_SRC = 'src';
    const A_DIR = 'dir';
    const A_NAME = 'name';
    const A_HREF = 'href';
    const A_TARGET = 'target';
    const A_ALT = 'alt';

    const T_IMG = 'img';
    const T_SPAN = 'span';
    const T_BR = 'br';
    const T_STRONG = 'strong';
    const T_STRIKE = 'strike';
    const T_EM = 'em';
    const T_U = 'u';
    const T_PARAGRAPH = 'p';
    const T_HR = 'hr';
    const T_SUB = 'sub';
    const T_SUP = 'sup';
    const T_OL = 'ol';
    const T_LI = 'li';
    const T_UL = 'ul';
    const T_H1 = 'h1';
    const T_H2 = 'h2';
    const T_H3 = 'h3';
    const T_H4 = 'h4';
    const T_H5 = 'h5';
    const T_H6 = 'h6';
    const T_ADDRESS = 'address';
    const T_BLOCKQUOTE = 'blockquote';
    const T_A = 'a';
    const T_PRE = 'pre';

    private static $buff = array();

    public static function clear() {
        self::$buff = array();
    }

    public static function add($pTag, $pAttr='', $pValue='') {
        
        if (is_array($pTag)) {
            foreach($pTag as $tagName){
                self::$buff[$tagName] = array();
            }
        } else {
            if (!isset(self::$buff[$pTag])) {
                self::$buff[$pTag] = array();
            }
        }
        if ($pAttr) {
            if (is_array($pValue)) {
                if ( !isset(self::$buff[$pTag][$pAttr])){
                    self::$buff[$pTag][$pAttr] = array();
                }
                self::$buff[$pTag][$pAttr] = array_merge(self::$buff[$pTag][$pAttr], $pValue);
            } else {
                self::$buff[$pTag][$pAttr][] = $pValue;
            }
        }
    }

    /* public static function set($pBuff){
      self::$buff = $pBuff;
      } */

    // TODO: Добавить еще унивесальный тег
    public static function validate($pHtml) {
        $tags = '<' . implode('><', array_keys(self::$buff)) . '>';
        $html = strip_tags($pHtml, $tags);
        // Если тексты не совпадают, то, были использованы запрет теги
        if ($pHtml != $html) {
            throw new \Exception('Not valid HTML', 1);
        }
        // Считаем количество < и >, если они разные то, что то тут не то
        if (substr_count($html, '<') != substr_count($html, '>')) {
            throw new \Exception('Bad count: < != >', 2);
        }
        // Есть ли не валидные закрывающие теги
        if (preg_match('/(<\/\w+[^\w>]+\w*>)/i', $html)) {
            throw new \Exception('Bad valid end tags', 3);
        }
        // Поиск всех тегов
        preg_match_all('/<\s*(\w+)([^>]+)?>/i', $html, $tags);
        // Если ли теги
        if (isset($tags[1])) {
            // Количество тегов
            $tagsCount = count($tags[1]);
            for ($i = 0; $i < $tagsCount; $i++) {
                // Имя тега
                $tag = $tags[1][$i];
                // Убераем пробелы
                $attr = trim($tags[2][$i]);
                // Убегаем у одиночного теги /
                $attr = trim($attr, '/');
                if (!$attr)
                    continue;
                // Валидность аттрибутов, пробел специально добавляется, для прохождения регекспа
                if (!preg_match('/^(\w+="[^"]+"\s)*?$/i', $attr . ' ')) {
                    throw new \Exception('Not valid attr: ' . $tag . '[' . $attr . ']', 4);
                }
                // Выбераем все аттрибуты и их значения
                preg_match_all('/(\w+)="([^"]+)"/i', $attr . ' ', $attrArr);
                $attrCount = count($attrArr[1]);
                // Бегаем по полученным аттрибутам
                for ($j = 0; $j < $attrCount; $j++) {
                    // Параметр
                    $param = $attrArr[1][$j];
                    // Значение параметра
                    $value = $attrArr[2][$j];
                    // Разрешён ли такой аттрибут
                    if (!isset(self::$buff[$tag][$param])) {
                        throw new \Exception('Attr denied: ' . $tag . '[' . $param . ']', 5);
                    }
                    $buffCount = count(self::$buff[$tag][$param]);
                    // Бегаем по установленным пользователям параметрам
                    $flagException = true;
                    for ($n = 0; $n < $buffCount; $n++) {
                        $regex = self::$buff[$tag][$param][$n];
                        if (preg_match('/' . $regex . '/i', $value)) {
                            $flagException = false;
                            break;
                        }
                    } // for $n
                    if ($flagException) {
                        throw new \Exception('Bad value: ' . $tag . '[' . $param . ']="' . $value . '" as "' . $regex . '"', 6);
                    }
                } // for $j
            } // for $i
        } // isset($tag[1])
    }

// func validation
}

// class htmlValid

/*
  // Зачёркнутый
  htmlValid::add(htmlValid::T_STRIKE);
  //Параграф
  htmlValid::add(htmlValid::T_PARAGRAPH);
  // Жирный
  htmlValid::add(htmlValid::T_STRONG);
  // Курсив
  htmlValid::add(htmlValid::T_EM);
  // Подчёркнутый
  htmlValid::add(htmlValid::T_SPAN, htmlValid::A_STYLE, htmlValid::R_UNDERLINE);
  // Цвет тектса
  htmlValid::add(htmlValid::T_SPAN, htmlValid::A_STYLE, htmlValid::R_COLOR);
  // Цвет фона текста
  htmlValid::add(htmlValid::T_SPAN, htmlValid::A_STYLE, htmlValid::R_BGCOLOR);
  // Линия
  htmlValid::add(htmlValid::T_HR);
  //
  htmlValid::add(htmlValid::T_SUB);
  //
  htmlValid::add(htmlValid::T_SUP);



  try{
  print (int)htmlValid::htmlValidate($data);
  }catch(\Exception $ex){
  print $ex->getMessage();
  } */
?>