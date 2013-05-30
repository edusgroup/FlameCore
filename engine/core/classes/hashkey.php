<?
namespace core\classes;

class hashkey{
    public static function checkHashKey($pSkey){
        $params = [];
        // Извлечение всех параметров POST-запроса, кроме WMI_SIGNATURE
        foreach($_POST as $name => $value){
            if ($name !== "WMI_SIGNATURE"){
                $params[$name] = $value;
            }
        } // foreach

        // Сортировка массива по именам ключей в порядке возрастания
        // и формирование сообщения, путем объединения значений формы
        uksort($params, "strcasecmp");

        $values = "";
        foreach($params as $value){
            //Конвертация из текущей кодировки (UTF-8)
            //необходима только если кодировка магазина отлична от Windows-1251
            //echo $value, '<br/>';
            //$value = iconv("utf-8", "windows-1251", $value);
            $values .= $value;
        } // foreach

        // Формирование подписи для сравнения ее с параметром WMI_SIGNATURE
        $signature = base64_encode(pack("H*", md5($values . $pSkey)));

        //Сравнение полученной подписи с подписью W1
        return $signature;
        // func. checkHashCode
    }
// class hashcode
}