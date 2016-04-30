<?php

namespace core\classes\user\service;

use core\classes\user\model\User;

// Core
use core\classes\request;
use core\classes\DB\table;
use core\classes\render;
use core\classes\validation\word;
use core\classes\password;
use core\classes\filesystem;

// Conf
use \site\conf\DIR;
use \site\conf\DBMongo;


class UserService {
    private $name = '';
    private $email = '';
    private $isAuth = false;
    private $_id = '';
    private $pwd;

    private $mongoHandle;

    public function __construct($db)
    {
        $this->mongoHandle = $db;//(new \MongoClient("mongodb://localhost"))->selectDB(DBMongo::DB_NAME);
    }

    public function getUser()
    {

        $user = new User();
        if (!isset($_SESSION['userId'])) {
            $user->setAuth(false);
            return $user;
        }
        try{
            $userId = new \MongoId($_SESSION['userId']);
        } catch (\Exception $ex){
            $user->setAuth(false);
            return $user;
        }


        $userData = $this->mongoHandle->users->findOne(['_id' => $userId], []);
        $user->setSum($user);
    }

    /**
     * @param string $userName
     * @param string $userEmail
     * @param string|null $func
     */
    public function registration($userName, $userEmail)
    {
        if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            //return ['status'=>'mail-bad-format', 'msg'=>'Не правильный формат почты'];
			throw new \Exception('user-exists');
        }

        $userData = $this->mongoHandle->users->findOne(['email' => $userEmail], []);
        if ($userData) {
            //return ['status'=>'user-exists', 'msg'=>'Такой пользователь с таким Email уже существует'];
			throw new \Exception('user-exists');
        }

        $insertData['email'] = $userEmail;
        $insertData['pwd'] = password::generate(6);
        $insertData['callName'] = $userName;
        $insertData['sum'] = 0;

        $data = $this->mongoHandle->users->insert($insertData, ['upserted' => 1]);

        if ($data['err']) {
            return ['status' => 'user-exists', 'msg' => $data['err']];
			throw new \Exception('db-error');
        }

        $_SESSION['userId'] = $insertData['_id'].'';

        /*foreach($insertData as $key => $name) {
            $this->{$key} = $name;
        }

        return $insertData; */
		return $insertData['_id'];
    }

    public function authorization($email, $pwd)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            //return ['status'=>'mail-bad-format', 'msg'=>'Не правильный формат почты'];
			throw new \Exception('mail-bad-format');
        }

        $userData = $this->mongoHandle->users->findOne(['email' => $email, 'pwd' => $pwd], []);
        if (!$userData) {
            //return ['status'=>'user-exists', 'msg'=>'Пользователя с таким email и паролем не существует'];
			throw new \Exception('user-exists');
        }

        $_SESSION['userId'] = $userData['_id'];
    }

    public function getAuthorizationStatus()
    {
        return $this->isAuth;
    }
	

    /*public function initSession(){
        $this->id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;

        if (!$this->id) {
            $this->isAuth = false;
        }
        $this->isAuth = true;

        $userData = $this->mongoHandle->users->findOne(['_id' => $this->id]);
        foreach($userData as $key => $name) {
            $this->{$key} = $name;
        }
    }*/
}
