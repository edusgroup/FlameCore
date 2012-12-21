<?php
namespace admin\library\mvc\comp\spl\objItem\logic\article;

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
class article extends \core\classes\component\abstr\admin\comp implements \core\classes\component\abstr\admin\table{
    use \admin\library\mvc\comp\spl\objItem\help\table;
    use \admin\library\mvc\comp\spl\objItem\help\file;
    use \admin\library\mvc\comp\spl\objItem\help\prop;


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

        // Все параметры статьи
        self::setJson('objItemData', $objItemData);
        // Заголовок статьи
        self::setVar('caption', $objItemData['caption']);

        // Получаем путь до папки, где храняться данные статьи
        $loadDir = baseModel::getPath($compId, $contId, $objItemId);
        $loadDir = dirFunc::getSiteDataPath($loadDir);

        $textData = '';
        if (is_readable($loadDir . 'data.txt')) {
            $textData = file_get_contents($loadDir . 'data.txt');
        } // if
        if (is_readable($loadDir . 'kat.txt')) {
            $textKat = file_get_contents($loadDir . 'kat.txt');
            if ($textKat) {
                $textData = $textKat . '<hr />' . $textData;
            } // if
        } // if is_readable
        self::setVar('text', $textData);

        if (is_readable($loadDir . 'minidescr.txt')) {
            self::setVar('miniDescrText', file_get_contents($loadDir . 'minidescr.txt'));
        } // if is_readable

        // Загружаем данные клоакинга
        if ($objItemData['isCloaking']) {
            self::setVar('cloakingText', file_get_contents($loadDir . 'cloak.txt'));
        }

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
        $seoKeywords = self::post('seoKeywords');
        $seoDescr = self::post('seoDescr');

        // Статья клоакинга
        $cloakingText = self::post('cloakingText');

        // Сохраняем превью изображения для статьи
        $prevImgUrl = self::post('prevImgUrl');
        (new articleOrm())->saveExt(
            [ 'objItemId' => $objItemId ]
            ,['prevImgUrl' => $prevImgUrl,
             'seoKeywords' => $seoKeywords,
             'isCloaking' => trim($cloakingText) != '',
             'seoDescr' => $seoDescr]
        );

        // TODO: добавить настройку фильтрации кода HTML
        //class_exists('admin\library\comp\spl\objItem\htmlvalid\full');
        //htmlValid::validate($data);

        // Текст статьи
        $srcData = self::post('objItem');
        $distData = $srcData;
        $katData = '';

        $miniDescr = self::post('miniDescrText');

        $pos = strpos($srcData, '<hr />');
        if ($pos !== false) {
            $katData = substr($srcData, 0, $pos);
            $distData = substr($srcData, $pos + 6);
        }
        filesystem::saveFile($saveDir, 'kat.txt', $katData);
        filesystem::saveFile($saveDir, 'data.txt', $distData);
        filesystem::saveFile($saveDir, 'cloak.txt', $cloakingText);
        filesystem::saveFile($saveDir, 'minidescr.txt', $miniDescr);
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

    /* Настройка метода для buildsys\library\event\comp\spl\objItem\article\model

    */
    public function saveDataInfo($pObjItemId, $pObjItemOrm){
        $objItemData = $pObjItemOrm
            ->select('i.id, i.seoUrl, i.treeId, i.caption, a.prevImgUrl, i.isPublic'
                         . ',cc.seoName, cc.name category, a.seoKeywords, a.seoDescr, a.isCloaking'
                         . ',DATE_FORMAT(i.date_add, "%Y-%m-%dT%h:%i+04:00") as dateISO8601'
                         . ',DATE_FORMAT(i.date_add, "%d.%m.%y %H:%i") date_add, i.date_add dateunf, a.urlTpl', 'i')
            ->joinLeftOuter(articleOrm::TABLE. ' a', 'i.id=a.objItemId')
            ->join(compContTree::TABLE . ' cc', 'i.treeId=cc.id')
            ->where('i.id=' . $pObjItemId)
            ->comment(__METHOD__)
            ->fetchFirst();


        // Данные предыдушей статьи
        $return['prev'] = $pObjItemOrm
            ->select('i.id, i.seoUrl, i.caption, cc.seoName, a.urlTpl', 'i')
            ->joinLeftOuter(articleOrm::TABLE. ' a', 'i.id=a.objItemId')
            ->join(compContTree::TABLE . ' cc', 'i.treeId=cc.id')
            ->where(
            'date("' . $objItemData['dateunf'] . ' ") >= date(i.date_add)
                AND i.isPublic = "yes"
                AND i.isDel = 0
                And i.treeId = ' . $objItemData['treeId'] . '
                AND i.id < ' . $objItemData['id'])
            ->order('i.date_add DESC, i.id desc')
            ->fetchFirst();

        // Данные следующей статьи
        $return['next'] = $pObjItemOrm
            ->select('i.id, i.seoUrl, i.caption, cc.seoName, a.urlTpl', 'i')
            ->joinLeftOuter(articleOrm::TABLE. ' a', 'i.id=a.objItemId')
            ->join(compContTree::TABLE . ' cc', 'i.treeId=cc.id')
            ->where(
            'date("' . $objItemData['dateunf'] . ' ") <= date(i.date_add)
                AND i.isPublic = "yes"
                AND i.isDel = 0
                And i.treeId = ' . $objItemData['treeId'] . '
                AND i.id > ' . $objItemData['id'])
            ->order('i.date_add ASC')
            ->fetchFirst();

        $objItemData['canonical'] = sprintf($objItemData['urlTpl'], $objItemData['seoName'], $objItemData['seoUrl']);

        unset($objItemData['seoUrl'], $objItemData['urlTpl'], $objItemData['treeId'], $objItemData['dateunf']);

        $return['obj'] = $objItemData;

        return $return;
        // func. saveDataInfo
    }

    // class article
}