<?

namespace admin\library\mvc\manager\tplvar;

// Engine
use core\classes\filesystem;
use core\classes\mvc\controllerAbstract;
use core\classes\render;
use core\classes\tplParser\tplBlockParser;
use core\classes\arrays;

// Conf
use \DIR;
use \SITE;
// ORM
use ORM\blockfile as blockFileOrm;
use ORM\tplvar as tplvarOrm;
//use ORM\tree\wareframeTree as wfTreeOrm;

/**
 * Страница Выбор файла
 * 
 * @author Козленко В.Л.
 */
class tplvar extends controllerAbstract {

	public function init(){
	}

	public function indexAction(){
        $actionId = self::getInt('acid', '');
        self::setVar('acid', $actionId);

        // Получаем список файлов для WF или по всему сайту
        $blockFileOrm = new blockFileOrm();
        // Если action ID не задано, то показываем переменные по всему сайту
        if ( $actionId != '' ){
            $blockFileOrm
                ->select('bftmp.file', 'bf')
                ->join(blockFileOrm::TABLE.' bftmp', 'bftmp.wf_id = bf.wf_id')
                ->where('bf.action_id='.$actionId);

            $saveData = (new tplvarOrm())->selectAll('name, value', 'acId='.$actionId);
        }else{
            $blockFileOrm->select('file', 'bf');
            $saveData = (new tplvarOrm())->selectAll('name, value', 'acId is null');
        }
        // Список всех шаблонов сайта по WF
        $blockFileList = $blockFileOrm->comment(__METHOD__)->fetchAll();

        // Директория, где храняться шаблоны
        $tplDir = DIR::getSiteTplPath();
        $tplBlockParser = new tplBlockParser('');
        $varibleList = [];
        // Бегаем по всем файлам
        foreach($blockFileList as $item){
            $tplBlockParser->parseBlock($tplDir.$item['file']);
            $varibleList = array_merge($varibleList, $tplBlockParser->getVaribleList());
        }


        // Есть ли сохранённые данные
        if ( $saveData ){
            $saveData = arrays::dbQueryToAssoc($saveData);
            self::setVar('saveData', $saveData);
        } // if ( $saveData )

        self::setVar('varList', $varibleList);

        $this->view->setBlock('panel', 'block/tplvar.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
	}

    public function saveDataAction(){
        $this->view->setRenderType(render::JSON);

        $actionId = self::getInt('acid', null);
        $varibleList = self::post('var');
        if ( is_array($varibleList)){
            $tplvarOrm = new tplvarOrm();
            if ( $actionId != null ){
                $tplvarOrm->delete('acid='.$actionId);
            }else{
                $tplvarOrm->delete('acid is null');
            }

            foreach( $varibleList as $name => $val ){
                if ( !$val ){
                    continue;
                }
                $tplvarOrm->insert(['name'=>$name,
                                   'value'=>$val,
                                   'acId' => $actionId]);
            } // foreach

        } // if is_array

        // func. saveDataAction
    }

// class test
}

?>