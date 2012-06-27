<link   href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<!--<script src="res/plugin/classes/html.js" type="text/javascript"></script>-->
<style>
    div.bothPanel{float: left; margin-right: 10px;}
    br.clearBoth { clear:both;}
    div.buttonPanel{height: 30px}
    div.treePanel{height:218px;background-color:#f5f5f5;border :1px solid Silver; overflow:auto; width: 200px;}
    #secondPanel {display: none; width: 400px}
    div .items .dt{font-weight: bold}
    div .items .dd{ padding-left: 25px}

    div.submitDiv{margin-top: 20px}
</style>

<!-- start panel right column -->
<div class="column" >
    <!-- start panel right panel -->
    <div class="panel corners">
        <!-- start panel right title -->
        <div class="title corners_top">
            <div class="title_element">
                <span><a href="/?$t=manager&$c=site"><?=self::get('$siteName')?></a></span>
            </div>
        </div><!-- end title -->

        <div>
            <div class="boxmenu corners">

                <ul class="menu-items">
                    <li>
                        <a href="/?$t=manager&$c=wareframe" title="WF">
                            <img src="<?= self::res('images/wf_32.png') ?>" alt="WF" /><span>Wareframe</span>
                        </a>
                    </li>
                    <li>
                        <a href="/?$t=manager&$c=complist" title="Component List">
                            <img src="<?= self::res('images/refresh_32.png') ?>" alt="Component List" /><span>Component List</span>
                        </a>
                    </li>
                    <li>
                        <a href="/?$t=utils&$c=tree" title="Utils">
                            <img src="<?= self::res('images/refresh_32.png') ?>" alt="Utils" /><br /><span>Utils</span>
                        </a>
                    </li>
                    <li>
                        <a id="updateAllBtn" title="Обновить все">
                            <img src="<?= self::res('images/update_32.png') ?>" alt="Update All" /><br /><span>Update All</span>
                        </a>
                    </li>
                    <li>
                        <a href="/?$t=manager&$c=event" title="События">
                            <img src="<?= self::res('images/event_32.png') ?>" alt="События" /><br /><span>События</span>
                        </a>
                    </li>
                    <li>
                        <a href="/?$t=manager&$c=tplvar" title="Переменные">
                            <img src="<?= self::res('images/refresh_32.png') ?>" alt="Переменные" /><br /><span>Переменные</span>
                        </a>
                    </li>

                </ul>

            </div>


            <div class="content" id="mainpanel">
                @TODO CREATE SHOW MSGBOX<br/>


                <div class="bothPanel">
                    <div class="buttonPanel">
                        <a href="#itemAddDiv" id="fileAdd" itemType="file"><img src="<?= self::res('images/fadd_24.png') ?>" alt="Создать страницу" /></a>
                        <a href="#itemAddDiv" id="dirAdd" itemType="dir"><img src="<?= self::res('images/dadd_24.png') ?>" alt="Создать папку" /></a>
                        <img id="rmObj"   class="img_button" src="<?= self::res('images/del_24.png') ?>"  alt="Удалить объект" />
                        <img id="rename"  class="img_button" src="<?= self::res('images/edit_24.png') ?>" alt="Переименовать объект" />
                    </div>
                    <div class="treePanel" id="acTree"></div>
                </div>

                <div class="bothPanel" id="secondPanel">

                    <div class="buttonPanel">
                        <img id="saveDataBtn" src="<?= self::res('images/save_24.png') ?>" alt="Сохранить" />
                        <img id="setUpdateBtn" src="<?= self::res('images/update_24.png') ?>" alt="Обновить" />
                    </div>

                    <div id="acParam"></div>
                </div>

                <br class="clearBoth"/>

            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->



<div id="wfBox" title="Выберите шаблон" style="display: none" class="treePanel"></div>
<div id="groupBox" style="display: none" class="treePanel"></div>

<div id="itemAddDiv" style="display: none">
    <form id="itemAddForm">
        <div>Введите имя:</div>
        <div><input type="text" name="name" id="itemName"/></div>
        <div>Тип:</div>
        <div><label><input type="radio" name="propType" value="0" checked="checked"/> Шаблон</label></div>
        <div><label><input type="radio" name="propType" value="1"/> Переменная</label></div>
        <div><label><input type="radio" name="propType" value="2"/> Функция</label></div>
        <div class="submitDiv">
            <input type="submit" value="Создать" id="itemAddSubmit"/>
        </div>
    </form>
</div>

<script type="text/javascript">
    var contrName = 'action';
    var callType = 'manager';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
   
    var action = {
        tree: {ac:null},
        acId: <?= self::get('acId') ?>,
        data: {},
        selectWFId: -1,
        // При добавлении файла или папки, хранит что добавляем
        // Нужно так как один шаблона на две сущности добавления
        itemType: null
    }
    
    var actionData = {
        group: <?= self::get('groupTree') ?>
    }
    
    action.data.actTree = <?= self::get('actTree') ?>;
    
    action.itemAddSubmitClick = function(){
        var data = $('#itemAddForm').serialize();
        if ( action.itemType == 'file' ){
            dhtmlxInit.fileAddObj(data, 'acTree');
        }else{
            dhtmlxInit.dirAddObj(data, 'acTree');
        }
        $.fancybox.close();
        return false;
    }

    action.saveDataBtnClick = function(){
        var data = $('#acForm').serialize();
        var acid = action.tree.ac.getSelectedItemId();
        
        var val = $('#contrList').val();
        if (val == '' ){
            alert('Выберите контроллер');
            return false;
        }
        
        data += '&group=' + action.tree.group.getAllChecked();
        
        HAjax.saveData({data: data, query:{acid: acid}, methodType: 'POST'});
        // func. action.saveDataData
    } 
    
    action.saveDataSuccess = function(pData){
        //$('#varBtn').unbind('click').click(action.varBtnClick);
        //$('#wfEditBtn').unbind('click').click(action.wfEditBtnClick);
        if (pData['ok']){
            alert('Данные сохранены');
        }
        // func. action.saveData
    }
    
    action.beforeShowWfBox = function(){
        var wfId = $('#wfVal').val();
        action.tree.wf.selectItem(wfId);
    }
    
    action.acTreeClick = function(pTreeId){
        $('#acParam').load(utils.url({method: 'loadProp', query: {id: pTreeId}}));
        $('#secondPanel').show();
    } // func. action.acTreeClick
    
    action.wfTreeDbClick = function(pTreeId){
        // Если кликаем по файлу
        if ( this.getUserData(pTreeId, 'type') == 1 ){
            $('#wfPath').html(utils.getTreeUrl(this, pTreeId));
            $('#wfVal').val(pTreeId);
            action.selectWFId = pTreeId;
            $.fancybox.close(); 
        }
    }
    
    action.acTreeItemAddonShow = function(){
        action.itemType = this.itemType;
    }
    
    var acTree = {
        tree:   { id:'acTree', json: action.data.actTree },
        dirAdd: { id: '#dirAdd',    url: {method: 'addDir'}}, beforeShow: action.acTreeItemAddonShow,
        fileAdd:{ id: '#fileAdd',   url: {method: 'addFile'}, beforeShow: action.acTreeItemAddonShow},
        
        rmObj:  { id: '#rmObj',     url: {method: 'rmObj'}},
        renameObj:{ id: '#rename',  url: {method: 'renameObj'}},
        clickEnd: action.acTreeClick,
        clickPanel: function(){
            //$('#secondPanel').hide();
        }
    } // var acTree
    
    var wfTree = {
        // ========================= Дерево шаблона ====================================
        tree: {id:'wfBox', json: <?= self::get('wfTree') ?> },
        // ------------- Выбор дерева шаблона -----------------------------
        dbClick: action.wfTreeDbClick
    } // var wfTree
    
    action.wfEditBtnClick = function(){
        if ( action.wfId == -1){
            if ( action.selectWFId == -1 ){
                alert('Выберите шаблон');
            }else{
                alert('Сохранить настройки');
            }
            return false;
        } // if
        
        
        // Получаем ID текущего Action
        var acId = action.tree.ac.getSelectedItemId();
        
        // Если редактирования не было сразу переходим на изменение шаблона
        var url = utils.url({contr:'wareframe', query: {acid: acId}});
        utils.go(url);
    }
    
    action.varBtnClick = function(){
        var acId = action.tree.ac.getSelectedItemId();
        // Если редактирования не было сразу переходим на изменение шаблона
        var url = utils.url({contr:'varible', query: {acid: acId}});
        utils.go(url);
    }
    
    action.groupBoxBeforeClose = function(){
        var relationBox = '';
        var list = action.tree.group.getAllChecked()
        if ( list ){
            list = list.split(',');
            for( var i in list ){
                relationBox += action.tree.group.getItemText(list[i]) + ', ';
            } // for
        } // if
        $('#relationBox').html(relationBox);
        // func. groupBoxBeforeClose
    }

    action.setUpdateBtnClick = function(){
        if ( !confirm('Добавить на обнавление?') ){
            return;
        } // if
        var query = {
            acId: action.tree.ac.getSelectedItemId()
        };
        HAjax.setUpdate({query: query});
        // action.setUpdateBtnClick
    }

    action.setUpdateSuccess = function(pData){
        if ( pData['error'] ){
            alert(pData['error']['msg']);
            return;
        }

        alert('Успешно');
        // func. action.setUpdateSuccess
    }

    action.updateAllBtn = function(pData){
        if ( !confirm('Добавить всё на обнавление?') ){
            return;
        } // if
        HAjax.setUpdate({query:{ all: 1}});
        // action.setUpdateBtnClick
    }
    
    $(document).ready(function(){
        dhtmlxInit.init({
            'acTree': acTree, 
            'wfTree': wfTree,
            'groupTree': {
                tree: {id:'groupBox', json: actionData.group }
                ,checkbox: true 
            }
        });
        action.tree.ac  = dhtmlxInit.tree['acTree'];
        action.tree.wf = dhtmlxInit.tree['wfTree'];
        action.tree.group  = dhtmlxInit.tree['groupTree'];
        
        HAjax.create({
            //loadProp: action.loadPropSuccess,
            setUpdate: action.setUpdateSuccess,
            saveData: action.saveDataSuccess
        });
        
        // Клик по кнопке создать в панели создания объекта для action
        $('#itemAddSubmit').click(action.itemAddSubmitClick);
        $('#saveDataBtn').click(action.saveDataBtnClick);
        $('#setUpdateBtn').click(action.setUpdateBtnClick);
        $('#updateAllBtn').click(action.updateAllBtn);

        if ( action.acId != -1 ){
            action.tree.ac.selectItem(action.acId, true);
        }
        
    }); // $(document).ready
    
    
    
</script>