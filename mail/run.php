<?
// Core
use core\classes\request;
use core\classes\dbus;
use core\classes\DB\DB as DBCore;
use core\classes\password;
use core\classes\filesystem;
use core\classes\render;

// ORM
use ORM\tree\compContTree;
use ORM\users as usersOrm;

// Conf
use site\conf\DIR;

use core\classes\validation\word;

include 'lib/class.phpmailer.php';

// Config DIR
include 'conf/DIR.php';

//include
include DIR::CORE . 'site/function/autoload.php';

include DIR::CORE . 'core/function/errorHandler.php';
//include DIR::CORE . 'core/classes/DB/adapter/mysql/adapter.php';
// Add DB conf param
//DBCore::addParam('site', \site\conf\DB::$conf);

$render = new render('', '');

// the true param means it will throw exceptions on errors, which we need to catch
$mail = new PHPMailer(true); 
// telling the class to use SMTP
$mail->IsSMTP(); 
//$mail->Host       = "smtp.mail.ru"; // SMTP server
$mail->host = '127.0.0.1';
$mail->SMTPDebug = 0;                     // enables SMTP debug information (for testing)
$mail->SMTPAuth  = false;                  // enable SMTP authentication
//$mail->Host       = "mail.yourdomain.com"; // sets the SMTP server
//$mail->Port       = 26;                    // set the SMTP port for the GMAIL server
//$mail->Username   = "www.dft@mail.ru"; // SMTP account username
//$mail->Password   = "19811986dftdft";        // SMTP account password

foreach( DIR::$domainList as $domain ){
	$mailDir = DIR::APP_DATA.$domain.'/data/mail/';
	$fileList = filesystem::dir2array($mailDir, filesystem::FILE, '/^mail/');
	
	$iCount = count($fileList);
	for( $i = 0; $i < $iCount; $i++ ){
		$data = filesystem::loadFileContentUnSerialize($mailDir.$fileList[$i]);
		
		if ( !isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) ){
			echo "File ".$mailDir.$fileList[$i].' has not valid email'.PHP_EOL;
			filesystem::copy($mailDir.$fileList[$i], $mailDir.'notvalid/'.$fileList[$i]);
			continue;
		}
		
		chdir(DIR::APP_DATA.'theme-res/'.$data['theme'].'/images/');
		
		$param = filesystem::loadFileContent(DIR::APP_DATA.$domain.'/tpl/mail/'.$data['tpl'].'.json');
		$param = json_decode($param, true);

		$render->clear();
		$render->setTplPath(DIR::APP_DATA.$domain.'/tpl/mail/');
		$render->setContentType('');
		$render->setMainTpl($data['tpl'].'.html');
		
		foreach( $data['vars'] as $varName=>$varValue){
			$render->setVar($varName, $varValue);
		} // foreach
		
		ob_start();
		$render->render();
		$htmlCode = ob_get_clean();

		try {
		  //$mail->AddReplyTo('www.dft@mail.ru', 'First Last');
		  $mail->ClearAllRecipients();
		  $mail->ClearReplyTos();
		  $mail->ClearAttachments();
		  
		  $mail->AddAddress($data['email']);
		  $mail->SetFrom('reguser@xenglishx.ru', 'xENGLISHx');
		  //$mail->AddCustomHeader('Return-Receipt-To: "VK" <www.dft@mail.ru>');
		  //$mail->AddCustomHeader('Date: Tue, 21 Jul 2012 13:34:59 +0400');
		  $mail->CharSet = 'utf-8';

		  $mail->Subject = "=?UTF-8?B?" . base64_encode(html_entity_decode($param['subject'], ENT_COMPAT, 'UTF-8')) . "?=";
		  $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		  $mail->MsgHTML($htmlCode);
		  //$mail->Send();
		  
		  
		   //$mail->Subject = 'Test email for alex.Video template1';
		   //$mail->Send();
		  echo "Message Sent OK</p>\n";
		} catch (phpmailerException $e) {
		  //echo $e->errorMessage(); //Pretty error messages from PHPMailer
		} catch (Exception $e) {
		  //echo $e->getMessage(); //Boring error messages from anything else!
		}

	} // for
	
} // foreach