<?

namespace admin\library\mvc\plugin\fileManager;

// Conf
use \DIR;
// Engine
use core\classes\upload;
use core\classes\word;
use core\classes\filesystem;
use core\classes\image\resize;
//use core\classes\image\imageProp;
use core\classes\validation\filesystem as fileValid;
use core\classes\render;
// ORM
use ORM\imgSizeList;
use ORM\tree\compContTree;

/**
 * @author Козленко В.Л.
 */
class fileManager {

    public function init() {
        
    }

    public function indexAction() {
        //$this->view->setRenderType(render::NONE);
        exit;
    }

    public function showFile($pContr, $fileDistPath, $filePreviewUrl, $filePublicUrl, $sizeList=array()) {
        $contr = $pContr;
        
        $filterType = $contr::get('type', model::FILTER_TYPE_IMG);
        $filter = null;
        switch ($filterType) {
            case model::FILTER_TYPE_IMG:
                $filter = model::FILTER_IMAGE;
                break;
            case model::FILTER_TYPE_ALL:
                $filter = model::FILTER_ALL;
                break;
            case model::FILTER_TYPE_FLASH:
                $filter = model::FILTER_FLASH;
                break;
            default:
                throw new \Exception('Bad type of filter type: '.__METHOD__, 232);
        }
        $contr->setVar('filterType', $filterType);
        
        // Получаем файлы и их аттребуты
        $fileList = model::getFileList($fileDistPath, $filter);
        $contr->setJson('fileList', $fileList);
        
        $funcNameCallBack = $contr::getInt('CKEditorFuncNum', -1);
        if ( $funcNameCallBack == -1 ){
            $funcNameCallBack = $contr::getInt('funcNameCallBack');
        }
        $contr->setVar('funcNameCallBack', $funcNameCallBack);
        
        $isSizeListShow = (int)count($sizeList) != 0;
        
        $contr->setVar('fileUrl', $filePublicUrl);
        $contr->setVar('sizeList', $sizeList);
        $contr->setVar('isSizeListShow', $isSizeListShow);
        
        $contr->setVar('filePreviewUrl', $filePreviewUrl);
        $contr->setVar('fileDistUrl', $filePublicUrl);
        // func. showFile
    }

}