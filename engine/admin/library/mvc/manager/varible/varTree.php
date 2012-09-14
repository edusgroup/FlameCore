<?php

namespace admin\library\mvc\manager\varible;

// Engine
use core\classes\DB\tree;
// ORM
use ORM\tree\routeTree;
use ORM\urlTreePropVar;
use ORM\varTree as varTreeOrm;
use ORM\tree\componentTree;
use ORM\tree\compContTree;
// Plugin 
use admin\library\mvc\plugin\dhtmlx\model\tree as dhtmlxTree;
// Model
use admin\library\mvc\manager\varible\model;
use admin\library\mvc\manager\complist\model as complistModel;

class varTree {

    /**
     * Отображение Тип дерево в переменных
     * @param type $pController
     * @param integer $pActionId 
     */
    public static function show($pController, integer $pActionId, $pInclude = null, $varCount) {

        $varTreeOrm = new varTreeOrm();
        // Получаем сохранёные данные
        $data = $varTreeOrm->selectFirst('*', 'action_id=' . $pActionId);
        // Есть ли сохранёные данные
        if ($data) {
            $compId = (int) $data['comp_id'];
            $contId = (int) $data['treeIdStat'];
            // Описание
            $pController->setVar('descrip', $data['descrip']);
            $pController->setVar('compid', $compId);
            $pController->setVar('contid', $contId);


            // Получаем дерево контена, если $compId доступен, т.е. был выбран компонент
            $contTree = $compId ? dhtmlxTree::createTreeOfTable(
                new compContTree(),
                ['comp_id' => $compId, 'isDel'=>'no']) : null;
            $pController->setJson('contTree', $contTree);
        } // if ($data)

        // Дерево компонентов
        $compTree = dhtmlxTree::createTreeOfTable(new componentTree());
        $pController->setJson('compTree', $compTree);

        if ( $varCount == 1 ){
            $varType = 'block/vartype/tree.tpl.php';
        }else{
            $varType = 'block/vartype/treefree.tpl.php';
        } // if
        
        if ($pInclude) {
            $pController->view->setBlock($pInclude, $varType);
        } else {
            $pController->view->setMainTpl($varType);
        } // if
        // func. show
    }

    // Сохранение данные, если выбран типо переменной дерево
    public static function saveData($pController, integer $pAcId) {

        // Описание переменной
        $descrip = $pController->post('descrip');
        $treeIdStat = $pController->postInt('contId');
        $compId = $pController->postInt('compId');


        $saveArr = [
            'action_id' => $pAcId,
            'comp_id' => $compId,
            'treeIdStat' => $treeIdStat,
            'descrip' => $descrip
        ];
        (new varTreeOrm())->saveExt(['action_id' => $pAcId], $saveArr);

        $routeData = ['varType' => model::VAR_TYPE_TREE];
        (new urlTreePropVar())->saveExt(['acId' => $pAcId], $routeData);

        // func. saveData
    }
// class varTree
}