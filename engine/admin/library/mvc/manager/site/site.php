<?

namespace admin\library\mvc\manager\site;

// Engine
use core\classes\filesystem;
use core\classes\mvc\controllerAbstract;
// Conf
use \DIR;
use \core\classes\DB\DB as DBCore;
use \core\classes\DB\table;

/**
 * Страница Выбор файла
 * 
 * @author Козленко В.Л.
 */
class site extends controllerAbstract {

	public function init(){
	}

	public function indexAction(){
		//include('../conf/DB.php');
		//DBCore::addParam('admin', \ADMIN_DB::$conf);
		
		$sitesList = new table('sites');
		$list = $sitesList->setHandleName('admin')->selectList('name', 'name');
		//var_dump($list);
		
		
		
        $siteList = [];
        $siteList['list'] = $list;//filesystem::dir2array(DIR::SITE_CORE, filesystem::DIR, '/\.\w{2,3}$/i');

        self::setVar('siteList', $siteList);

        $this->view->setBlock('panel', 'block/site.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
	}

// class site
}