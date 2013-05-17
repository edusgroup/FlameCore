<?
namespace admin\library\mvc\plugin\video;

// Conf
use \DIR;

// Engine
use core\classes\upload;
use core\classes\word;
use core\classes\filesystem;
use core\classes\image\resize;
use core\classes\validation\filesystem as fileValid;
use core\classes\render;

// ORM
use ORM\tree\compContTree;
use ORM\plugin\video as videoOrm;

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * @author Козленко В.Л.
 */
class video extends \core\classes\mvc\controllerAbstract {

    public function init() {

    }

    public function indexAction() {
        $objItemId = self::getInt('id');
        self::setVar('id', $objItemId);

        $videoList = (new videoOrm())->selectAll('*', 'objItemId='.$objItemId);
        self::setJson('videoList', $videoList);

        $this->view->setMainTpl('video/main.tpl.php');
        // func. indexAction
    }

    public function saveDataAction(){
        $this->view->setRenderType(render::JSON);
        $objItemId = self::postInt('id');
        $data = self::post('data');
        if (!is_array($data)){
            return;
        }

        $result = [];

        foreach( $data as $id=>$val){
            isset($val['type']) or die('["error":"bad data 2"]');
            $itemId = (int)substr($id,1);

            $imgId = isset($val['id']) ? $val['id'] : null;
            $txt = isset($val['txt']) ? $val['txt'] : null;

            switch($val['type']){
                case 'rm':
                    (new videoOrm())->delete('id='.$itemId);
                    break;
                case 'new':
                    $videoOrm = new videoOrm();
                    $videoOrm->insert(['imgId' => $imgId, 'txt' => $txt, 'objItemId'=>$objItemId]);
                    $newId = $videoOrm->insertId();
                    $result[$id] = $newId;
                    break;
                case 'edit':
                    (new videoOrm())->update(['imgId' => $imgId, 'txt' => $txt], 'id='.$itemId);
                    break;
            } // switch
        } // foreach

        $this->setVar('json', $result);
        // func. saveDataAction
    }

    // func. video
}