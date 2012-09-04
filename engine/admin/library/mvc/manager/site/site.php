<?
namespace admin\library\mvc\manager\site;

// Engine
use core\classes\filesystem;
use core\classes\mvc\controllerAbstract;

// Conf
use \DIR;

// ORM
use ORM\sitelist as sitelistOrm;

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
        // Получаем список доступных сайтов
        $siteList['list'] = (new sitelistOrm())->selectList('name', 'name');
        self::setVar('siteList', $siteList);

        $this->view->setBlock('panel', 'block/site.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
	}

// class site
}