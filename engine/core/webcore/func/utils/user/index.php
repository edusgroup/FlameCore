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
$httpHost = str_replace('.codecampus.ru', '.ru', $httpHost);
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
if ( $type == 'exit'){
    setCookie("userId", '', -1, '/');
    setCookie("userData", '', -1, '/');
	session_start();
    session_destroy();
    exit;
}else
/*if ( $type == 'lightreg' ){
	$userLogin = request::post('email');
	$isExist = true;
	if ( trim($userLogin)){
		$isExist = (boolean)$userOrm->selectFirst('1', ['login' => $userLogin]);
	}
	
	if (!$isExist){
	}
	
	echo json_encode([
		'status'=> 'ok'
	]);
	exit;
}else*/
if ($type == 'regist' ){
	$userLogin = request::post('email');
	$isExist = true;
	if ( trim($userLogin)){
		$isExist = (boolean)$userOrm->selectFirst('1', ['login' => $userLogin]);
	}
	
	if (!$isExist){

        $vars['pwd'] = password::generate(6, 1, 2);

        include '/opt/www/FlameCore/mail/lib/class.phpmailer.php';

        $htmlCode = 'Добрый день!<br/> Вы зарегистрировались на '.$httpHost.'<br/> Ваш пароль: '.$vars['pwd'];


        $mail = new \PHPMailer(true);
        $mail->IsSMTP();
        $mail->host = '127.0.0.1';
        $mail->SMTPDebug = 0;                     // enables SMTP debug information (for testing)
        $mail->SMTPAuth  = false;                  // enable SMTP authentication

        $mail->AddAddress($userLogin);
        $mail->SetFrom('noreplay@'.$httpHost);
        $mail->CharSet = 'utf-8';

        $subject = 'Регистрация '.$httpHost;
        $mail->Subject = "=?UTF-8?B?" . base64_encode(html_entity_decode($subject, ENT_COMPAT, 'UTF-8')) . "?=";
        $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
        $mail->MsgHTML($htmlCode);

        try{
            $mail->Send();
        }catch(Exception $ex){
            die(json_encode([
                'status'=> 'err', 'code'=>'bad-send', 'msg'=>'Ошибка отправки письма'
            ]));
        }

        $insert = [
            'login' => $userLogin,
            'pwd' => $password->hash($vars['pwd']),
            'uniq' => md5($userLogin.time()),
            'nick' => request::post('name'),
            'enable' => 1
        ];
        (new usersOrm())->insert($insert);
	}else{
        die(json_encode([
            'status'=> 'err', 'code'=>'registr-user'
        ]));
    }
	
	echo json_encode([
		'status'=> 'ok'
	]);
	exit;
} // if


if ( $type == 'restore' ){
    // Логин пользователя
    $userLogin = request::post('email');
    // Создаём уникальный ключ для восстановления

    $isExist = (boolean)$userOrm->selectFirst('1', ['login' => $userLogin]);
    if ( !$isExist ){
        die(json_encode(['status'=>'err', 'code'=>'noexists']));
    }

    $pwd = password::generate(6, 1, 2);
    $pwdHash = $password->hash($pwd);
    $isUpdate = $userOrm->update( ['pwd'=>$pwdHash], ['login' => $userLogin] );

    include '/opt/www/FlameCore/mail/lib/class.phpmailer.php';

    $htmlCode = 'Добрый день!<br/> Вы запросили восстановление паролья на сайте '.$httpHost.'<br/> Ваш пароль: '.$pwd;


    $mail = new \PHPMailer(true);
    $mail->IsSMTP();
    $mail->host = '127.0.0.1';
    $mail->SMTPDebug = 0;                     // enables SMTP debug information (for testing)
    $mail->SMTPAuth  = false;                  // enable SMTP authentication

    $mail->AddAddress($userLogin);
    $mail->SetFrom('noreplay@'.$httpHost);
    $mail->CharSet = 'utf-8';

    $subject = 'Восстановления пароля '.$httpHost;
    $mail->Subject = "=?UTF-8?B?" . base64_encode(html_entity_decode($subject, ENT_COMPAT, 'UTF-8')) . "?=";
    $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
    $mail->MsgHTML($htmlCode);

    try{
        $mail->Send();
    }catch(Exception $ex){
        die(json_encode([
            'status'=> 'err', 'code'=>'bad-send', 'msg'=>'Ошибка отправки письма'
        ]));
    }

    echo json_encode([
        'status'=>'ok'
    ]);
    exit;
} // if


$userLogin = request::post('email');
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
	setCookie("userData", json_encode(['email'=>$userLogin, 'nick'=>$userData['nick']]), $time, '/');


	
	echo json_encode([
		'status'=>'ok'
	]);
} else {
    echo json_encode([
		'status'=>'err',
		'msg'=>'Wrong login/password', 
		'code' => 'wrong-pwd'
	]);
}