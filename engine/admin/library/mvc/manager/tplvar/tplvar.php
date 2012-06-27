<?

namespace admin\library\mvc\manager\tplvar;

// Engine
use core\classes\filesystem;
use core\classes\mvc\controllerAbstract;
use core\classes\render;
use core\classes\tplParser\tplBlockParser;

// Conf
use \DIR;
use \SITE;
// ORM
use ORM\blockfile as blockFileOrm;
use ORM\tree\wareframeTree as wfTreeOrm;

/**
 * Страница Выбор файла
 * 
 * @author Козленко В.Л.
 */
class tplvar extends controllerAbstract {

	public function init(){
	}

	public function indexAction(){
        $actionId = self::getInt('acid', null);

        // Получаем список файлов для WF или по всему сайту
        $blockFileOrm = new blockFileOrm();
        // Если action ID не задано, то показываем переменные по всему сайту
        if ( $actionId != null ){
            $blockFileOrm
                ->select('bftmp.file', 'bf')
                ->join(blockFileOrm::TABLE.' bftmp', 'bftmp.wf_id = bf.wf_id')
                ->where('bf.action_id='.$actionId);
        }else{
            $blockFileOrm->select('file', 'bf');
        }
        $blockFileList = $blockFileOrm->comment(__METHOD__)->fetchAll();

        $tplDir = DIR::getSiteTplPath();
        $tplBlockParser = new tplBlockParser('');

        $varibleList = [];
        // Бегаем по всем файлам
        foreach($blockFileList as $item){
            $tplBlockParser->parseBlock($tplDir.$item['file']);
            $varibleList = array_merge($varibleList, $tplBlockParser->getVaribleList());
        }

        self::setVar('varList', $varibleList);

        $this->view->setBlock('panel', 'block/tplvar.tpl.php');
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