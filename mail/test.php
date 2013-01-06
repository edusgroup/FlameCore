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

$data['mail'] = '23@msdfail.ru';



//