
header('Content-Type: text/html;charset=UTF-8');
// Core
use core\classes\request;
use core\classes\dbus;
use core\classes\DB\DB as DBCore;
// Conf 
use \site\conf\DIR;
// ORM
use ORM\tree\compContTree;

// Config DIR
include '<?=self::get('siteConf')?>/conf/DIR.php';
include DIR::CORE.'site/function/autoload.php';
include DIR::CORE.'core/function/errorHandler.php';
include DIR::CORE.'core/classes/DB/adapter/mysql/adapter.php';
// Add DB conf param
DBCore::addParam('site', \site\conf\DB::$conf);

session_start();
dbus::$user = isset($_SESSION['userData']) ? $_SESSION['userData'] : null;
try{
<?
    $isUsecompContTree = self::get('isUsecompContTree');
    if ( $isUsecompContTree ){
        echo '$compContTree = new compContTree();';
    }
    $oldName = '';
    $varList = self::get('varList');
    $varNum = 0;
    foreach($varList as $name => $item ){
        if ( $item['type'] == 'tree'){
            ++$varNum;
          $treeId = $item['treeId'] == -1 ? 'dbus::$vars[\''.$oldName.'\'][\'id\']' : $item['treeId'];
echo '
    
// ====== Varible TREE('.$name.')
$name = \''.$name.'\';
dbus::$vars[$name.\'Name\'] = request::get($name);
dbus::$vars[$name] = $compContTree->selectFirst(\'id, name as caption, seoname\', array(
    \'seoname\'=>dbus::$vars[$name.\'Name\']
    ,\'tree_id\' => '.$treeId.'
)
);
if ( !dbus::$vars[$name] ){
    //echo \'ERROR: 404 \'.$name;
    header(\'Status: 404 Not Found\');
    exit;
}
';

}else
  if ( $item['type'] == 'comp'){
      echo '// ====== Varible COMP('.$name.')
$name = \''.$name.'\';
';
      echo 'dbus::$vars[$name] = '.$item['comp'];
      echo '(request::get($name)';
      if ( $varNum ){
        echo ', \''.$oldName.'\'';
      }else{
          echo ",null";
      }
      echo ",'{$item['contId']}'";
      echo ",'{$item['compId']}'";
      
      ++$varNum;
      echo ');'.PHP_EOL;
      echo 'if ( !dbus::$vars[$name] ){
    header(\'Status: 404 Not Found\');
    exit;
}
';
    
}
$oldName = $name;
}?>
}catch(Exception $ex){
    header('Status: 500 Internal Server Error');
    exit;
}