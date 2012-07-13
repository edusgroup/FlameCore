<?php

namespace admin\library\mvc\manager\action;

// ORM
use ORM\tree\routeTree;
use ORM\urlTreePropVar;
use ORM\tree\componentTree;
use ORM\varTree as varTreeOrm;
use ORM\varComp as varCompOrm;
// Model
use admin\library\mvc\manager\varible\model as varibleModel;
use admin\library\mvc\manager\copmlist\model as complistModel;
// Plugin
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;

/**
 * Description of action
 *
 * @author Козленко В.Л.
 */
class model {

    const NONE = 0;
    const SQL = 1;
    const TREE = 2;

    public static function saveRouteData(array $pData, integer $pId) {
        $urlTreePropVar = new urlTreePropVar();
        $urlTreePropVar->save('acId=' . $pId, $pData);
    }
    
    public static function getActTree($pRouteTree) {
        dhtmlxTree::setField(array('propType'));
        dhtmlxTree::$endBrunch = function(&$pDist, $pType, $pSource, $pNum, $pParam) {
                    dhtmlxTree::endBrunch($pDist, $pType, $pSource, $pNum, $pParam);
                    switch ($pSource[$pNum]['propType']){
                        // Переменная
                        case 1: $charPref = '$';break;
                        // Функция
                        case 2: $charPref = '';break;
                        default : $charPref = '';
                    }
                    $pDist['text'] = $charPref.$pDist['text'].'['.$pSource[$pNum]['id'].']';
                    // funct. endBrunch
                };
        $actTree = dhtmlxTree::createTreeOfTable($pRouteTree, 'isDel=0');
        dhtmlxTree::clear();
        return $actTree;
    }
    
    public static function loadPropTplVar(integer $pAcId){
        $urlTreePropVar = new urlTreePropVar();
        $loadData = $urlTreePropVar->selectFirst('*', 'acId='.$pAcId);
        if ( !$loadData ){
            return null;
        }
        
        $loadData['varName'] = '';
        if ($loadData['varType'] == varibleModel::VAR_TYPE_TREE) {
            $varTreeOrm = new varTreeOrm();
            $varData = $varTreeOrm->select('concat("tree (", c.name, ")") as name', 't')
                    ->join(componentTree::TABLE . ' c', 'c.id = t.comp_id AND t.action_id=' . $pAcId)
                    ->comment(__METHOD__)
                    ->fetchFirst();
            $loadData['varName'] = $varData['name'];
        }else
        if ($loadData['varType'] == varibleModel::VAR_TYPE_COMP) {
            $varCompOrm = new varCompOrm();
            $varData = $varCompOrm->select('concat("Comp (", c.name, ")") as name', 't')
                    ->join(componentTree::TABLE . ' c', 'c.id = t.compId AND t.acId=' . $pAcId)
                    ->comment(__METHOD__)
                    ->fetchFirst();
            $loadData['varName'] = $varData['name'];
        }
        return $loadData;
    }

    public static function getRouteData(integer $pAcId) {
        $routeTree = new routeTree();
        $data = $routeTree->selectFirst('propType, robots', 'id='.$pAcId);
        $return = array();
        if ( $data['propType'] == 2){
                //self::loadPropFunc($id);
        }else{
             $return = self::loadPropTplVar($pAcId);
             // Если ли переменные в адрессе
             $varCount = $routeTree->getActVarCountById($pAcId);
             $return['varCount'] = $varCount;
        } // switch
        
        return array($data, $return);
    }

}