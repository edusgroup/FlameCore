<!-- DXHTML COMPONENT -->
<link href="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/dhtmlxgrid.css" rel="stylesheet" type="text/css"/>
<link href="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css" rel="stylesheet" type="text/css"/>
<link href="res/plugin/dhtmlx-2.5-pro/ext/dhtmlxgrid_pgn_bricks.css" rel="stylesheet" type="text/css">

<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>

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

                <!--<div>       
                    Тип пользователя: <select></select>
                </div>-->
                <div>
                    <div id="gridItem" style="width:700px;height:150px;"></div>
                    <div><span id="pagingArea"></span>&nbsp;<span id="infoArea"></span></div>
                </div>

            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<script type="text/javascript">
    var contrName = 'users';
    var callType = 'utils';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
   
    var usersData = {
        //grid: '<?= self::get('listXML'); ?>',
        imgTheme: '<?= self::res('images/') ?>'
    };
    
    var usersMvc = (function(){
        var options = {};
        //var gridPageCurrent = 1;
        
        // Клик по кноке Сохранить
        function saveBtnClick(){
            var json = userGrid.serializeToJSON();
            if ( json.total_count == 0 ){
                return false;
            }
            var data = 'data='+$.toJSON(json.rows);
            HAjax.saveData({method: 'saveUserData', data: data, methodType: 'POST'});
            
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
        
        /*function loadPageSuccess(pData){
            usersData.grid = pData;
            loadUserGridSuccess();
            // func. loadPage
        }
        
        function loadUserGridSuccess(){
            userGrid.clearAll();
            userGrid.parse(usersData.grid, 'xml');
            console.log(usersData.grid);
            //userGrid.currentPage = 2;
        
            for( var i = 0; i < userGrid.rowsCol.length; i++ ){
                var id = userGrid.getRowId(i);
                var img = setGridImgEdit(id);
                userGrid.cells(id, 1).setValue(img);
            }
            // func. loadUserGridSuccess
        }
        
        function gridOnBeforePageChanged(pPageCurrent, pPageSel){
            gridPageCurrent = pPageCurrent;
            //console.log(pPageCurrent+ '   ' + this.currentPage);
            if ( pPageCurrent >= pPageSel ){
                return false;
            }
            HAjax.loadPage({ 
                query:{ pageNum: pPageSel},
                dataType: 'xml'
            });
            
             
            return true;
            // func. gridonBeforePageChanged
        }*/
        
        function gridOnPageChanged(pPageCurrent){
            console.log('gridOnPageChanged:' + gridPageCurrent + ' ' + pPageCurrent);
            if ( gridPageCurrent >= pPageCurrent ){
                return false;
            }
            HAjax.loadPage({ 
                query:{ pageNum: pPageCurrent},
                dataType: 'xml'
            });
            
        }
        
        function init(pOptions){
            options = pOptions;
            
            // Кнопка Назад
            $(options.backBtn).attr('href', utils.url({
                contr:'tree'
            }));
            // Кнопка Сохранить
            $(options.saveBtn).click(saveBtnClick);
            
            HAjax.create({
                saveData: saveDataSuccess
                //,loadPage: loadPageSuccess
            });
            
            
            // Создаём таблицу
            userGrid = new dhtmlXGridObject('gridItem');
            userGrid.newId = -1;
            userGrid.setImagePath("res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/imgs/");
            userGrid.setHeader(",,Логин,Тип,Телеф.,Доступ");
            userGrid.setColTypes("ch,img,ro,ro,ro,ch");
            userGrid.setColAlign("center,center,left,left,left,center");
            userGrid.setInitWidths("32,32,*,100,100,100");
            userGrid.setColsName(['','','login', 'type', 'phone', 'enable']);
            userGrid.setSkin("light");
            userGrid.enableAutoHeight(true);
            userGrid.enablePaging(true, 5, 10, "pagingArea", true, "infoArea");
            userGrid.setPagingSkin("bricks");
            userGrid.init();
            userGrid.splitAt(1);
            
            var url = utils.url({
                method: 'loadPage', 
                query:{posStart: 0}
            });
            userGrid.loadXML(url);
            userGrid.attachEvent("onXLE", onXLE);
            
            // func. init
        }
        
        function onXLE(pGrid){
            for( var i = 0; i < pGrid.rowsCol.length; i++ ){
                var offset = ( pGrid.currentPage - 1) * this.rowsBufferOutSize;
                var id = pGrid.getRowId(i + offset);
                var img = setGridImgEdit(id);
                pGrid.cells(id, 1).setValue(img);
            } // ofr
            // func. onXLE
        }
        
        function setGridImgEdit(pId){
            return usersData.imgTheme + 'edit_16.png^Настройки^javascript:usersMvc.editUser('+pId+')^_self';
        }
        
        function editUser(pUserId){
            var url = utils.url({
                contr: 'userEdit', 
                query: { userId: pUserId }
            });
            utils.go(url);
        }
        
        return {
            init: init,
            editUser: editUser
        }  
    })();
    
    $(document).ready(function(){
        
        usersMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn',
            treeDiv: '#treeDiv'
        });         
    }); // $(document).ready

</script>