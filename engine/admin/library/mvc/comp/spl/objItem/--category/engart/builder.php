<?php

namespace admin\library\mvc\comp\spl\objItem\category\article;

// Orm
use ORM\comp\spl\objItem\article\article as articleOrm;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;
use buildsys\library\event\comp\spl\objItem\model as eventModelObjitem;

// Conf
use \DIR;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class builder extends \admin\library\mvc\comp\spl\objItem\category\article\builder {

    public static function getTable(){
        return [articleOrm::TABLE];
    }

    // class. builder
}