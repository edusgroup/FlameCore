<!-- DXHTML COMPONENT -->
<link   href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>
<!-- END DXHTML COMPONENT -->

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
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
                    
                    <div class="dt">Шаблон</div>
                    <div class="dd">
                        <a href="#tplBox" id="tplTreeBtn">
                            <img src="<?= self::res('images/folder_16.png') ?>"/>
                        </a>
                        <span id="tplTreePath"></span>
                    </div>
                    
                    <div class="dt">Доступность</div>
                    <div class="dd">
                        <input type="checkbox" name="userReg" value="1"/>
                    </div>
                    
                    <div class="dt">Группа</div>
                    <div class="dd">
                        <a href="#groupBox" id="groupTreeBtn">
                            <img src="<?= self::res('images/folder_16.png') ?>"/>
                        </a>
                    </div>
                    <div class="dd" id="relationBox"></div>

                </form>

            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<div id="groupBox" style="display: none" class="treePanel"></div>
<div id="tplBox" style="display: none" class="treePanel"></div>

<script type="text/javascript">
    var contrName = 'biUserAccess';
    var callType = 'manager';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
   
    var biUserAccessData = {
        biGroupData: <?= self::get('biGroupData') ?>,
        groupTree: <?= self::get('groupTree') ?>,
        tplTree: <?= self::get('tplTree') ?>,
        blockItemId: <?= self::get('blockItemId') ?>,
        acId: <?= self::get('acId') ?>,
        itemData: <?= self::get('itemData') ?>
    };
    
    var biUserAccessMvc = (function(){
        var options = {};
        var tree = {};
        
        // Клик по кноке Сохранить
        function saveBtnClick(){
            //var userRegId = biUserAccessData.userReg;
            var data = $(options.mainForm).serialize();
            data += '&group=' + tree.group.getAllChecked();
            data += '&tpl=' + tree.tpl.getSelectedItemId();
            
            HAjax.saveData({
                data: data,
                method: 'saveData', 
                query:{
                    blockItemId: biUserAccessData.blockItemId
                }, 
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
        
        function tplTreeDbClick(pItemId, pTreeObj){
            var text = utils.getTreeUrl(pTreeObj, pItemId);
            $(options.tplTreePath).html(text);
            $.fancybox.close();
            // func. tplTreeDbClick
        }
     
        function init(pOptions){
            options = pOptions;
            
            // Кнопка Назад
            $(options.backBtn).attr('href', utils.url({
                contr:'blockItem',
                query: {
                    id: biUserAccessData.blockItemId,
                    acid: biUserAccessData.acId
                }
            }));
            // Кнопка Сохранить
            $(options.saveBtn).click(saveBtnClick);
            
            HAjax.create({
                saveData: saveDataSuccess
            });
            
            // Создаём деревья
            dhtmlxInit.init({
                'groupTree': {
                    tree: {id:'groupBox', json: biUserAccessData.groupTree }
                    ,checkbox: true 
                },
                'tplTree': {
                    tree: {id:'tplBox', json: biUserAccessData.tplTree },
                    dbClick: tplTreeDbClick
                }
            });
            tree.group  = dhtmlxInit.tree['groupTree'];
            tree.tpl  = dhtmlxInit.tree['tplTree'];
            
            // Выставляем значение для шаблона, если есть ограничения
            var itemId = biUserAccessData.itemData['tplAccess'];
            tree.tpl.selectItem(itemId);
            // Текст возли кнопки 
            var text = utils.getTreeUrl(tree.tpl, itemId);
            $(options.tplTreePath).html(text);
            
            // Выставляем значение, Доступ только авторизованным пользователям
            var userReg = biUserAccessData.itemData['userReg'] == '1' ? 'checked' : '';
            $('input[name="userReg"]').attr('checked', userReg);
            
            // Клик по кнопке выбора групп
            $(options.groupTreeBtn).fancybox({
                "beforeClose": groupBoxBeforeClose
            });
            
            // Клик по кнопке выбора шаблона
            $(options.tplTreeBtn).fancybox();
            
            // Получаем текст с выбраными группами
            var relationBox = '';
            for( var i in biUserAccessData.biGroupData ){
                var itemId = biUserAccessData.biGroupData[i];
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
        
        biUserAccessMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn',
            mainForm: '#mainForm',
            groupTreeBtn: '#groupTreeBtn',
            tplTreeBtn: '#tplTreeBtn',
            relationBox: '#relationBox',
            tplTreePath: '#tplTreePath'
        });         
    }); // $(document).ready

</script>