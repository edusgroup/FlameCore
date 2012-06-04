<?php

namespace core\classes;

//ORM
use ORM\tree\componentTree;
use ORM\tree\compContTree;
use ORM\compprop as compPropOrm;

/**
 * Класс работы с компонентами. Создание объектов, получение свойств.
 *
 * @author Козленко В.Л.
 */
class comp {

    const DEFAULT_VALUE = 'default';

    public static function getFullCompClassName($pType, $pNs, $pDir, $pClassName){
        $prefix = '';
        if ( $pType == 'user'){
            $prefix = 'site\\';
        }
        return $prefix.'core\comp\\' . $pNs .$pDir.'\\'. $pClassName;
    }

    // Получение объекта по настройкам
    public static function getCompObject($pProp, $pCompProp = null) {
        if (!$pProp) {
            $pProp['classType'] = self::DEFAULT_VALUE;
        }
        if (!$pCompProp) {
            $pCompProp = ['ns' => $pProp['ns'],
                          'classname' => $pProp['classname']
            ];
        } // if
        $className = 'admin\library\mvc\comp\\';
        if ($pProp['classType'] == self::DEFAULT_VALUE) {
            $className .= $pCompProp['ns'] . $pCompProp['classname'];
            return new $className('', '');
        } else
            if ($pProp['classType'] == 'user') {
                $file = filesystem::getName($pProp['classUserFile']);
                $className .= $pCompProp['ns'] . 'user\\' . $file;
                return new $className('', '');
            } else
                if ($pProp['classType'] == 'ext') {
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
     * array('ns'=>'article', 'classname'=>'spl/article', 'compId'=>12)
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

    // class comp
}