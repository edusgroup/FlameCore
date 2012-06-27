<?php

namespace admin\library\mvc\comp\spl\objItem;

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

/**
 * Description of event
 *
 * @author Козленко В.Л.
 */
class event {
    const NAME = 'objItem';
    /**
     * Сохранение названия, seo названия и доступности публикации
     */
    const ACTION_TABLE_SAVE = 'objItem:tableSave';
    /**
     * Удаление статьи
     */
    const ACTION_DELETE = 'objItem:tableDelete';
    /**
     * Изменение кастом параметров в дереве статьи
     * см. функ. compProp
     */
    const ACTOIN_CUSTOM_PROP_SAVE = 'objItem::propCustSave';
    // class. event
}