
<!-- DXHTML COMPONENT -->
<link href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>
<!-- END DXHTML COMPONENT -->

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

<style type="text/css">
    .bold {font-weight: bold}
    .vmiddle{vertical-align: middle; height: 40px}
    .vmiddle img{vertical-align: middle}
    .treeBlock{vertical-align:top; width:200px; height:218px;background-color:#f5f5f5;border :1px solid Silver;; overflow:auto;}
    img.img_button{cursor: pointer}
</style>

<!-- start panel right column -->
<div class="column" >
    <!-- start panel right panel -->
    <div class="panel corners">
        <!-- start panel right title -->
        <div class="title corners_top">
            <div class="title_element">

                <a style="margin-left: 10px" href="" title="В начало">
                    <img src="<?=self::res('images/home_16x16.png') ?>" alt="В начало" width="16" height="16" alt="В начало"/>
                    В начало /
                </a>
                <span id="history">{Hisotry}</span>
            </div>
        </div><!-- end title -->
        <!-- start panel right content -->
        <div class="content">


            <div class="boxmenu corners">
                <ul class="menu-items">
                    
                    <li>
                        <a href="?$t=manager&$c=action" id="" title="Route">
                            <img src="<?= self::res('images/action_32.png') ?>" alt="Route" /><span>Route</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="?$t=manager&$c=wareframe" id="" title="WF">
                            <img src="<?= self::res('images/wf_32.png') ?>" alt="Comp List" /><span>WareFrame</span>
                        </a>
                    </li>

                </ul>
            </div>


            <div class="content" id="mainpanel">
                <table>
                    <tr class="bold">
                        <td class="vmiddle img_button" style="width: 200px">
                            Компоненты
                        </td>
                        <td class="vmiddle img_button" style="width: 200px">
                            Контент
                            <img id="fileAdd" class="img_button" src="<?= self::res('images/fadd_24.png') ?>" alt="Создать страницу" />
                            <img id="dirAdd" class="img_button" src="<?= self::res('images/dadd_24.png') ?>" alt="Добавить папку" />
                            <img id="rmObj" class="img_button" src="<?= self::res('images/del_24.png') ?>" alt="Удалить объект" />
                            <img id="rename" class="img_button" src="<?= self::res('images/edit_24.png') ?>" alt="Переименовать объект" />
                            <img id="propObj" class="img_button" src="<?= self::res('images/prop_24.png') ?>" alt="Настройки" />
                        </td>
                        <td></td>
                    </tr>
                    
                    <tr>
                        <td id="compTree" class="treeBlock"></td>
                        <td id="contTree" class="treeBlock"></td>
                    </tr>
                </table>

            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<script type="text/javascript">
    var contrName = 'complist';
    var callType = 'manager';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
    
    var comp = {
        tree:{
            // Дерево с компонентами
            comp: null,
            // Дерево с данными компонентов 
            cont: null,
            data:{
                comp: null
            }
        },
        compid: <?= self::get('compId') ?>,
        contid: <?= self::get('contId') ?>,
        // Содержит старый compId, нужно что бы не делать запрос
        // при клике на один и тот же элемент
        lastCompId: null
        // Дополнительные параметры URL для contentBox
        //contBoxParam: ''
    }

    /**
     * Клик по компоненту в дереве. Подгрузка контента компонента
     */ 
    comp.tree.compClick = function(pItemId, pTree){
        var compid = pTree.getSelectedItemId();
        // Если кликаем на одном и том же элементе, то делаем ровно один запрос
        if ( comp.lastCompId == compid ){
            return;
        }
        // Если не файл, то выходим
        if ( pTree.getUserData(pItemId, 'type') != 1 )
            return;
        // загрузка контента
        HAjax.loadContTree({query: {compid: pItemId}});
        //comp.contBoxParam = '';
        //$('#tableBoxLoad').html('');
        
        // служит для проверка в начале кода, на один клик по элементу
        comp.lastCompId = compid;
    }

    comp.loadContTree = function(pData){
        // Получае дерево контента
        var contTree = comp.tree.cont
        // Очищаем старое дерево
        contTree.deleteChildItems(0);
        // Грузим новое дерево
        contTree.loadJSONObject(pData['tree']);
        
        // Если к странице вернулись, нужно открыть где мы были
        if ( comp.contid != -1 ){
            contTree.selectItem(comp.contid);
        }
        contTree.treeId = 0;
    }
    
    /**
     * Двойной клик по контенту компонента
     */
    comp.tree.contDbClick = function(){
        var compId = comp.tree.comp.getSelectedItemId();
        /*if ( comp.tree.comp.getUserData(compId, 'isTableBoxLoad') == 1 ){
            return;
        }*/
        
        var contId = comp.tree.cont.getSelectedItemId();
        var objType = comp.tree.cont.getUserData(contId, 'type');
        
        var onlyFolder = comp.tree.comp.getUserData(compId, 'onlyFolder');
        
        // Если объект не папка, выходим
        if ( objType != 1 && onlyFolder != 1 ){
            return;
        }
        
        //var classname = comp.tree.comp.getUserData(compId, 'classname');

        utils.go( utils.url({type: 'comp', contr: contId}) );
    }
    
    comp.propObjClick = function(){
        var contId = comp.tree.cont.getSelectedItemId();
        //var compId = comp.tree.comp.getSelectedItemId();
        var url = utils.url( { contr: 'compprop', query: {contid: contId}});
        utils.go(url);
    }
    
    comp.contUserUrl = function (){
        return '&compid=' + comp.tree.comp.getSelectedItemId();
    }

    comp.tree.data.cont = {
        tree:{ id: 'contTree' }
        ,dirAdd:    { url: {method: 'dirAdd'},    id: '#dirAdd',  userUrl: comp.contUserUrl }
        ,rmObj:     { url: {method: 'rmObj'},     id: '#rmObj',   userUrl: comp.contUserUrl }
        ,fileAdd:   { url: {method: 'fileAdd'},   id: '#fileAdd', userUrl: comp.contUserUrl }
        ,renameObj: { url: {method: 'renameObj'}, id: '#rename',  userUrl: comp.contUserUrl }
        ,dbClick:  comp.tree.contDbClick
      //  ,clickEnd: comp.tree.contClick
    }
    
    comp.tree.data.comp = {
        tree: { id: 'compTree', json: <?= self::get('compTree') ?> }
        ,clickEnd: comp.tree.compClick
    }

    $(document).ready(function(){
        dhtmlxInit.init({
            'comp': comp.tree.data.comp, 
            'cont': comp.tree.data.cont
        });
        
        comp.tree.cont = dhtmlxInit.tree['cont'];
        comp.tree.comp = dhtmlxInit.tree['comp'];
        
        $('#propObj').click(comp.propObjClick);

        HAjax.create({
            loadContTree: comp.loadContTree
        });
        
        // Если к странице вернулись, нужно открыть где мы были
        if ( comp.compid != -1 ){
            comp.tree.comp.selectItem(comp.compid, true);
        }

    });
</script>