<?php

namespace core\classes;

/**
$translitCombi = array(
'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'g','з'=>'z',
'и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
'с'=>'s','t'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shh',
'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'u','я'=>'ya',' '=>'-');

foreach($translitCombi as $key=>$val){
print ord($key)."=>'".$val."',";
}
 * @see http://www.bombina.com/t5_ascii.htm
 * @author вк
 */
class word {

    public static $translitCombi1251 =
        [224 => 'a', 225 => 'b', 226 => 'v', 227 => 'g', 228 => 'd', 229 => 'e',
         184 => 'e', 230 => 'zh', 231 => 'z', 232 => 'i', 233 => 'y', 234 => 'k', 235 => 'l', 236 => 'm',
         237 => 'n', 238 => 'o', 239 => 'p', 240 => 'r', 241 => 's', 242 => 't', 243 => 'u', 244 => 'f',
         245 => 'h', 246 => 'c', 247 => 'ch', 248 => 'sh', 249 => 'shh', 250 => '', 251 => 'y', 252 => '',
         253 => 'e', 254 => 'u', 255 => 'ya', 32 => '-'];

    /*public static $translitCombiUtf8 =
        [224 => 'a', 225 => 'b', 226 => 'v', 227 => 'g', 228 => 'd', 229 => 'e',
         184 => 'e', 230 => 'g', 231 => 'z', 232 => 'i', 233 => 'y', 234 => 'k', 235 => 'l', 236 => 'm',
         237 => 'n', 238 => 'o', 239 => 'p', 240 => 'r', 241 => 's', 242 => 't', 243 => 'u', 244 => 'f',
         245 => 'h', 246 => 'c', 247 => 'ch', 248 => 'sh', 249 => 'shh', 250 => '', 251 => 'y', 252 => '',
         253 => 'e', 254 => 'u', 255 => 'ya', 32 => '-'];*/

    public static function wordToUrl($pWordRus) {
        $wordRus = $pWordRus;
        if ( preg_match('#.#u', $wordRus) ){
            $wordRus = iconv('UTF-8', 'cp1251', $wordRus);
        } // if
        $return = '';
        // Если не поставить cp1251, то strToLower не переводит в нижний регистр буквы Я и Ч (вернего регистра)
        $wordRus = mb_strtolower($wordRus, 'cp1251');
        $wordLenght = strlen($wordRus);
        for ($i = 0; $i < $wordLenght; $i++) {
            $char = $wordRus[$i];
            if (($char >= 'a' && $char <= 'z') || ($char >= '0' && $char <= '9') || ($char == '-')) {
                $return .= $char;
                continue;
            }
            $ord = ord($char);

            if (isset(self::$translitCombi1251[$ord])) {
                $return .= self::$translitCombi1251[$ord];
            }
        } // for($i)
        $return = preg_replace('/-{2,}/', '-', $return);
        $return = trim($return, '-');
        return $return;
        // func. wordToUrl
    }

    public static function idToSplit($pId) {
        $return = preg_replace('/(\d{2})/', '$1/', $pId);
        if (strlen($pId) % 2 != 0) {
            $return .= '/';
        }
        return $return;
    }

    function toBase($num, $b=62){
        $base='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $r = $num  % $b ;
        $res = $base[$r];
        $q = floor($num/$b);
        while ($q) {
            $r = $q % $b;
            $q =floor($q/$b);
            $res = $base[$r].$res;
        }
        return $res;
        // func. toBase
    }

    function to10( $num, $b=62){
        $base='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $limit = strlen($num);
        $res=strpos($base,$num[0]);
        for($i=1;$i<$limit;$i++) {
            $res = $b * $res + strpos($base,$num[$i]);
        }
        return $res;
        // func. to10
    }

}