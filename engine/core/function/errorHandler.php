<?php
/**
 * Устраняет проблему использования скалярных типов данных<br/>
 * Прим. исп: set_error_handler( 'phpErrorHandler' ) ;
 * @param int $code Номер ошибка
 * @param string $message Собщение
 * @param string $file Название файла
 * @param int $line Номер строки
 * @return boolean
 */ 
function errorHandler( $code, $message, $file, $line ) {
	 //echo "Error $code happened! $message on $file line $line ".error_reporting()."<br/>";
	//return false;

    if ( error_reporting() & $code ) {
        if ( $code == E_RECOVERABLE_ERROR ) { // Патч для скалярных типов данных
            $regexp = '/^Argument (\d)+ passed to (.+) must be an instance of (?<hint>.+), (?<given>.+) given/i' ;
            if ( preg_match( $regexp, $message, $match ) ) {
                $given = $match[ 'given' ] ;
				$hint = explode( '\\', $match[ 'hint' ] );// поддержка namespaces
                $hint  = end( $hint ) ; 
                if ( $hint == $given ) return true ;
            }
        }
        return false ;
	}
	// func. errorHandler	
}

set_error_handler('errorHandler');