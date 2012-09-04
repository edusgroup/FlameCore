<?

namespace admin\library\mvc\manager\site;

// Engine
use core\classes\filesystem;
use core\classes\mvc\controllerAbstract;
// Conf
use \DIR;

/**
 * Страница Выбор файла
 * 
 * @author Козленко В.Л.
 */
class site extends controllerAbstract {

	public function init(){
	}

	public function indexAction(){

        $siteList = [];
        $siteList['list'] = filesystem::dir2array(DIR::SITE_CORE, filesystem::DIR, '/\.\w{2,3}$/i');

        self::setVar('siteList', $siteList);

        $this->view->setBlock('panel', 'block/site.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
	}

// class site
}