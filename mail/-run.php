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
//$mail->Username   = "mail@mail.ru"; // SMTP account username
//$mail->Password   = "pwf";  // SMTP account password


foreach( DIR::$domainList as $domain ){
	$mailDir = DIR::APP_DATA.$domain.'/data/mail/';
	$fileList = filesystem::dir2array($mailDir, filesystem::FILE, '/^mail/');
	
	$iCount = count($fileList);
	for( $i = 0; $i < $iCount; $i++ ){
        $mailData = filesystem::loadFileContent($mailDir.$fileList[$i]);
        if ( !$mailData ){
            echo '[0] File '.$mailDir.$fileList[$i].' is empty'.PHP_EOL;
            copyFile($mailDir, $fileList[$i]);
            continue;
        }
        $mailData = json_decode($mailData, true);
        //var_dump($mailData);
        //exit;

        if ( !isset($mailData['file']) ){
            echo '[1] data[file] not found'.PHP_EOL;
            copyFile($mailDir, $fileList[$i]);
            continue;
        }

        if ( !isset($mailData['email']) ){
            echo '[2] data[email] not found'.PHP_EOL;
            copyFile($mailDir, $fileList[$i]);
            continue;
        }
		
		if ( !filter_var($mailData['email'], FILTER_VALIDATE_EMAIL) ){
			echo "[3] File ".$mailDir.$fileList[$i].' has bad-valid email'.PHP_EOL;
            copyFile($mailDir, $fileList[$i]);
			continue;
		}

        preg_match('/@(.*)$/', $mailData['email'], $domainMail);
        if ( !$domainMail || gethostbyname($domainMail[1]) == $domainMail[1]){
            echo "[4] Email ".(isset($mailData['mail'])?$mailData['mail']:'').' is bad'.PHP_EOL;
            copyFile($mailDir, $fileList[$i]);
            continue;
        }

        $file = DIR::APP_DATA.$domain.'/tpl/mail/'.$mailData['file'];
		$param = filesystem::loadFileContent($file);
        if ( !$param ){
            echo "[5] File ".$file.'.json no found or data is empty'.PHP_EOL;
            copyFile($mailDir, $fileList[$i]);
            continue;
        }

		$param = json_decode($param, true);
        if ( !isset($param['subject']) && !isset($mailData['subject']) && !$mailData['subject']){
            echo '[6] Param file: subject not found'.PHP_EOL;
            copyFile($mailDir, $fileList[$i]);
            continue;
        }

        if ( !isset($param['imgDir'])){
            echo '[7] Param file: imgDir not found'.PHP_EOL;
            copyFile($mailDir, $fileList[$i]);
            continue;
        }

        $imgDir = str_replace('%siteDir%', DIR::APP_DATA, $param['imgDir'] );
        if ( !is_dir($imgDir) ){
            echo '[8] Param file: Dir '.$imgDir.' not found'.PHP_EOL;
            copyFile($mailDir, $fileList[$i]);
            continue;
        }

        chdir($imgDir);

        if ( !isset($param['fromMail'])){
            echo '[9] Param file: fromMail not found'.PHP_EOL;
            copyFile($mailDir, $fileList[$i]);
            continue;
        }
        if ( !isset($param['fromName']) ){
            echo '[10] Param file: fromName not found'.PHP_EOL;
            copyFile($mailDir, $fileList[$i]);
            continue;
        }

        $dir = DIR::APP_DATA.$domain.'/tpl/mail/';
        if ( !is_dir($dir)){
            echo '[11] Dir '.$dir.' not found'.PHP_EOL;
            continue;
        }

        $file = DIR::APP_DATA.$domain.'/tpl/mail/'.$param['tpl'];
        if ( !is_readable($file)){
            echo '[12] File '.$file.' not found'.PHP_EOL;
            copyFile($mailDir, $fileList[$i]);
            continue;
        }

		$render->clear();
		$render->setTplPath(DIR::APP_DATA.$domain.'/tpl/mail/');
		$render->setContentType('');
		$render->setMainTpl($param['tpl']);

        if ( isset($mailData['vars'])){
            foreach( $mailData['vars'] as $varName=>$varValue){
                $render->setVar($varName, $varValue);
            } // foreach
        } // if
		
		ob_start();
		$render->render();
		$htmlCode = ob_get_clean();

		try {
		  //$mail->AddReplyTo('www.dft@mail.ru', 'First Last');
		  $mail->ClearAllRecipients();
		  $mail->ClearReplyTos();
		  $mail->ClearAttachments();
		  
		  $mail->AddAddress($mailData['email']);
		  $mail->SetFrom($param['fromMail'], $param['fromName']);
		  //$mail->AddCustomHeader('Return-Receipt-To: "VK" <www.dft@mail.ru>');
		  //$mail->AddCustomHeader('Date: Tue, 21 Jul 2012 13:34:59 +0400');
		  $mail->CharSet = 'utf-8';

          $subject = isset($mailData['subject']) ? $mailData['subject']: $param['subject'];
		  $mail->Subject = "=?UTF-8?B?" . base64_encode(html_entity_decode($subject, ENT_COMPAT, 'UTF-8')) . "?=";
		  $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		  $mail->MsgHTML($htmlCode);
		  $mail->Send();
          filesystem::unlink($mailDir.$fileList[$i]);

		} catch (phpmailerException $e) {
		  //echo $e->errorMessage(); //Pretty error messages from PHPMailer
		} catch (Exception $e) {
		  //echo $e->getMessage(); //Boring error messages from anything else!
		}

	} // for
	
} // foreach

function copyFile($mailDir, $name){
    echo  $mailDir.'notvalid/'. $name."\n";
    filesystem::copy($mailDir.$name, $mailDir.'notvalid/', $name.'.bad');
    filesystem::unlink($mailDir.$name);
    // func. copyFile
}