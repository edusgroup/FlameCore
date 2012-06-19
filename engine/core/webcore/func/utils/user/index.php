<?php

header('Content-Type: text/html;charset=UTF-8');

// Core
use core\classes\request;
use core\classes\dbus;
use core\classes\DB\DB as DBCore;
// Conf 
use \site\conf\DIR;
// ORM
use ORM\users as usersOrm;
use ORM\users\group as userGroupOrm;
use ORM\users\relation as userGroupRelationOrm;

// Config DIR
include '/home/www/SiteCoreFlame/seoforbeginners.ru/conf/DIR.php';
include DIR::CORE . 'site/function/autoload.php';
include DIR::CORE . 'core/function/errorHandler.php';
include DIR::CORE . 'core/classes/DB/adapter/mysql/adapter.php';
// Add DB conf param
DBCore::addParam('site', \site\conf\DB::$conf);

$userLogin = request::get('login');
$userPwd = \md5(request::get('pwd'));

$usersOrm = new usersOrm();
$userData = $usersOrm->selectFirst('nick, login, phone, uniq, id', array(
	'enable' => 1,
    'login' => $userLogin,
    'pwd' => $userPwd));

if ($userData) {
	
	$userGroupRelationOrm = new userGroupRelationOrm();
	$userGroupData = $userGroupRelationOrm->select('ug.sysname, ugr.groupId', 'ugr')
					      ->join(userGroupOrm::TABLE.' ug', 'ug.id = ugr.groupId')
						  ->where('ugr.userId = '.$userData['id'])
						  ->fetchAll();
						  
	$userGroupsId = array();
	$userGroupsSysname = array();
	
	foreach( $userGroupData as $item ){
		$userGroupsId[] = $item['groupId'];
		$userGroupsSysname[] = $item['sysname'];
	} // foreach

	$time = time() + 60 * 60 * 24;

	session_start();
	$_SESSION['userData'] = $userData;
	$_SESSION['userGroupId'] = $userGroupsId;
	$_SESSION['userGroupSysname'] = $userGroupsSysname;
	
	setCookie("userData", json_encode($userData), $time, '/') ;
	setCookie("userUniq", $userData['uniq'], $time, '/') ;
	echo json_encode(array(
		'type'=>'ok'
	));
} else {
    echo json_encode(array(
		'type'=>'error', 
		'msg'=>'Неверный логин/пароль', 
		'code' => 97
	));
}
?>