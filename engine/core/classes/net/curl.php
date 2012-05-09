<?php

namespace core\classes\net;

/**
 * Description of curl
 *
 * @author Козленко В.Л.
 */
class curl {

    // HTTP заголовки
    private $_headers = [];
    // Использовать ли cookie файлы
    private $_cookieFile = '';
    private $_proxy = null;

    public function __construct(/*$cookies=TRUE, $cookie='cookies.txt', $compression=''*/) {
        self::initHeader();

        /*$this->cookies = $cookies;
        if ($cookies == TRUE)
            $this->cookie($cookie);
        */
        // func. cURL
    }


    /*function cookie($cookie_file) {
        if (file_exists($cookie_file)) {
            $this->_isUseCookieFile = $cookie_file;
        } else {
            $fw = fopen($cookie_file, 'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
            fclose($fw);
            $this->_isUseCookieFile = $cookie_file;

        }
    }*/

    function get($pUrl) {

        $process = curl_init($pUrl);
        self::_defaultInitRequest($process);

        $return = curl_exec($process);
        curl_close($process);
        /*if ($this->param['file']) {
            fclose($fw);
        }*/

        return $return;
        // func. get
    }

    function initHeader() {
        //$this->_headers['accept'] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
        //$this->_headers['connection'] = 'Connection: keep-alive';
        $this->_headers['content-type'] = 'Content-Type: text/xml';
        //$this->_headers['accept-language'] = 'Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3';
        //$this->_headers['accept-charset'] = 'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7';
        // func. initHeaders
    }

    private function _defaultInitRequest($process){
        // CURLOPT_VERBOSE CURLOPT_NOPROGRESS
        // Установка заголовков из массива $this->_headers
        if ( $this->_headers ){
            curl_setopt($process, CURLOPT_HTTPHEADER, $this->_headers);
        }
        // Отключаем вывод шабпки Header в возвращаемое значение
        curl_setopt($process, CURLOPT_HEADER, 0);

        // Использовать ли cookie файлы
        /*if ($this->cookies ){
            curl_setopt($process, CURLOPT_COOKIEFILE, $this->_isUseCookieFile);
            curl_setopt($process, CURLOPT_COOKIEJAR, $this->_isUseCookieFile);
        } // if
        */
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_NOPROGRESS, 1);
        curl_setopt($process, CURLOPT_VERBOSE, 1);
        if ($this->_proxy){
            curl_setopt($process, CURLOPT_PROXY, $this->_proxy);
        }
        // Нам нужено тело результат
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        // При редиректах переходить туда, куда редиректят
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);

        // Забиваем на проверку HTTPS
        curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);

        /*if ($this->param['file']) {
            $fw = fopen($this->param['file'], 'w');
            curl_setopt($process, CURLOPT_FILE,  $fw);
        }*/
        // func. _defaultInitRequest
    }

    function post($pUrl, $pData) {
        $process = curl_init($pUrl);
        self::_defaultInitRequest($process);

        curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_POSTFIELDS, $pData);

        $return = curl_exec($process);
        curl_close($process);
        return $return;
        // func. post
    }
// class curl
}
