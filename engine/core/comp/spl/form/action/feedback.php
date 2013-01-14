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
class feedback{
    public function run($formData){
        $feedbackData = request::post('feedback');
        if ( !$feedbackData ){
            die(json_encode(['status' => 10, 'msg' => 'Bad feedback data']));
        }
        
        $tmpfname = tempnam(DIR_SITE::APP_DATA.'mail/', "mail_");
        $handle = fopen($tmpfname, "w");

        $subject = isset($feedbackData['title'])?$feedbackData['title']:'';

        fwrite($handle, \serialize([
                                   'email' => SITE_SITE::FEEDBACK_EMAIL,
                                   'vars' => $feedbackData,
                                   'tpl' => 'feedback/feedback',
                                   'subject' => 'Feedback '.$_SERVER['SERVER_NAME'].': '.$subject,
                                   'theme' => SITE_SITE::THEME_NAME
                                   ]));
        fclose($handle);
        chmod($tmpfname, 0666);
        // func. run
    }
}