<?

namespace admin\library\mvc\plugin\dhtmlx;

// Engine
use core\classes\mvc\controllerAbstract;
use core\classes\render;
use core\classes\validation\word;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\grid as dhtmlxGrid;

/**
 * Класс для работы с компонентами DHTMLX
 *
 * @author Козленко В.Л.
 */
class dhtmlx extends controllerAbstract {

    public function init() {
        // Сюда вставить код с предворительной инициализацией данных
    }

    /**
     * Загрузка данных для DHTMLX Grid<br/>
     * Принимает <b>GET</b> параметры:<br/>
     * <b>v</b> - массив переменных
     * <b>name</b> - имя ORM класса<br/>
     * у класса должен быть определён метод:
     * getList(array) или можно задать другой метод c помощью переменной:<br/>
     * v[method]<br/>
     * В <b>loadData</b> передаётся массив <b>v</b>
     * @return void 
     */
    /*public function loadGridAction() {
        header("Content-Type: text/xml; charset=UTF-8");
        $this->view->setRenderType(render::NONE);
        // Получаем ORM имя класса
        $ormName = self::get('name');
        $className = 'ORM\\' . $ormName;
        if (!class_exists($className)) {
            return;
        }
        // Получаем массив данных
        $vars = self::get('v');
        $gridArr = model::loadData($vars, new $className);
        echo dhtmlxGrid::createXMLOfArray($gridArr);
        // func. loadGridAction
    }*/

    /**
     * Возвращает дерево в формате JSON<br/>
     * Принимаем GET параметрами:<br/>
     * name - имя ORM класса
     */
    public function loadTreeAction() {
        $this->view->setRenderType(render::JSON);
        // Получаем имя ORM класса
        $ormName = self::get('name');
        // Проверяем на валидность имени
        word::isLatin($ormName, new \Exception('Неверное имя дерева', 23));
        
        $tree = array();
        $className = 'ORM\\' . $ormName;
        if (!class_exists($className)) {
           throw new \Exception('Имя класса '.$className.' не найдено', 25);
        }
        
        $tree = dhtmlxTree::createTreeOfTable(new $className);
        self::setVar('json', $tree);
        // func. loadTreeAction
    }

    /**
     * Сохранение данных от DHTMLX Grid<br/>
     * Входные POST данные:<br/>
     * data - данные в формате JSON. Пример:
     * $_POST['data']='[{"id":"4","data":{"field1":"value1", "field2":"value2"}}]'<br/>
     * d - Дополнительные поля для insert
     * <br/>
     * Входные GET данные:<br/>
     * name - имя ORM класса
     * @throws \Exception 
     */
    /*public function saveGridAction() {
        $this->view->setRenderType(render::JSON);
        $data = self::post('data');
        $insertDopData = self::post('d', array());
        $ormName = self::get('name');
        
        // Проверяем правильно ли имя класса
        word::isNsClassName($ormName, new \Exception('Неверное имя ORM '.$ormName, 233) );

        $className = 'ORM\\' . $ormName;
        // Если такой класс 
        if (!class_exists($className)) {
            throw new \Exception('Имя класса '.$className.' не найдено', 29);
        }

        $newId = array();
        $data = json_decode($data, true);
        if ($data) {
            $newId = dhtmlxGrid::saveRows($data, $insertDopData, new $className);
        }
        self::setVar('json', array(0 => 'ok', 'newId' => $newId));
        // func. saveGridAction
    }*/

    /*public function rmGridAction() {
        $this->view->setRenderType(render::JSON);
        // Получаем имя Orm класса
        $ormName = self::get('name');
        
        // Если такой Orm класс
        $className = 'ORM\\' . $ormName;
        if (!class_exists($className)) {
            throw new \Exception('Имя класса '.$className.' не найдено', 28);
        }
        // Строка с ID, которые необходимо удалить. Разделитель: запятая
        $rowsId = self::post('row');
        $list = dhtmlxGrid::rmRows($rowsId, new $className);
        self::setVar('json', array(0 => 'ok', 'list' => $list));
        // func. rmGridAction
    }*/

}

?>