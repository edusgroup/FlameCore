<?php

namespace core\comp\spl\form\action;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;

// ORM
use ORM\comp\spl\form\formData as formDataOrm;


/**
 * Description of objItem
 *
 * @author Козленко В.Л.
 */
class contact {
    public function run($pData){
        $data = \serialize($pData);
        (new formDataOrm())->insert(['type'=>'contact', 'data' => $data]);
        // func. index
    }
    // class. catalogCont
}