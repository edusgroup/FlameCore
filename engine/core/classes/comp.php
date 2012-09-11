<?php

namespace core\classes;

//ORM
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\compprop as compPropOrm;

// Engine
use core\classes\validation\word;

// Conf
use \DIR;

/**
 * Класс работы с компонентами. Создание объектов, получение свойств.
 *
 * @author Козленко В.Л.
 */
class comp {

    const DEFAULT_VALUE = 'default';

    /**
     * Тип файла: out - файл кастомный, создан исключительно под сайт
     */
    const FILE_OUT = 'user';
    /**
     * Тип файла: in - файл общий, создан и доступен для всех сайтов
     */
    const FILE_IN = 'in';

    /**
     * Получение молного имени класса компонента
     * @static
     * @param $pType Тип данных: 'user' или ''
     * @param $pNs namespace класса
     * @param $pDir дополнительная под директория, если есть, иначе передовать пустою строку
     * @param $pClassName имя класса
     * @return string полное имя класса с namespace
     */
    public static function getFullCompClassName($pType, $pNs, $pDir, $pClassName) {
        $prefix = '';
        if ($pType == self::FILE_OUT) {
            $prefix = 'site\\';
        }
        return $prefix . 'core\comp\\' . $pNs . $pDir . '\\' . $pClassName;
    }

    public static function getFileType($tplFile){
        // Внешний ли это файл
        $isTplFileOut = 0;
        if ( substr($tplFile, 0, 3) == '[o]' ){
            // Если внешний, то нужно убрать префикс с каждого файла
            // Он был добавлен дл того что бы были разные ID в случае совпадения в out и в in
            $tplFile = substr($tplFile, 3);
            $isTplFileOut = 1;
        } // if

        // Убираем лишний слеш
        $tplFile = substr($tplFile, 1);
        return ['isOut'=>$isTplFileOut, 'file' => $tplFile];
        // func. getFileType;
    }

    // Получение объекта по настройкам
    public static function getCompObject($pProp, $pCompProp = null) {
        if (!$pProp) {
            $pProp['classType'] = self::DEFAULT_VALUE;
            $pProp['category'] = '';
        }
        if (!$pCompProp) {
            $pCompProp = [
                'ns' => $pProp['ns'],
                'classname' => $pProp['classname']
            ];
        } // if
        //var_dumP($pProp);
        if ( $pProp['category'] && $pProp['classType'] != self::DEFAULT_VALUE ){
            global $gObjProp;
            $gObjProp['category'] = $pProp['category'].'/'.$pProp['classType'];
            $pProp['classType'] = self::DEFAULT_VALUE;
        }
        $className = 'admin\library\mvc\comp\\';
        switch ($pProp['classType']) {
            case self::DEFAULT_VALUE:
                $className .= $pCompProp['ns'] . $pCompProp['classname'];
                return new $className('', '');
            case  'user':
                $file = filesystem::getName($pProp['classUserFile']);
                $className .= $pCompProp['ns'] . 'user\\' . $file;
                return new $className('', '');
            case 'ext':
                $file = filesystem::getName($pProp['classExtFile']);
                $className .= $pCompProp['ns'] . 'ext\\' . $file;
                return new $className('', '');
        }
        throw new \Exception('Неизвестный classType', 97);
        // func. getCompObject
    }

    /**
     * Получаем первую настройку контента
     * @param integer $pContId ID контента
     * @return array
     */
    public static function getCompContProp(\integer $pContId) {

        $compContTree = new compContTree();
        // получаем список папок-родителей в виде массива
        $nodeList = $compContTree->getTreeUrlById(compContTree::TABLE, $pContId);

        $where = 'cp.parentLoad != 1';
        if ($nodeList) {
            $strTmp = '';
            foreach ($nodeList as $item) {
                $strTmp .= ',' . $item['id'];
            }
            $where .= ' AND ct.id in (' . substr($strTmp, 1) . ')';
        }
        // Находим настроку контента, ближайщую к ветке
        $propData = $compContTree
            ->select('ct.id, ct.`name` `name`, c.`name` class , c.ns'
                         . ',c.classname ,cp.*, ct.comp_id as compId', 'ct')
            ->join(componentTree::TABLE . ' c', 'ct.comp_id = c.id')
            ->joinLeftOuter(compPropOrm::TABLE . ' cp', 'cp.contId = ct.id ')
            ->where($where)
            ->order('ct.id DESC')
            ->comment(__METHOD__)
            ->fetchFirst();

        // Если настроек нет, значит надо выставить найтройки по умолчанию
        if (!$propData) {
            $propData = self::getCompPropByContId($pContId);
            $propData['classType'] = self::DEFAULT_VALUE;
            $propData['tplType'] = self::DEFAULT_VALUE;
            $propData['tplUserFile'] = '';
            $propData['tplExtFile'] = '';
            $propData['classUserFile'] = '';
            $propData['classExtFile'] = '';
            $nsPath = filesystem::nsToPath($propData['ns']);
            $categoryDir = DIR::CORE . 'admin/library/mvc/comp/'.$nsPath.'category/';
            $propData['category'] = '';
            if ( is_dir($categoryDir)){
                $propData['category'] = filesystem::getFirstObjectInFolder($categoryDir, filesystem::DIR);
            }
            $propData['noProp'] = 1;
        }else{
            $propData['noProp'] = 0;
        }
        return $propData;
        // func. getCompContProp
    }

    /**
     * Получаем информацию по контенту<br/>
     * Возвращает массив с:<br/>
     * ns - namespace. См. ORM compContTree<br/>
     * classname - имя класса. См. ORM compContTree<br/>
     * Пример:<br/>
     * array('ns'=>'objItem', 'classname'=>'spl/objItem', 'compId'=>12)
     * @param integer $pContId ID контента компонента
     * @return array
     */
    public static function getCompPropByContId(\integer $pContId) {
        $compContTree = new compContTree();
        $data = $compContTree
            ->select('c.ns, c.classname, c.id as compId', 'cc')
            ->join(componentTree::TABLE . ' c', 'c.id = cc.comp_id AND cc.id=' . $pContId)
            ->comment(__METHOD__)
            ->fetchFirst();
        return $data;
        // func. getContData
    }

    public static function getClassDataByCompId(\integer $pCompId) {
        $componentTree = new componentTree();
        return $componentTree
            ->selectFirst('ns, classname',
                          'id=' . $pCompId,
                          new \Exception('Component compId: ' . $pCompId . ' не найден', 238)
        );
        // func. getClassDataByCompIds
    }

    public static function getClassFullName($pClassFile, $pNs){
        $classNameData = comp::getFileType($pClassFile);
        $className = $classNameData['file'];
        $className = substr($className, 0, strlen($className) - 4);
        $className = str_replace('/', '\\', $className);
        word::isNsClassName(
            $className
            , new \Exception('Bad Ns name: [' . __METHOD__ . '(className=>' . $className . ')]', 23)
        );

        $classType = $classNameData['isOut'] ? comp::FILE_OUT : comp::FILE_IN;
        return comp::getFullCompClassName($classType, $pNs, 'logic', $className);
        // func. getClassFullName
    }

    // class comp
}