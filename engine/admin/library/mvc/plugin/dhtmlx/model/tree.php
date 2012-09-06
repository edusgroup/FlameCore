<?php

namespace admin\library\mvc\plugin\dhtmlx\model;

use core\classes\DB\table;
use core\classes\filesystem;

/**
 * Description of dhtmlxTree
 * @see http://docs.dhtmlx.com/doku.php?id=dhtmlxtree:toc
 * @author Козленко В.Л.
 */
class tree {

    const FOLDER = 0;
    const FILE = 1;
    const EVENT_CHANGE_TREE = 'change_tree';

    protected static $defaultFields = array('id', 'tree_id', 'name', 'item_type');
    // TODO: Сделать нормальные доп поля для UserData
    protected static $dopFields = array();

    public static $endBrunch = null;

    /*
     * Mandatory attributes:
      text - label of the node;
      id - id of the node;
      Optional attributes:
      tooltip - tooltip of the node;
      im0 - image for a node without child items (tree will get images from the path specified in setImagePath() method);
      im1 - image for an expanded node with child items;
      im2 - image for a collapsed node with child items;
      aCol - colour of an item that is not selected;
      sCol - colour of a selected item;
      select - select a node on load (any value);
      style - text style of a node;
      open - show a node opened (any value);
      call - call function on select(any value);
      checked - check checkbox if exists (in case of 3-state checkboxes values can be 1 - checked or -1 - unsure);
      nocheckbox - instruct component, to not render checkbox for related item, optional
      child - specifies whether a node has child items (1) or not (0);
      imheight - height of the icon;
      imwidth - width of the icon;
      topoffset - offset of the item from the node above;
      radio - if not empty, child items of this node will have radio buttons.
     */

    /**
     * Получаем все объекты в виде дерева
     * @param array $pData список объектов дерева
     * @param integer $pID начальный ID
     * @return array массив объектов в виде дерева 
     */
    public static function all(array $pData, $pID, $pParam = NULL) {
        $return = array();
        $pos = 0;
        //print "<br/><br/><pre>"; var_dump($pData); print '</pre><br/><br/><br/>';
        self::_rTreeTableArray($return, $pData, $pID, $pos, $pParam);
        $return['item'] = isset($return['item']) ? $return['item'] : [];
        return array('id' => $pID, 'item' => $return['item']);
    }

    public static function setField(array $pFields) {
        self::$dopFields = $pFields;
    }

    public static function clear() {
        self::$dopFields = array();
        self::$endBrunch = null;
    }

    /**
     * Возвращает массив данных для DHTMLX
     * @param table $pTable ORM объект таблицы
     * @param mixed $pWhere условие отбора
     * @return array 
     */
    public static function createTreeOfTable(table $pTable, $pWhere = []) {
        if (\is_string($pWhere)) {
            $where = $pWhere.' AND id!=0';
        } else {
            $where = $pWhere;
            $where['id!'] = '0';
        }

        $selectFields = array_merge(self::$defaultFields, self::$dopFields);
        $data = $pTable->select($selectFields)
                ->where($where)
                ->order('tree_id, item_type, id')
                ->comment(__METHOD__)
                ->fetchAll();
        return self::all($data, 0);
        // func. createTreeOfTable
    }

    public static function createTreeOfDir(string $pDir) {
        $dirTree = filesystem::dir2tree($pDir);
        $item = array();
        if ( count($dirTree) ){
            $item = self::_rTreeTableDir($dirTree, 0, '');
        }
        return array('id' => 0, 'item' => $item);
        // fucn. createTreeOfDir
    }

    public static function getFileIdValid($pPath, string $pFile, $pException = null) {
// TODO: Возможно ли переписать на регегсп или испльзовать valid::filesystem
        $file = str_replace('..', '', $pFile);
        $path = filesystem::andEndSlash($pPath);
        $isFile = is_readable($path . $file);
        if ($pException && !$isFile) {
            throw $pException;
        }
        $file = $isFile ? $file : null;
        return $file;
        // func. getFileIdValid
    }

    public static function _rTreeTableDir(array $pDirTree, integer $pPos, string $pHistory) {
        $return = array();
        $iCount = count($pDirTree[$pPos]);
        for ($i = 0; $i < $iCount; $i++) {
            $data = $pDirTree[$pPos][$i];
// TODO: Проверить на линксе. Брать локаль у ОС
            $name = iconv("cp1251", "UTF-8", $data[filesystem::ITEM_NAME]);
            $history = $pHistory . '/' . $name;
            $return[$i] = array('id' => $history, 'text' => $name);
// Если это директория( а только директория имеет ITEM_NUM )
            $itemType = 1;
            if (isset($data[filesystem::ITEM_NUM])) {
                $itemType = 0;
                $itemNum = $data[filesystem::ITEM_NUM];
                if ($itemNum != -1) {
                    $return[$i]['item'] = self::_rTreeTableDir($pDirTree, $itemNum, $history);
                } else {
                    $return[$i]['im0'] = 'folderClosed.gif';
                }
            }

            $return[$i]['userdata'][] = array(
                'name' => 'type',
                'content' => $itemType);
        }
        return $return;
        // func. _rTreeTableDir
    }

