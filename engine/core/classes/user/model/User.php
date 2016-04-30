<?php

namespace core\classes\user\model;

/**
 * Установить количество денег у пользователя
 * @method setSum($sum)
 * @method setCallName($sum)
 * @method setId($userId) Установить Id пользователя
 * Получаем Id пользователя
 * @method string getId()
 */
class User extends \core\classes\Object
{
	private $_id_;
	private $_sum_;
	private $_callName_;

    private $isAuth;

    public function setAuth($isAuth)
    {
        $this->isAuth = $isAuth;
    }

    public function isAuth()
    {
        return $this->isAuth;
    }
}
