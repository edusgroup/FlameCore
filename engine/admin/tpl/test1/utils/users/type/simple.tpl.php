<!-- DXHTML COMPONENT -->
<link href="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/dhtmlxgrid.css" rel="stylesheet" type="text/css"/>
<link href="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css" rel="stylesheet" type="text/css"/>
<link href="res/plugin/dhtmlx-2.5-pro/ext/dhtmlxgrid_pgn_bricks.css" rel="stylesheet" type="text/css">

<link   href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script src="res/plugin/dhtmlx-2.5-pro/dhtmlxgrid.js"></script>
<script src="res/plugin/dhtmlx-2.5-pro/dhtmlxgridcell.js"></script>
<script src="res/plugin/dhtmlx-2.5-pro/excells/dhtmlxgrid_excell_tree.js"></script>
<script src="res/plugin/dhtmlx-2.5-pro/ext/dhtmlxgrid_nxml.js"></script> 
<script  src="res/plugin/dhtmlx-2.5-pro/ext/dhtmlxgrid_pgn.js"></script>    
<script  src="res/plugin/dhtmlx-2.5-pro/ext/dhtmlxgrid_splt.js"></script>    
<!-- END DXHTML COMPONENT -->


<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<script src="res/plugin/classes/dhtmlx/grid.js" type="text/javascript"></script>
<style>
    div .dt{font-weight: bold}
    div .dd{ padding-left: 25px}
    div.treePanel{height:218px;background-color:#f5f5f5;border :1px solid Silver; overflow:auto; width: 200px;}
</style>

<!-- start panel right column -->
<div class="column" >
    <!-- start panel right panel -->
    <div class="panel corners">
        <!-- start panel right title -->
        <div class="title corners_top">
            <div class="title_element">

                <a style="margin-left: 10px" href="" title="В начало">
                    <img src="<? self::res('images/home_16x16.png') ?>" alt="В начало" width="16" height="16" alt="В начало"/>
                    В начало /
                </a>
                <span id="history">{Hisotry}</span>
            </div>
        </div><!-- end title -->
        <!-- start panel right content -->
        <div class="content" id="mainpanel">

            <div class="boxmenu corners">
                <ul class="menu-items">

                    <li>
                        <a href="#back" id="backBtn" title="Назад">
                            <img src="<?= self::res('images/back_32.png') ?>" alt="Назад" /><span>Назад</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=wareframe" id="" title="WF">
                            <img src="<?= self::res('images/wf_32.png') ?>" alt="WF" /><span>Wareframe</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=complist" id="" title="Component List">
                            <img src="<?= self::res('images/refresh_32.png') ?>" alt="Component List" /><span>Component List</span>
                        </a>
                    </li>

                    <li>
                        <a href="#save" id="saveBtn" title="Сохранить">
                            <img src="<?= self::res('images/save_32.png') ?>" alt="Сохранить" /><span>Сохранить</span>
                        </a>
                    </li>

                </ul>
            </div>


            <div class="content">

                <form id="mainForm">
                    <div class="dt">Ник</div>
                    <div class="dd"><input type="text" name="nick"/></div>

                    <div class="dt">Логин</div>
                    <div class="dd"><input type="text" name="login"/></div>

                    <div class="dt">Пароль</div>
                    <div class="dd"><input type="password" name="pwd" value="~null~"/></div>

                    <div class="dt">Группа</div>
                    <div class="dd">
                        <a href="#groupBox" id="groupTreeBtn">
                            <img src="<?= self::res('images/folder_16.png') ?>"/>
                        </a>
                    </div>
                    <div class="dd" id="relationBox"></div>

                    <div class="dt">Телефон</div>
                    <div class="dd"><input type="text" name="phone"/></div>

                    <!--<div class="dt">Почта</div>
                    <div class="dd"><input type="text" name="email"/></div>-->

                    <div class="dt">Доступность</div>
                    <div class="dd"><input type="checkbox" name="enable" value="1"/></div>

                </form>

            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<div id="groupBox" style="display: none" class="treePanel"></div>

<script type="text/javascript">
    var contrName = 'userEdit';
    var callType = 'utils';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
   
    var simpleData = {
        userData: <?= self::get('userData') ?>,
        relation: <?= self::get('relation') ?>,
        groupTree: <?= self::get('groupTree') ?>
    };
    
    var simpleMvc = (function(){
        var options = {};
        var tree = {};
        
        // Клик по кноке Сохранить
        function saveBtnClick(){
            var userId = simpleData.userData['id'];
            var data = $(options.mainForm).serialize();
            data += '&group=' + tree.group.getAllChecked();
            
            HAjax.saveData({
                data: data,
                method: 'saveData', 
                query:{userId: userId}, 
                methodType: 'POST'
            });
            // func. saveBtnClick
        }
        
        // callback сохранения данных
        function saveDataSuccess(pData){
            if ( pData['error'] ){
                alert(pData['error']['msg']);
                return;
            }
            
            alert('Данные успешно сохранены');
            // func. saveDataSuccess
        }
        
        function groupBoxBeforeClose(){
            var relationBox = '';
            var list = tree.group.getAllChecked()
            if ( list ){
                list = list.split(',');
                for( var i in list ){
                    relationBox += tree.group.getItemText(list[i]) + ', ';
                } // for
            } // if
            $(options.relationBox).html(relationBox);
            // func. groupBoxBeforeClose
        }
     
        function init(pOptions){
            options = pOptions;
            
            // Кнопка Назад
            $(options.backBtn).attr('href', utils.url({
                contr:'users'
            }));
            // Кнопка Сохранить
            $(options.saveBtn).click(saveBtnClick);
            
            HAjax.create({
                saveData: saveDataSuccess
            });
            
            $('input[name="nick"]').val(simpleData.userData['nick']);
            $('input[name="login"]').val(simpleData.userData['login']);
            $('input[name="phone"]').val(simpleData.userData['phone']);
            var enable = simpleData.userData['enable'] == '1' ? 'checked' : '';
            $('input[name="enable"]').attr('checked', enable);
            
            dhtmlxInit.init({
                'groupTree': {
                    tree: {id:'groupBox', json: simpleData.groupTree }
                    ,checkbox: true 
                }
            });
            tree.group  = dhtmlxInit.tree['groupTree'];
            
            
            $(options.groupTreeBtn).fancybox({
                "beforeClose": groupBoxBeforeClose
            });
            
            var relationBox = '';
            for( var i in simpleData.relation ){
                var itemId = simpleData.relation[i];
                tree.group.setCheck(itemId, 1);
                relationBox += tree.group.getItemText(itemId) + ', ';
            } // for
            
            $(options.relationBox).html(relationBox);
            //
            // func. init
        }

        return {
            init: init
        }  
    })();
    
    $(document).ready(function(){
        
        simpleMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn',
            mainForm: '#mainForm',
            groupTreeBtn: '#groupTreeBtn',
            relationBox: '#relationBox'
        });         
    }); // $(document).ready

</script>