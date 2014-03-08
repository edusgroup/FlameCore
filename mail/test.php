<?
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

$userId = 234234;

$mail = new PHPMailer(true);
// telling the class to use SMTP
$mail->IsSMTP();
//$mail->Host       = "smtp.mail.ru"; // SMTP server
$mail->host = '127.0.0.1';
$mail->SMTPDebug = 0;                  // enables SMTP debug information (for testing)
$mail->SMTPAuth  = false;                  // enable SMTP authentication

$imgDir = '/opt/www/SiteCoreFlame/marketingforyou.ru/www/res/email/';
chdir($imgDir);

$htmlCode = file_get_contents('/home/vk/mail.html');

//$mail->AddReplyTo('www.dft@mail.ru', 'First Last');
$mail->ClearAllRecipients();
$mail->ClearReplyTos();
$mail->ClearAttachments();

//$mail->AddAddress('alex@askalex.ru');
$mail->AddAddress('www.dft@mail.ru');
$mail->SetFrom('tg@marketingforyou.ru', 'Ольга Скворцова');
//$mail->AddCustomHeader('Return-Receipt-To: "VK" <www.dft@mail.ru>');
$mail->AddCustomHeader('List-Unsubscribe: <http://codecampus.ru/unsubscribe/>,<mailto:unsubscribe-gmail@marketingforyou.ru?subject=User:'.$userId.'>');
$mail->CharSet = 'utf-8';

$subject = 'CountDown в письме';//isset($mailData['subject']) ? $mailData['subject']: $param['subject'];
$mail->Subject = "=?UTF-8?B?" . base64_encode(html_entity_decode($subject, ENT_COMPAT, 'UTF-8')) . "?=";
$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
$mail->MsgHTML($htmlCode);
$ret = $mail->Send();
var_dump($ret);

// http://habrahabr.ru/post/114852/

// List-Unsubscribe Header
// Precedence: bulk
// PTR   http://centralops.net/co/DomainDossier.aspx
// DMARC   http://help.mail.ru/mail-help/postmaster/dmarc
// SPF
// DKIM
// https://postmaster.mail.ru/settings/