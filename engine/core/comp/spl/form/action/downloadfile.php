<?
namespace core\comp\spl\form\action;

// Core
use core\classes\request;

// Conf
use \site\conf\DIR as DIR_SITE;
use \site\conf\SITE as SITE_SITE;

// ORM
use \ORM\comp\spl\form\subscribeEmail as subscribeEmailORM;

/**
 * Description of subscribe
 *
 * @author Козленко В.Л.
 */
class downloadfile {

    public function run($formData, $pNotDie=false){
        $email = trim(request::post('email'));
        $name = trim(request::post('name'));

        if (!$email || !$name){
            if ( $pNotDie ){
                return;
            }else{
                die(json_encode(['status' => 10, 'msg' => 'Неверные данные']));
            }
        } // if (!$email || !$name)

        $email = substr($email, 0, 50);
        $name = substr($name, 0, 50);

        $subscribeEmailORM = new subscribeEmailORM();

        if ($subscribeEmailORM->count(['email'=>$email]) == 0){

            $type = request::post('type', 'none');
            $subscribeEmailORM->insert(['email'=>$email, 'name'=>$name, 'type'=>$type]);
        }

        //setcookie('bookEmail', 1, time()+ 60 * 60 * 24 * 365 * 24, '/');

        return [];

        // func. run
    }
    // class. downloadfile
}