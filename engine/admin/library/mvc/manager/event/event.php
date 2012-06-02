<?

namespace admin\library\mvc\manager\event;

// Engine
use core\classes\filesystem;
use core\classes\mvc\controllerAbstract;
use core\classes\render;
// Conf
use \DIR;
use \SITE;

/**
 * Страница Выбор файла
 * 
 * @author Козленко В.Л.
 */
class event extends controllerAbstract {

	public function init(){
	}

	public function indexAction(){

        $this->view->setBlock('panel', 'block/event.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
	}

    public function saveDataAction(){
        $this->view->setRenderType(render::JSON);
        $siteName = self::get('siteName');
        $path = '../FlameCore/buildsys/';
        if ( strToLower(substr(PHP_OS, 0, 3)) === 'win' ){
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path );
            $file = 'run.bat "cmd=event method=run siteName='.$siteName.'"';
        }else{
            $file = 'run.sh cmd=event method=run siteName='.$siteName;
        } // if
        exec( $path.$file );
        // func. saveDataAction
    }

// class test
}

?>