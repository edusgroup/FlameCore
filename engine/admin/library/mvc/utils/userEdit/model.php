<?php

namespace admin\library\mvc\utils\userEdit;
// ORM
use ORM\users as usersOrm;
use ORM\users\group as usersGroupOrm;
use ORM\users\type as usersTypeOrm;

/**
 * @author Козленко В.Л.
 */
class model {
    public function getUserData($pUserId){
        $userOrm = new usersOrm();
        return $userOrm->select('ul.*, ut.sysname', 'ul')
                    ->join(usersTypeOrm::TABLE . ' ut', 'ul.typeId = ut.id')
                    ->where('ul.id='.$pUserId)
                    ->comment(__METHOD__)
                    ->fetchFirst();
        // func. getUserXmlGrid
    }


// class model
}