    /**
     * Добавление дерева
     * @param table $pTable ORM таблица
     * @param string $pName название объекта
     * @param integer $pTreeId ID папки родителя
     * @param integer $pType тип объекта
     * @param string $pUserData пользовательские данные
     * @return integer ID созданного объекта
     */
    public static function add(table $pTable, string $pName, integer $pTreeId, integer $pType, $pUserData = null) {
        $id = (int) $pTable->add($pName, $pTreeId, $pType, $pUserData);
        return ['objId' => $id,
            'treeId' => $pTreeId,
            'name' => $pName,
            'type' => $pType];
        // func. add
    }

    /**
     * Построение дерева из списка
     * @param array $pDist массив назначения
     * @param array $pSource исходный массив-список
     * @param integer $pId ID текущей ветки(начальной)
     * @param integer $pPos позиция в массиве $pSource
     * @return void 
     */
    private static function _rTreeTableArray(array &$pDist, array $pSource, $pId, integer $pPos, $pParam = NULL) {
        if (!isset($pSource[$pPos])) {
            return;
        }
        $id = (string) $pId;
        // Бегаем по дереву
        $pos = 0;
        //TODO: посмотреть, можно ли прооптимизировать  for ($i = $pPos; $i < $sourceCount; $i++)
        $sourceCount = count($pSource);
        for ($i = $pPos; $i < $sourceCount; $i++) {
            $sTreeId = (string) $pSource[$i]['tree_id'];
            if ($sTreeId == $id) {
                $type = $pSource[$i]['item_type'];
                // TODO: Уменьшить имя с помощью ссылок
                $pDist['item'][$pos] = array(
                    'id' => $pSource[$i]['id'],
                    'text' => $pSource[$i]['name']
                );

                $userData = array();
                $userData[] = array('name' => 'type', 'content' => $type);
                // TODO: Сделать норм доп поля
                foreach (self::$dopFields as $item) {
                    $userData[] = [
                            'name' => $item,
                            'content' => $pSource[$i][$item]
                    ];
                }

                $pDist['item'][$pos]['userdata'] = $userData;

                $dist = &$pDist['item'][$pos];

                self::_rTreeTableArray($dist, $pSource, $dist['id'], $i + 1);
                /* if (self::$plugin) {
                  self::$plugin->endBrunch($dist, $type, $pSource, $i, $pParam);
                  } */
                /* $endBrunch = self::$endBrunch;
                  $endBrunch($dist, $type, $pSource, $i, $pParam); */
                if (self::$endBrunch) {
                    $endBrunch = self::$endBrunch;
                    $endBrunch($dist, $type, $pSource, $i, $pParam);
                } else {
                    self::endBrunch($dist, $type, $pSource, $i, $pParam);
                }
                ++$pos;
            } // if
        } // for
        // func. _rTreeTableArray
    }

    public static function endBrunch(&$pDist, $pType, $pSource, $pNum, $pParam) {
        if (!isset($pDist['item']) && ($pType == 0)) {
            $pDist['im0'] = 'folderClosed.gif';
            if (isset($pParam['child'])) {
                $pDist['child'] = '1'; //'folderClosed.gif';
            } // if isset($pParam)
        }// if isset($pDist)
        // func. endBrunch
    }

    public static function rename(table $pTable, string $pName, integer $pId) {
        $pTable->rename($pName, $pId);
        return ['objId' => $pId,
                'name' => $pName];
    }

    public static function remove(table $pTable, integer $pId) {
        if ($pId == 0){
            return [];
        }
        $list = [];

        self::_rRemove($pTable, $pId, $list);
        $pTable->delete('id=' . $pId);
        $list[] = $pId;
        return $list;
        // func. remove
    }

    private static function _rRemove(table $pTable, integer $pId, array &$pList) {
        $data = $pTable->selectAll('id', 'id!=0 and tree_id=' . $pId);
        if ($data) {
            $i_count = count($data);
            for ($i = 0; $i < $i_count; $i++) {
                $pList[] = (int) $data[$i]['id'];
                self::_rRemove($pTable, (int) $data[$i]['id'], $pList);
            } // for
        } // if
        // func. _rRemove
    }

    public static function setPluign($pPlugin) {
        self::$plugin = $pPlugin;
        // func. setPluign
    }
// class tree
}