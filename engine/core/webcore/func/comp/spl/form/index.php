<?
// Core
use core\classes\DB\DB as DBCore;
use core\classes\validation\filesystem as fileValid;
use core\classes\request;

// Conf
use \site\conf\DIR;

// Config DIR
include '../../../../../conf/DIR.php';
include DIR::CORE . 'site/function/autoload.php';
include DIR::CORE . 'core/function/errorHandler.php';
include DIR::CORE . 'core/classes/DB/adapter/mysql/adapter.php';
// Add DB conf param
DBCore::addParam('site', \site\conf\DB::$conf);
umask(0002);
header('Content-Type: application/json');

try {

    $form = request::getVar('$form');
    if ( !isset($form['action']) || !fileValid::isSafe($form['action'])){
        new \Exception('Bad type name', 234);
    } // if

    if ( !isset($form['formId']) || !$form['formId']){
        new \Exception('Bad formId name', 235);
    } // if

    $className = 'site\core\comp\spl\form\action\\' . $form['action'];
    $classObj = new $className();

    $return = $classObj->run($form);
    $return['formId'] = $form['formId'];
    echo json_encode($return);
}catch (\Exception $ex) {

    echo json_encode(['error' => $ex->getCode(),
                     'msg' => $ex->getMessage()]);
}