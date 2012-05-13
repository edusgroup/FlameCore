<?

namespace admin\library\mvc\manager\test;

// Engine
use core\classes\mvc\controllerAbstract;
use core\classes\render;
use core\classes\word;
use core\classes\filesystem;
use core\classes\validation\filesystem as filesystemValid;
use core\classes\event as eventCore;
// ORM
use ORM\tree\routeTree;
use ORM\urlTreePropVar;
use ORM\tree\compContTree;
use ORM\tree\componentTree;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;
// Model
use admin\library\mvc\manager\complist\model as complistModel;
// Conf
use \DIR;

/**
 * Страница теста
 * 
 * @author Козленко В.Л.
 */
class test extends controllerAbstract {

	public function init(){
	}

	public function indexAction(){
		$this->view->setRenderType(render::NONE);
		//print word::wordToUrl('Человек')."<br/>";
		//print word::wordToUrl('ПЧАПОЛИНЯО');
		
		//print ini_get('mbstring.func_overload');
	
	}

// class test
}

?>