<?php

header('Content-Type: application/json');

// Core
use core\classes\request;
use core\classes\dbus;
use core\classes\DB\DB as DBCore;
use core\classes\password;
use core\classes\validation\word;

// ORM
use ORM\tree\compContTree;
use ORM\users as usersOrm;

// Conf
use \DIR as DIR_ADMIN;
use \site\conf\DIR as DIR_SITE;
use \site\conf\SITE as SITE_SITE;


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

$password = new password(10);
$userOrm = new usersOrm();

$type = request::post('type');
if ($type == 'regist' ){
	$userLogin = request::post('login');
	$isExist = true;
	if ( trim($userLogin)){
		$isExist = (boolean)$userOrm->selectFirst('1', ['login' => $userLogin]);
	}
	
	if (!$isExist){

		$tmpfname = tempnam(DIR_SITE::APP_DATA.'mail/', "mail_");
		$handle = fopen($tmpfname, "w");
		$vars['pwd'] = password::generate(6, 1, 2);

        $jsonText = json_encode([
            'email' => $userLogin,
            'vars' => $vars,
            'file' => 'user/reg.json',
            'site' => $_SERVER['SERVER_NAME']
        ]);
        fwrite($handle, $jsonText);

		fclose($handle);
		chmod($tmpfname, 0666);

        $insert = [
            'login' => $userLogin,
            'pwd' => $password->hash($vars['pwd']),
            'uniq' => md5($userLogin.time()),
            'enable' => 1
        ];
        (new usersOrm())->insert($insert);
	}
	
	echo json_encode([
		'result'=>$isExist
	]);
	exit;
} // if


if ( $type == 'restore' ){
    // Логин пользователя
    $userLogin = request::post('login');
    // Создаём уникальный ключ для восстановления
    $restoreCode = password::generate(6, 1, 2);
    // Обновляем поле в таблице пользователей
    $isUpdate = $userOrm->update( ['restoreCode'=>$restoreCode], ['login' => $userLogin]);
    // Если ни чего нет, то пользователя не существует
    if ( !$userOrm->getHandle()->affected_rows ){
        echo json_encode([
            'error'=>'noexists'
        ]);
        exit;
    } // if

    // Создаём файл для письма
    $tmpfname = tempnam(DIR_SITE::APP_DATA.'mail/', "mail_");
    $handle = fopen($tmpfname, "w");
    // Указываем ссылку для восстановления
    $vars['url'] = '/user/?type=restore&email='.$userLogin.'&code='.$restoreCode;

    $jsonText = json_encode([
        'email' => $userLogin,
        'vars' => $vars,
        'file' => 'user/restore.json',
        'site' => $_SERVER['SERVER_NAME']
    ]);
    fwrite($handle, $jsonText);
    fclose($handle);
    chmod($tmpfname, 0666);
    echo json_encode([
        'type'=>'ok'
    ]);
    exit;
} // if


$userLogin = request::post('login');
$userPwd = request::post('pwd');

$userData = (new usersOrm())->selectFirst(SITE_SITE::USER_DATA_FIELD, [
	'enable' => 1,
    'login' => $userLogin
]);


$userData = !$userData ?: $password->verify($userPwd, $userData['pwd']) ? $userData : null;

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

    ini_set('session.gc_maxlifetime', 120960);
    ini_set('session.cookie_lifetime', 120960);

	session_start();
    unset($userData['pwd']);
	$_SESSION['userData'] = $userData;

	///$_SESSION['userGroupId'] = $userGroupsId;
	//$_SESSION['userGroupSysname'] = $userGroupsSysname;
	
	//setCookie("userData", json_encode($userData), $time, '/') ;
	setCookie("userId", $userData['uniq'], $time, '/');
	setCookie("userData", json_encode(['email'=>$userLogin]), $time, '/');


	
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