<?php
namespace core\classes\DB;
use core\classes\DB\call;
/**
 * ORM дерево
 */
class tree extends table {
    const TABLE = '#tree_table';
    public function getItem(integer $pID){
        return self::select('id, tree_id, name, item_type')
                   ->where('tree_id='.$pID)
                   ->fetchAll();
    }
    
    public function add(string $pName,integer $pTreeId, integer $pType, $pUserData=''){
        //$call = new call();
        // Создаём SQL под Update, но не выполняем его
        $userSql = $pUserData ? (string)$this->update($pUserData, 'id=?', false) : '';
        return self::addTreeItem($this::TABLE, $pName, $pTreeId, $pType, $userSql);
    }
    
    /*public function getTreeUrlId(integer $pTreeId){
        //$call = new call();
        $result = $this->getTreeUrlId($this::TABLE, $pTreeId);
        //if ( !$result )
        //    throw new adapter\DBException('TreeId '.$pTreeId.' not found', 23);
        return $result;
    }*/
    
    public function rename(string $pName,integer $pId){
        self::update(array('name'=>$pName), 'id='.$pId);
    }
    
    public function isExists($pId, $pExc=null){
        return parent::isExists('id='.$pId, $pExc);
    }
    
    public function addTreeItem(string $pTable, string $pName, integer $pItemId, integer $pType, $pUserSql='') {
        self::parseProcedure('addTreeItem(:table, :name, :itemId, :type, :id, :userSql)')
                ->bindIn(':table', $pTable)
                ->bindIn(':type', $pType)
                ->bindIn(':itemId', $pItemId)
                ->bindIn(':name', $pName)
                ->bindIn(':userSql', $pUserSql)
                ->bindOut(':id');
        return self::exec()->id;
    }

    /**
     * Возвращает URL-id для ветки дерева в виде массива<br/>
     * $data = $orm::getTreeUrlById('pr_component', 32);<br/>
     * Результат: <br />
     * <table border="0">
     *      <tr><td>$data = [</td></tr>
     *      <tr><td>['name'=>'test', id=>32, 'treeId'=>12],</td></tr>
     *      <tr><td>['name'=>'blog', id=>12, 'treeId'=>0],</td></tr>
     *      <tr><td>]</td></tr>
     * </table>
     * @param string $pTable имя таблицы(tree)
     * @param int $pId ID элемента-ребёнка
     * @return array
     */
    public function getTreeUrlById(string $pTable, integer $pId) {
        self::parseProcedure('getTreeUrlById(:table, :id, :json)')
                ->bindIn(':table', $pTable)
                ->bindIn(':id', $pId)
                ->bindOut(':json');
        return json_decode(self::exec()->json, true);
    }
    
    public function getTreeParentsById(string $pTable, integer $pId) {
        self::parseProcedure('getTreeParentsById(:table, :id, :json)')
                ->bindIn(':table', $pTable)
                ->bindIn(':id', $pId)
                ->bindOut(':json');
        return json_decode(self::exec()->json, true);
    }
}
?>
