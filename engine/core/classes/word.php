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
 *
 * @author вк
 */
class word {

    public static $translitCombi1251 =
        [224 => 'a', 225 => 'b', 226 => 'v', 227 => 'g', 228 => 'd', 229 => 'e',
         184 => 'e', 230 => 'g', 231 => 'z', 232 => 'i', 233 => 'y', 234 => 'k', 235 => 'l', 236 => 'm',
         237 => 'n', 238 => 'o', 239 => 'p', 240 => 'r', 241 => 's', 242 => 't', 243 => 'u', 244 => 'f',
         245 => 'h', 246 => 'c', 247 => 'ch', 248 => 'sh', 249 => 'shh', 250 => '', 251 => 'y', 252 => '',
         253 => 'e', 254 => 'u', 255 => 'ya', 32 => '-'];

    public static $translitCombiUtf8 =
        [224 => 'a', 225 => 'b', 226 => 'v', 227 => 'g', 228 => 'd', 229 => 'e',
         184 => 'e', 230 => 'g', 231 => 'z', 232 => 'i', 233 => 'y', 234 => 'k', 235 => 'l', 236 => 'm',
         237 => 'n', 238 => 'o', 239 => 'p', 240 => 'r', 241 => 's', 242 => 't', 243 => 'u', 244 => 'f',
         245 => 'h', 246 => 'c', 247 => 'ch', 248 => 'sh', 249 => 'shh', 250 => '', 251 => 'y', 252 => '',
         253 => 'e', 254 => 'u', 255 => 'ya', 32 => '-'];

    public static function wordToUrl($pWordRus) {
        $wordRus = $pWordRus;
        if ( preg_match('#.#u', $wordRus) ){
            $wordRus = iconv('UTF-8', 'cp1251', $wordRus);
        } // if
        $return = '';
        $wordRus = strtolower($wordRus);
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
        }
        return $return;
    }

    public static function idToSplit($pId) {
        $return = preg_replace('/(\d{2})/', '$1/', $pId);
        if (strlen($pId) % 2 != 0) {
            $return .= '/';
        }
        return $return;
    }

}