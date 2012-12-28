<?php

header('Content-Type: application/json');

// Core
use core\classes\request;
use core\classes\dbus;
use core\classes\DB\DB as DBCore;
use core\classes\password;

// ORM
use ORM\tree\compContTree;
use ORM\users as usersOrm;

// Conf
use \DIR as DIR_ADMIN;
use \site\conf\DIR as DIR_SITE;

use core\classes\validation\word;

// Config DIR
include '../../../../../admin/conf/DIR.php';
$httpHost = str_replace('.lo', '.ru', $_SERVER['HTTP_HOST']);
if (!include DIR_ADMIN::SITE_CORE . $httpHost . '/conf/DIR.php') {
    die('Conf file ' . $_SERVER['HTTP_HOST'] . ' not found');
}

include DIR::CORE . 'site/function/autoload.php';

include DIR::CORE . 'core/function/errorHandler.php';
include DIR::CORE . 'core/classes/DB/adapter/mysql/adapter.php';
// Add DB conf param
DBCore::addParam('site', \site\conf\DB::$conf);

if ( !request::isPost() ){
	exit;
}

$type = request::post('type');
if ($type == 'checkLogin' ){
	$userLogin = request::post('login');
	$isExist = true;
	if ( trim($userLogin)){
		$isExist = (boolean)(new usersOrm())->selectFirst('1', ['login' => $userLogin]);	
	}
	
	if (!$isExist){
		$tmpfname = tempnam(DIR_SITE::APP_DATA.'mail/', "mail_");
		$handle = fopen($tmpfname, "w");
		$vars['pwd'] = password::generate(6, 1, 2);
		fwrite($handle, \serialize([
			'email' => $userLogin, 
			'vars' => $vars, 
			'tpl' => 'user/reg', 
			'site' => $_SERVER['SERVER_NAME'],
			'theme' => \site\conf\SITE::THEME_NAME
		]));
		fclose($handle);
		chmod($tmpfname, 0666);
	}
	
	echo json_encode([
		'result'=>$isExist
	]);
	exit;
} // if



$userLogin = request::post('login');
$userPwd = request::post('pwd');

$userData = (new usersOrm())->selectFirst('uniq, fio', [
	'enable' => 1,
    'login' => $userLogin,
    'pwd' => md5($userPwd)
]);

if ($userData) {
	/*$userGroupRelationOrm = new userGroupRelationOrm();
	$userGroupData = $userGroupRelationOrm->select('ug.sysname, ugr.groupId', 'ugr')
					      ->join(userGroupOrm::TABLE.' ug', 'ug.id = ugr.groupId')
						  ->where('ugr.userId = '.$userData['id'])
						  ->fetchAll();
						  
	$userGroupsId = [];
	$userGroupsSysname = [];
	
	foreach( $userGroupData as $item ){
		$userGroupsId[] = $item['groupId'];
		$userGroupsSysname[] = $item['sysname'];
	} // foreach

	$time = time() + 60 * 60 * 24;*/

	session_start();
	$_SESSION['userData'] = $userData;
	///$_SESSION['userGroupId'] = $userGroupsId;
	//$_SESSION['userGroupSysname'] = $userGroupsSysname;
	
	//setCookie("userData", json_encode($userData), $time, '/') ;
	setCookie("userId", $userData['uniq'], $time, '/');
	
	echo json_encode([
		'type'=>'ok'
	]);
} else {
    echo json_encode([
		'type'=>'error', 
		'msg'=>'Wrong login/password', 
		'code' => 97
	]);
}