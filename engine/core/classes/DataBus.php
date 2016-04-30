<?php

namespace core\classes;

use core\classes\user\model\User;

/**
 * Description of dbus
 *
 * @
 */
class DataBus {
    /** @var  User $user */
    private $user;

	private $jsList = [];

    public function __construct($user)
    {
        $this->user;
    }

    public function getUser()
    {
        return $this->user;
    }
    
    public function addJs($pJsName){
        if ( !in_array($pJsName, $this->jsList) ){
            $this->jsList[] = $pJsName;
        } // if
    }
	
	public function getJsList(){
		return $this->jsList;
	}

}
