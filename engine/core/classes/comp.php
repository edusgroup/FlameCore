<?php

namespace core\classes;

//ORM
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\compprop as compPropOrm;

// Engine
use core\classes\admin\dirFunc;

// Conf
use \DIR;


/**
 * Класс работы с компонентами. Создание объектов, получение свойств.
 *
 * @author Козленко В.Л.
 */
class comp {

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

     /**
     * Получаем первую настройку контента в дереве т.е.
     * снизу вверх к корню и ищем любую настройка, если настройки не будет
     * то функцию вернёт значения по умолчанию.
     * @param integer $pContId ID контента
     * @return array
     */
    private static function _findCompPropUpToRoot(integer $pContId) {

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
        }else{
            $where .= ' AND ct.id = '.$pContId;
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
        return $propData;
        // func. _findCompPropUpToRoot
    }

    /**
     * Получаем информацию по контенту<br/>
     * Возвращает массив с:<br/>
     * ns - namespace. См. ORM compContTree<br/>
     * classname - имя класса. См. ORM compContTree<br/>
     * Пример:<br/>
     * ['ns'=>'objItem', 'classname'=>'spl/objItem', 'compId'=>12]
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

    public static function findCompPropBytContId(integer $pContId, $ex=null){
        // Получаем настройки конкретной ветки
        $objProp = self::getBrunchPropByContId($pContId);

        if ( !$objProp['classFile'] ){
            // Если нет конкретных настроек веток, надо пройтись вверх и посмореть выше настройки по веткам
            $objProp = self::_findCompPropUpToRoot($pContId);
        }

        // Если ни чего не нашли, то берём настройки и пытаемся загрузить по умолчанию классы
        if ( !$objProp ){
            // Берём просто настройки по компоненту
            $objProp = self::getCompPropByContId($pContId);
            $objProp['classFile'] = '';
            $objProp['tplFile'] = '';
        } // if
        if ( !isset($objProp['classname']) && $ex){
            /** @var $ex \Exception */
            throw $ex;
        } // if
        return $objProp;
        // func.
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

        $classNameData['file'] = $className;
        return $classNameData;
        // func. getClassName
    }

    /**
     * Классы-админка. Возвращает путь до:
     * При $pIsOut == true - кастомных классов админки
     * При $pIsOut == false - вшитых классов админки
     * @param $pIsOut
     * @param $pNsPath
     * @return string
     */
    public static function getAdminCompClassPath($pIsOut, $pNsPath){
        // Проверяем существование класса
        if ( $pIsOut){
            return dirFunc::getAdminCompClassPathOut($pNsPath).'logic/';
        }else{
            return dirFunc::getAdminCompClassPathIn($pNsPath).'logic/';
        } // if
        // func. getAdminCompClassPath
    }

    /**
     * Классы-сборщик. Возвращает путь до:
     * При $pIsOut == true - кастомных классов сборщика(build)
     * При $pIsOut == false - вшитых классов сборщика(build)
     * @param $pIsOut
     * @param $pNsPath
     * @return string
     */
    public static function getBuildCompClassPath($pIsOut, $pNsPath){
        // Проверяем существование класса
        if ( $pIsOut){
            return dirFunc::getAdminCompClassPathOut($pNsPath).'build/';
        }else{
            return dirFunc::getAdminCompClassPathIn($pNsPath) . 'build/';
        } // if
        // func. getBuildCompClassPath
    }

    public static function getAjaxCompClassPath($pIsOut, $pNsPath){
        // Проверяем существование класса
        if ( $pIsOut){
            return dirFunc::getSiteClassCore($pNsPath).'ajax/';
        }else{
            return dirFunc::getCoreScript().'comp/'.$pNsPath.'ajax/';
        } // if
        // func. getAjaxCompClassPath
    }

    public static function fullNameClassAdmin($pClassFileName, $pNs){
        $classNameData = self::getClassName($pClassFileName);
        if ( $classNameData['isOut']){
            $className = '\\site\\core\\admin\\comp\\'.$pNs.'logic\\';
        }else{
            $className = '\\admin\\library\\mvc\\comp\\'.$pNs.'logic\\';
        } // if
        $className .= $classNameData['file'];
        return $className;
        // func. fullNameClassAdmin
    }

    public static function fullNameBuildClassAdmin($pClassFileName, $pNs){
        $classNameData = self::getClassName($pClassFileName);
        if ( $classNameData['isOut']){
            $className = '\\site\\core\\admin\\comp\\'.$pNs.'build\\';
        }else{
            $className = '\\admin\\library\\mvc\\comp\\'.$pNs.'build\\';
        } // if
        $className .= $classNameData['file'];
        return $className;
        // func. fullNameClassAdmin
    }

    public static function fullNameClassSite($pClassFileName, $pNs){
        $classNameData = self::getClassName($pClassFileName);
        if ( $classNameData['isOut']){
            $className = '\\site\\core\\site\\comp\\'.$pNs.'logic\\';
        }else{
            $className = '\\core\\comp\\'.$pNs.'logic\\';
        } // if
        $className .= $classNameData['file'];
        return $className;
        // func. fullNameClassSite
    }

    public static function fullNameVarClass($classNameData, $pNs){
        $className = $classNameData['file'];
        $className = substr($className, 0, strlen($className) - 4);
        $className = str_replace('/', '\\', $className);

        $className = '\\core\\comp\\'.$pNs.'vars\\'.$className;
        return $classNameData['isOut'] ? '\\site'.$className : $className;
        // func. fullNameVarClass
    }

    /**
     * Шаблоны-админка. Возвращает путь до:
     * При $pIsOut == true - кастомных шаблонов админки
     * При $pIsOut == false - вшитых шаблонов админки
     * @param $pIsOut
     * @param $pNsPath
     * @return string
     */
    public static function getAdminCompTplPath($pIsOut, $pNsPath){
        // Проверяем существование класса
        if ( $pIsOut ){
            return dirFunc::getAdminTplPathOut($pNsPath);
        }else{
            return dirFunc::getAdminTplPathIn('comp/' . $pNsPath).'admin/';
        } // if
        // func. getAdminCompTplPath
    }

    public static function getSiteCompClassPath($pIsOut, $pNsPath){
        if ( $pIsOut){
            return dirFunc::getSiteClassCore($pNsPath).'logic/';
        }else{
            return dirFunc::getCoreScript().'comp/'.$pNsPath.'logic/';
        } // if
        // func. getSiteCompClassPath
    }


    public static function getSiteVarClassPath($pIsOut, $pNsPath){
        if ( $pIsOut){
            return dirFunc::getSiteClassCore($pNsPath).'vars/';
        }else{
            return dirFunc::getCoreScript().'comp/'.$pNsPath.'vars/';
        } // if
        // func. getSiteVarClassPath
    }
    // class comp
}