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

// Init
use admin\library\init\comp as compInit;

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
    /*public static function getCompObject($pProp, $pCompProp = null) {
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
            compInit::$objProp['category'] = $pProp['category'].'/'.$pProp['classType'];
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
    }*/

    /**
     * Получаем первую настройку контента в дереве т.е.
     * снизу вверх к корню и ищем любую настройка, если настройки не будет
     * то функцию вернёт значения по умолчанию.
     * @param integer $pContId ID контента
     * @return array
     */
    public static function findCompPropUpToRoot(integer $pContId) {

        $compContTree = new compContTree();
        // получаем список папок-родителей в виде массива
        $nodeList = $compContTree->getTreeUrlById(compContTree::TABLE, $pContId);
        $where = 'cp.parentLoad != 1';
        // Далее по коду мы формируем список веток к корню, получаем их ID
        if ($nodeList) {
            $strTmp = '';
            foreach ($nodeList as $item) {
                $strTmp .= ',' . $item['id'];
            }
            $where .= ' AND ct.id in (' . substr($strTmp, 1) . ')';
        }
        // Находим настроку контента, ближайщую к ветке
        // Для этого делаем выборку, сортируем по ID ветки и берём верхнюю запись
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
            //$propData['classFile'] = '';
            //$propData['tplFile'] = '';
        } // if

        return $propData;
        // func. findCompPropUpToRoot
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
    public static function getCompPropByContId(integer $pContId) {
        $data = (new compContTree())
            ->select('c.ns, c.classname, c.id as compId', 'cc')
            ->join(componentTree::TABLE . ' c', 'c.id = cc.comp_id')
            ->where('cc.id=' . $pContId)
            ->comment(__METHOD__)
            ->fetchFirst();
        return $data;
        // func. getContData
    }

    public static function getBrunchPropByContId(integer $pContId){
        $data = (new compContTree())
            ->select('c.ns, c.classname, c.id as compId, cp.*', 'cc')
            ->join(componentTree::TABLE . ' c', 'c.id = cc.comp_id')
            ->joinLeftOuter(compPropOrm::TABLE . ' cp', 'cp.contId = cc.id ')
            ->where('cc.id=' . $pContId)
            ->comment(__METHOD__)
            ->fetchFirst();
        return $data;
        //func. getBrunchPropByContId
    }

    public static function getClassDataByCompId(\integer $pCompId) {
        $exc = new \Exception('Component compId: ' . $pCompId . ' не найден', 238);
        return (new componentTree())->selectFirst('ns, classname', 'id=' . $pCompId, $exc );
        // func. getClassDataByCompIds
    }

    public static function getClassName($pClassFile){
        $classNameData = comp::getFileType($pClassFile);
        $className = $classNameData['file'];
        $className = substr($className, 0, strlen($className) - 4);
        $className = str_replace('/', '\\', $className);
        word::isNsClassName(
            $className
            , new \Exception('Bad Ns name: [' . __METHOD__ . '(className=>' . $className . ')]', 23)
        );
        $classNameData['file'] = $className;
        return $classNameData;
        // func. getClassName
    }

    public static function getClassFullName($pClassFile, $pNs){
        $classNameData = self::getClassName($pClassFile);
        $className = $classNameData['file'];

        $classType = $classNameData['isOut'] ? comp::FILE_OUT : comp::FILE_IN;
        return comp::getFullCompClassName($classType, $pNs, 'logic', $className);
        // func. getClassFullName
    }

    public static function getCompClassPath($pIsOut, $pNsPath){
        // Проверяем существование класса
        if ( $pIsOut){
            return DIR::getSiteClassCoreAdmin($pNsPath).'logic/';
        }else{
            return DIR::getCompClassPath().$pNsPath.'logic/';
        } // if
        // func. getCompClassPath
    }

    public static function createClassAdminObj($pClassFileName, $pNs){
        $classNameData = self::getClassName($pClassFileName);
        if ( $classNameData['isOut']){
            $className = '\\site\\core\\admin\\comp\\'.$pNs.'logic\\';
        }else{
            $className = '\\admin\\library\\mvc\\comp\\'.$pNs.'logic\\';
        } // if
        $className .= $classNameData['file'];
        return new $className('', '');
        // func. createClassAdminObj
    }

    public static function getCompTplPath($pIsOut, $pNsPath){
        // Проверяем существование класса
        if ( $pIsOut ){
            return DIR::getTplAdminOuter($pNsPath);
        }else{
            return DIR::getTplPath('comp/' . $pNsPath).'admin/';
        } // if
        // func. getCompTplPath
    }

    // class comp
}