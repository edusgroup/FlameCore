<?
namespace admin\library\mvc\manager\tplvar;

// Engine
use core\classes\filesystem;
use core\classes\mvc\controllerAbstract;
use core\classes\render;
use core\classes\tplParser\tplBlockParser;
use core\classes\arrays;
use core\classes\event as eventsys;

// Conf
use \DIR;
use \SITE;

// ORM
use ORM\blockfile as blockFileOrm;
use ORM\tplvar as tplvarOrm;
use ORM\tree\routeTree as routeTreeOrm;

// Event
use admin\library\mvc\manager\blockItem\event as eventBlockItem;
use admin\library\mvc\manager\action\event as actionEvent;


/**
 * Работа с переменными шаблонов: Выставление значений, сохранение.
 *
 * @author Козленко В.Л.
 */
class tplvar extends controllerAbstract {

    public function init() {
    }

    /**
     * Отображение страницы по умолчанию<br/>
     * Если acid задан, то редактируются локальные переменные acid, иначе глобальные
     * <b>acid</b> int - action ID. По умолчанию acid = null.
     */
    public function indexAction() {
        $actionId = self::getInt('acid', '');
        self::setVar('acid', $actionId);

        // Получаем список файлов для WF или по всему сайту
        $blockFileOrm = new blockFileOrm();
        // Если action ID не задано, то показываем переменные по всему сайту
        if ($actionId) {
            $blockFileOrm
                ->select('bftmp.file', 'bf')
                ->join(blockFileOrm::TABLE . ' bftmp', 'bftmp.wf_id = bf.wf_id')
                ->where('bf.action_id=' . $actionId);

            $saveData = (new tplvarOrm())->selectAll('name, value', 'acId=' . $actionId);
        } else {
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
        foreach ($blockFileList as $item) {
            $tplBlockParser->parseBlock($tplDir . $item['file']);
            $varibleList = array_merge($varibleList, $tplBlockParser->getVaribleList());
        }


        // Есть ли сохранённые данные
        if ($saveData) {
            $saveData = arrays::dbQueryToAssoc($saveData);
            self::setVar('saveData', $saveData);
        } // if ( $saveData )

        self::setVar('varList', $varibleList);

        $this->view->setBlock('panel', 'block/tplvar.tpl.php');
        $this->view->setMainTpl('main.tpl.php');
        // func. indexAction
    }

    /**
     * Сохранение данных. Если acid не задан, то предпологается<br/>
     * что идёт сохранение глобальных переменных, если acid задан, то идёт сохранение
     * локальной переменной по выбранному acid<br/>
     * Принимает данные:<br/>
     * <b>acid</b> int - action ID. По умолчанию acid = null.
     * <b>var</b> [] - ассоцив. массив формата: var[varName]=varValue
     * Для JSON вызова.
     */
    public function saveDataAction() {
        $this->view->setRenderType(render::JSON);

        $actionId = self::getInt('acid', null);
        // Получаем занчение переменных,
        // var - ассоцив. массив формата: var[varName]=varValue
        $varibleList = self::post('var');
        if (is_array($varibleList)) {
            $tplvarOrm = new tplvarOrm();
            // Если $actionId задан, то это локальные переменные для конкретной ветки
            if ($actionId) {
                $tplvarOrm->delete('acid=' . $actionId);
                eventsys::callOffline(actionEvent::URLTREE, actionEvent::PROP_SAVE, ['vartype'=>1], $actionId);
                $routeTreeWhere = 'id=' . $actionId;
            } else {
                // Иначе это глобальные переменные
                $tplvarOrm->delete('acid is null');
                eventsys::callOffline(eventBlockItem::BLOCKITEM, eventBlockItem::CHANGE, ['vartype'=>1]);
                $routeTreeWhere = 'id != 0';
            } // if else

            (new routeTreeOrm())->update(['isSave' => 'yes'], $routeTreeWhere);

            // Бегаем по переменным
            foreach ($varibleList as $name => $val) {
                // Добавляем те, у которых задано значение
                if (!$val) {
                    continue;
                }
                $tplvarOrm->insert(['name' => $name,
                                   'value' => $val,
                                   'acId' => $actionId]);
            } // foreach
        } // if is_array
        // func. saveDataAction
    }

    // class tplvar
}