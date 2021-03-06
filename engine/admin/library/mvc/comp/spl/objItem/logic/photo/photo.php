<?php
namespace admin\library\mvc\comp\spl\objItem\logic\photo;

// Conf
use \DIR;

// Engine
use core\classes\storage\storage;
use core\classes\render;
use core\classes\event as eventCore;
use core\classes\filesystem;
use core\classes\word;
use core\classes\DB\tree;
use core\classes\admin\dirFunc;

// ORM
use ORM\comp\spl\objItem\objItem as objItemOrm;
use ORM\comp\spl\objItem\article\article as articleOrm;
use ORM\tree\compContTree;

// Model
use admin\library\mvc\comp\spl\objItem\help\model\base\model as objItemModel;
use admin\library\mvc\comp\spl\objItem\help\model\base\model as baseModel;

// Event
use admin\library\mvc\comp\spl\objItem\help\event\base\event as eventBase;
use admin\library\mvc\comp\spl\objItem\help\event\article\event as eventArticle;

/**
 * Description of article
 *
 * @author Козленко В.Л.
 */
class photo extends \core\classes\component\abstr\admin\comp implements \core\classes\component\abstr\admin\table{
    use \admin\library\mvc\comp\spl\objItem\help\table;
    use \admin\library\mvc\comp\spl\objItem\help\file;
    use \admin\library\mvc\comp\spl\objItem\help\prop;
    use \admin\library\mvc\comp\spl\objItem\help\common;


    public function init(){

    }

    /**
     * Внешний вид страница по управлению статьями
     * @throws \Exception
     */
    public function itemAction() {

        $contId = $this->contId;
        self::setVar('contId', $contId);
        $compId = $this->compId;

        // ID статьи
        $objItemId = self::getInt('id');
        self::setVar('objItemId', $objItemId, -1);

        $_SESSION['siteName'] = $_COOKIE['siteName'];
        $_SESSION['group'] = 'p'.$objItemId;

        // Получаем параметры статьи и ранее сохранёные настройки (если они есть)
        $objItemData = (new objItemOrm())
            ->select('a.*, i.*', 'i')
            ->joinLeftOuter(articleOrm::TABLE.' a', 'a.objItemId=i.id')
            ->where('i.id=' . $objItemId)
            ->fetchFirst();

        // Если данных нет, то статьи не существует
        if ( !$objItemData){
            throw new \Exception('Item Id: '.$objItemId.' not found', 234);
        }

        // Заголовок статьи
        self::setVar('caption', $objItemData['caption']);
        self::setVar('siteName', $_COOKIE['siteName']);

        // Получаем путь до папки, где храняться данные статьи
        $loadDir = baseModel::getPath($compId, $contId, $objItemId);
        $loadDir = dirFunc::getSiteDataPath($loadDir);

        self::setVar('loadDir', $loadDir);

        if (is_readable($loadDir . 'data.txt')) {
            $textData = file_get_contents($loadDir . 'data.txt');
        } // if
        self::setVar('data', $textData);

        $this->view->setBlock('panel', $this->tplFile);
        $this->view->setTplPath(dirFunc::getAdminTplPathIn('manager'));
        $this->view->setMainTpl('main.tpl.php');
        // func. itemAction
    }

    /**
     * Сохранение данных компонента.<br/>
     * Входящие GET параметры:<br/>
     * id - ID статьи. см ORM comp/spl/objItem<br/>
     * Входящие POST параметры:<br/>
     * objItem - текст статьи
     */
    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);

        $contId = $this->contId;
        $compId = $this->compId;
        // ID статьи
        $objItemId = self::postInt('id');

        eventCore::callOffline(
            eventBase::NAME,
            eventArticle::ACTION_SAVE,
            ['compId' => $compId, 'contId' => $contId],
            $objItemId
        );

        // Директория с данными статьи
        $saveDir = baseModel::getPath($compId, $contId, $objItemId);
        $saveDir = dirFunc::getSiteDataPath($saveDir);

        // Текст статьи
        $srcData = self::post('data');
        filesystem::saveFile($saveDir, 'data.txt', $srcData);
        // func. saveDataAction
    }

    public function blockItemShowAction() {
        $this->view->setRenderType(render::NONE);
        echo 'article::blockItemShowAction() | No settings in this';
        // func. blockItemShowAction
    }

    // Возвращает частную таблицу с которой работает данных класс
    public function getTableCustom(){
        return articleOrm::TABLE;
    }

    // class article
}