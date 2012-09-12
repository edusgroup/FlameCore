<?php

namespace admin\library\mvc\comp\spl\objItem\help\event\base;

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