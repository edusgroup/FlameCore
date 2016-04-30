<?
namespace core\comp\spl\form\action;

// Core
use core\classes\request;

// Conf
use \site\conf\DIR as DIR_SITE;
use \site\conf\SITE as SITE_SITE;

/**
 * Функционал обратной связи
 *
 * @author Козленко В.Л.
 */
class regevent{
    public function run($formData){
        $freeData = request::post('data');
        if ( !$freeData ){
            die(json_encode(['status' => 10, 'msg' => 'Bad feedback data']));
        }
        
        /*$tmpfname = tempnam(DIR_SITE::APP_DATA.'mail/', "mail_");
        $handle = fopen($tmpfname, "w");

        $code = isset($freeData['code']) ? $freeData['code']:'';

        $jsonText = json_encode([
            'email' => SITE_SITE::FEEDBACK_EMAIL,
            'vars' => $freeData,
            'file' => 'eventdata/eventdata.json',
            'subject' => 'RegEvent '.$_SERVER['SERVER_NAME'].': '.$code,
        ]);

        fwrite($handle, $jsonText);
        fclose($handle);
        chmod($tmpfname, 0666);*/
		
		$userName = isset($freeData['name']) ? $freeData['name'] : 'Не указал';
		$userPhone = isset($freeData['phone']) ? $freeData['phone'] : 'Не указал';
		$emailPhone = isset($freeData['email']) ? $freeData['email'] : 'Не указал';

		$comment = '';
		
		include '/opt/www/FlameCore/mail/lib/class.phpmailer.php';
	
		$htmlCode = '<p>Новый запись на событие:<br/> Дата: '.date('d.m.Y').'<br/>Имя '.$userName.'<br/>Телефон: '.$userPhone.'<br/>Email: '.$emailPhone.'</p>';
		$htmlCode .= '<div>'.$comment.'</div>';

		$mail = new \PHPMailer(true);
		$mail->IsSMTP();
		$mail->host = '127.0.0.1';
		$mail->SMTPDebug = 0;                     // enables SMTP debug information (for testing)
		$mail->SMTPAuth  = false;                  // enable SMTP authentication

		//$mail->AddAddress('stepkid@yandex.ru');
		$mail->AddAddress(SITE_SITE::FEEDBACK_EMAIL);
		//$mail->SetFrom('iorator@codecampus.ru');
		$mail->SetFrom(SITE_SITE::FEEDBACK_FROM);

		$mail->CharSet = 'utf-8';
		
		$subject = 'Новая регистрация: '.SITE_SITE::FEEDBACK_FROM;
		$mail->Subject = "=?UTF-8?B?" . base64_encode(html_entity_decode($subject, ENT_COMPAT, 'UTF-8')) . "?=";
		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		$mail->MsgHTML($htmlCode);

		try{ 
			$mail->Send();
		}catch(Exception $ex){
			
		}
		
        // func. run
    }
}