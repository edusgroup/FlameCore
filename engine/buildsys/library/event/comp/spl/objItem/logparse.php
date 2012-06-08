<?php

namespace buildsys\library\event\comp\spl\objItem;

// Engine
use core\classes\DB\table as tableDb;
use core\classes\event as eventCore;

//Event
use admin\library\mvc\comp\spl\oiPopular\event;

// ORM
use ORM\comp\spl\objItem\objItem as objItemOrm;

/**
 * Получение популярных статей<br/>
 * Используется в buildsys\library\mvc\log\log;
 *
 * @author Козленко В.Л.
 */
class logparse {
    /** Имя временной таблицы для статей */
    const TABLE = 'pr_tmp_log_objItem';
    /** @var tableDb*/
    private static $tableDb;
    /** @var string URL regexp для получение сео названия статьи.
     * @see self::setTitleRegexp($regxp)
     * */
    private static $titleRegexp = '';

    /**
     * @static
     * Создание временной таблицы для парсинка статей
     */
    public static function createTable() {
        self::$tableDb = new tableDb(self::TABLE);
        self::$tableDb->sql('DROP TABLE IF EXISTS ' . self::TABLE)->query();
        self::$tableDb->sql('CREATE TABLE ' . self::TABLE . '(
                        seoTitle VARCHAR(255) NOT NULL COMMENT "seo title статьи",
                        id INT(11) DEFAULT NULL COMMENT "ID статьи"
                      )
                      ENGINE = HEAP
                      COMMENT = "Временная таблица для парсинга лога";')->query();
        // func. createTable
    }

    /**
     * @static
     * @param $pRegexp - регексп для получения заголовка статьи
     * Установка Regexp для получение сео заголовка статьи<br/>
     * Пример: #/blog/[^/]+/([^/]+)/#
     */
    public static function setTitleRegexp($pRegexp) {
        self::$titleRegexp = $pRegexp;
        // func. setTitleRegexp
    }

    /**
     * @static
     *  Обнавление таблицы со статьями, установка количества просмотров для статьи
     */
    public static function update() {

        // Заполнение у временной таблицы ID статей
        // Проходимся по таблице статей и по её сео названию вытаскиваем ID
        self::$tableDb->sql('UPDATE ' . self::TABLE . ' la
              JOIN ' . objItemOrm::TABLE . ' a ON a.seoUrl = la.seoTitle
              SET la.id = a.id')->query();

        // Группируем записи, только по тем у которых id не равен NULL
        // т.е. это точно статьи и сумируем их количество, обновялем в таблице
        // поле с количеством просмотров
        (new objItemOrm())->update('dayCount=0');
        self::$tableDb->sql('UPDATE
                  ' . objItemOrm::TABLE . ' a
                JOIN (
                 SELECT la.id
                     , count(1) as `count`
                FROM
                  ' . self::TABLE . ' la
                WHERE
                  la.id is not NULL
                GROUP BY la.id
                ) la on a.id = la.id
                SET a.dayCount = la.`count`')->query();

        self::$tableDb->drop(self::TABLE);

        // Посылаем сообщение, что данные обновались
        eventCore::callOffline(
            event::NAME,
            event::DATE_UPDATE,
            '',
            -1
        );
        // func. update
    }

    /**
     * @static
     * @param $pUrl url адрес из лога
     * @return void
     * Заполнение временной таблицы, сео названиями статей
     */
    public static function insertUrl($pUrl) {
        // Нам нужны только страницы статей, всё другой не обрабатываем
        if (!preg_match(self::$titleRegexp, $pUrl, $arr)) {
            return;
        } // if
        /**
         * Формат массива $arr = [ 0 => '{fullUrl}', 1 => '{title}']
         */

        $seoTitle = $arr[1];
        self::$tableDb->insert(['seoTitle' => $seoTitle]);
        // func. insertUrl
    }
    // class. logparse
}