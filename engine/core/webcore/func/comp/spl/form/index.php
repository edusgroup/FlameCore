<?

// Core
use core\classes\request;
use core\classes\dbus;
use core\classes\DB\DB as DBCore;
use core\classes\validation\word;
use core\classes\validation\filesystem as fileValid;

// ORM
use ORM\tree\compContTree;

// Conf
use \DIR as DIR_ADMIN;
use \site\conf\DIR as DIR_SITE;
use \site\conf\SITE as SITE_SITE;

// Config DIR
include '../../../../../../admin/conf/DIR.php';
$httpHost = str_replace('.lo', '.ru', $_SERVER['HTTP_HOST']);
if (!include DIR_ADMIN::SITE_CORE . $httpHost . '/conf/DIR.php') {
    die('Conf file ' . $_SERVER['HTTP_HOST'] . ' not found');
}

include DIR::CORE . 'site/function/autoload.php';

include DIR::CORE . 'core/function/errorHandler.php';
include DIR::CORE . 'core/classes/DB/adapter/mysql/adapter.php';
// Add DB conf param
DBCore::addParam('site', \site\conf\DB::$conf);

header('Content-Type: application/json');

umask(0002);

try {

    $form = request::getVar('form');
    if ( !isset($form['action']) || !fileValid::isSafe($form['action'])){
        die(json_encode(['status' => 1, 'msg' => 'Bad request']));
    } // if

    $className = '\core\comp\spl\form\action\\' . $form['action'];
    if ( !@class_exists($className)){
        die(json_encode(['status' => 2, 'msg' => 'Bad form name']));
    }

   $classObj = new $className();

   $return = $classObj->run($form);
   $return['status'] = 0;
   echo json_encode($return);
}catch (\Exception $ex) {
    echo json_encode(['status' => $ex->getCode(),
                     'msg' => $ex->getMessage()]);
}