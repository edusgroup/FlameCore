<?php

namespace admin\library\mvc\utils\userEdit;

// Engine
use core\classes\mvc\controllerAbstract;
// ORM
use ORM\users as usersOrm;
use ORM\users\group as usersGroupOrm;
use ORM\users\type as usersTypeOrm;

/**
 * @author Козленко В.Л.
 */
class userEdit extends controllerAbstract {

    public function init() {
        
    }

    public function indexAction(){
        $userId = self::getInt('userId');
        $userData = model::getUserData($userId);
        
        $userEditCont = 'admin\library\mvc\utils\userEdit\type\\'.$userData['sysname'];
        $userEditCont = new $userEditCont($this, $userData);
        $userEditCont->index();
        // func. indexAction
    }
    
    public function saveDataAction(){
        $userId = self::getInt('userId');
        $userData = model::getUserData($userId);

        $userEditCont = 'admin\library\mvc\utils\userEdit\type\\'.$userData['sysname'];
        $userEditCont = new $userEditCont($this, $userData);
        $userEditCont->saveData();
        // saveDataAction
    }
 // class userEdit
}

?>