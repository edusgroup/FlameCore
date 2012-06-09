<?php

namespace buildsys\library\event\comp\spl\objItem\article;

// ORM
use ORM\comp\spl\objItem\objItem as objItemOrm;
use core\classes\DB\tree;
use ORM\tree\compContTree;
use ORM\tree\componentTree;
use ORM\event\eventBuffer;
use ORM\comp\spl\objItem\objItemProp;
use ORM\comp\spl\oiComment\oiComment as oiCommentOrm;
use ORM\blockItemSettings as blockItemSettingsOrm;

// Conf
use \DIR;

// Engine
use core\classes\filesystem;
use core\classes\userUtils;

// Model
use admin\library\mvc\comp\spl\objItem\model as objItemModel;
// Event
use admin\library\mvc\comp\spl\objItem\event as eventObjItem;
use admin\library\mvc\comp\spl\objItem\category\article\event as eventArticle;

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class event extends \buildsys\library\event\comp\spl\objItem\event{



    // class. event
}