<?php

namespace core\classes\validation;

/**
 * Description of action
 *
 * @author Козленко В.Л.
 */
class filesystem {
    
    public static function isSafe(string $pFilename, $pExc=null) {
        $result = preg_match('/^([a-zA-Z0-9_-]{1,255}\/){0,}[a-zA-Z0-9_-]{1,255}(\.?[a-zA-Z]{0,10}){4}$/', $pFilename);
        if ( $pExc && !$result){
            throw $pExc;
        }
        return $result;
        // func. isSafe
    }

// class filesystem
}