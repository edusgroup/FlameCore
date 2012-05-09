<?php

namespace core\classes;

// ORM
use ORM\event\eventBuffer;
use ORM\event\eventClass;

//use ORM\event\eventListener;
use ORM\tree\compContTree;

// Engine
use core\classes\DB\adapter\adapter;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class event {

    public static function callOnline($pItemId) {
        // func. callOnline
    }

    public static function callOffline($pKeyName, $pEventName, $pUserData = '', $pUserId = -1) {
        // Получаем ID Класса
        $classOwnId = (int)(new eventClass())->get('id', ['keyname' => $pKeyName]);
        if ( $classOwnId ){
            // Декодируем пользовательские данные
            $userData = $pUserData;
            if ($pUserData && is_array($pUserData)) {
                $userData = serialize($pUserData);
            }

            (new eventBuffer())->insert(['classId' => $classOwnId,
                                        'eventName' => $pEventName,
                                        'userData' => $userData,
                                        'userId' => $pUserId]);
        } // if
        // func. callOffline
    }

    // class event
}