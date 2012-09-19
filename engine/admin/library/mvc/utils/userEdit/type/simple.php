<?php

namespace admin\library\mvc\utils\userEdit\type;

// Conf
use \DIR;

// Engine
use core\classes\render;
use core\classes\admin\dirFunc;

// ORM
use ORM\users as usersOrm;
use ORM\users\relation as usersRelationOrm;
use ORM\users\group as usersGroupOrm;

// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * @author Козленко В.Л.
 */
class simple {

    public $contr;
    public $userData;
    public $userId;

    public function __construct($pContr, $pUserData) {
        $this->contr = $pContr;
        $this->userData = $pUserData;
        $this->userId = $pUserData['id'];
    }

    public function index() {
        $this->contr->setJson('userData', $this->userData);

        $usersRelationOrm = new usersRelationOrm();
        $relation = $usersRelationOrm->selectList('groupId', 'groupId', 'userId=' . $this->userId);
        $this->contr->setJson('relation', $relation);

        $groupTree = dhtmlxTree::createTreeOfTable(new usersGroupOrm());
        $this->contr->setJson('groupTree', $groupTree);

        $this->contr->view->setBlock('panel', 'users/type/simple.tpl.php');
        $this->contr->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->contr->view->setMainTpl('main.tpl.php');
    }

    public function saveData() {
        $this->contr->view->setRenderType(render::JSON);
        $userId = $this->userId;

        $nick = $this->contr->post('nick');
        $login = $this->contr->post('login');
        $phone = $this->contr->post('phone');
        $pwd = $this->contr->post('pwd');
        $enable = $this->contr->post('enable');

        // Соотношение пользователя
        $usersRelationOrm = new usersRelationOrm();
        $usersRelationOrm->delete('userId=' . $userId);

        $group = $this->contr->post('group');
        if ($group) {
            $group = explode(',', $group);
            array_map(function ($pGroupId) use ($userId, $usersRelationOrm) {
                $usersRelationOrm->insert(array(
                                               'userId' => $userId,
                                               'groupId' => (int)$pGroupId
                                          ));
            }, $group);
        } // if

        $saveData = array(
            'nick' => $nick,
            'login' => $login,
            'phone' => $phone,
            'enable' => $enable
        );
        if ($pwd != '~null~') {
            $saveData['pwd'] = \md5($pwd);
        }

        $userOrm = new usersOrm();
        $userOrm->save('id=' . $userId, $saveData);

        //$this->contr->setVar('json', 1);
        // func. saveData
    }

}