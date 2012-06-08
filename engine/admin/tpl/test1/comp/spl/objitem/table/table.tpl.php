<!-- DXHTML COMPONENT -->
<link href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<link href="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/dhtmlxgrid.css" rel="stylesheet" type="text/css"/>
<link href="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css" rel="stylesheet"
      type="text/css"/>

<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>

<!--<script src="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>
<script src="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
<script src="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_tree.js"></script>
<script src="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/ext/dhtmlxgrid_nxml.js"></script>-->

<script src="res/plugin/dhtmlx-2.5-pro/dhtmlxgrid.js"></script>
<script src="res/plugin/dhtmlx-2.5-pro/dhtmlxgridcell.js"></script>
<script src="res/plugin/dhtmlx-2.5-pro/excells/dhtmlxgrid_excell_tree.js"></script>
<script src="res/plugin/dhtmlx-2.5-pro/ext/dhtmlxgrid_nxml.js"></script>


<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>
<!-- END DXHTML COMPONENT -->

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<script src="res/plugin/classes/dhtmlx/grid.js" type="text/javascript"></script>

<!-- start panel right column -->
<div class="column">
    <!-- start panel right panel -->
    <div class="panel corners">
        <!-- start panel right title -->
        <div class="title corners_top">
            <div class="title_element">
                <a style="margin-left: 10px" href="" title="В начало">
                    <img src="<?= self::res('images/home_16x16.png') ?>" alt="В начало" width="16" height="16"
                         alt="В начало"/>
                    В начало /
                </a>
                <span id="history">{Hisotry}</span>
            </div>
        </div>
        <!-- end title -->
        <!-- start panel right content -->
        <div class="content">


            <div class="boxmenu corners">
                <ul class="menu-items">

                    <li>
                        <a href="#back" id="backBtn" title="Назад">
                            <img src="<?= self::res('images/back_32.png') ?>" alt="Назад"/><span>Назад</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=action" title="Route">
                            <img src="<?= self::res('images/action_32.png') ?>" alt="Route"/><span>Route</span>
                        </a>
                    </li>

                </ul>
            </div>


            <div class="content" id="mainpanel">

                <div id="objItemTmpBox">
                    <div class="panel corners">
                        <div class="content" id="">
                            <img id="gridAddItem" class="button" src="<?= self::res('images/plus_32.png') ?>"
                                 alt="Добавить"/>
                            <img id="gridRmItem" class="button" src="<?= self::res('images/del_24.png') ?>"
                                 alt="Удалить"/>
                            <img id="gridSaveItem" class="button" src="<?= self::res('images/save_24.png') ?>"
                                 alt="Сохранить"/>

                            <div id="gridItem" style="width:700px;height:150px;"></div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- end panel right content -->
        </div>
        <!-- end panel right content -->
    </div>
    <!-- end panel right panel -->
</div><!-- end panel right column -->
<script type="text/javascript">
    var objItem = {
        grid:null,
        imgTheme:'<?= self::res('images/') ?>',
        gridData:'<?= self::get('listXML'); ?>',
        contid: <?=self::get('contId')?>
    }

    var contrName = objItem.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);

    /**
     * Добавление в таблицу новой строки
     */
    objItem.addBtnClick = function () {
        objItem.grid.addRow(objItem.grid.newId, [0, '', '', '', 0], 0);//objItem.grid.getRowsNum());
        --objItem.grid.newId;
    }

    /**
     * Сохранение данных из таблицы
     */
    objItem.saveBtnClick = function () {
        var json = objItem.grid.serializeToJSON();
        if (json.total_count == 0) {
            return false;
        }
        var data = 'data=' + $.toJSON(json.rows);
        HAjax.saveTableItemData({data:data, methodType:'POST'});
        // func. saveBtnClick  
    }

    objItem.saveTableItemDataSuccess = function (pData) {
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        }
        var grid = objItem.grid;
        for (var oldId in pData['newId']) {
            var newId = pData['newId'][oldId];
            var img = objItem.setGridImgEdit(newId);
            grid.cells(oldId, 1).setValue(img);
            grid.changeRowId(oldId, newId);
        }
        grid.clearChangedState();
        alert('Данные сохранены');
    }

    objItem.rmBtnClick = function () {
        if (!confirm('Уверены что хотите удалить?')) {
            return false;
        }
        // Берём все выделенные строчки по 0 столбцу
        var rowsId = objItem.grid.getCheckedRows(0);
        HAjax.rmTableItem({data:{rowsId:rowsId}, methodType:'POST'});
        // func. rmBtnClick 
    }

    objItem.rmTableItemSuccess = function (pData) {
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        }
        for (var num in pData['list']) {
            objItem.grid.deleteRow(pData['list'][num]);
        }
        alert('Данные удалены');
    }

    objItem.loadGridSuccess = function (pData) {
        var grid = objItem.grid;
        grid.clearAll();
        grid.parse(pData, 'xml');

        for (var i = 0; i < grid.rowsCol.length; i++) {
            var id = grid.getRowId(i);
            var img = objItem.setGridImgEdit(id);
            grid.cells(id, 1).setValue(img);
        }
    }

    objItem.setGridImgEdit = function (pId) {
        return objItem.imgTheme + 'edit_16.png^Настройки^javascript:objItem.editobjItem(' + pId + ')^_self';
    }

    objItem.editobjItem = function (pId) {
        var url = utils.url({method:'item', query:{id:pId}});
        utils.go(url);
    }

    $(document).ready(function () {
        $('#tableBoxLoad').html($('#objItemTmpBox').html());

        // Создаём таблицу
        objItem.grid = new dhtmlXGridObject('gridItem');
        objItem.grid.newId = -1;
        objItem.grid.setImagePath("res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/imgs/");
        objItem.grid.setHeader(",,Заголовок,СЕО URL,Публ");
        objItem.grid.setColTypes("ch,img,ed,ed,ch");
        objItem.grid.setColAlign("center,center,left,left,center");
        objItem.grid.setInitWidths("32,32,*,250,50");
        objItem.grid.setColsName(['', '', 'caption', 'seoUrl', 'isPublic']);
        objItem.grid.setSkin("light");
        objItem.grid.enableAutoHeight(true);
        objItem.grid.init();

        objItem.loadGridSuccess(objItem.gridData);

        HAjax.create({
            rmTableItem:objItem.rmTableItemSuccess,
            saveTableItemData:objItem.saveTableItemDataSuccess
        });

        // OnClick на Кнопку добавить
        $('#gridAddItem').click(objItem.addBtnClick);
        // onClick на Кнопку Сохранить
        $('#gridSaveItem').click(objItem.saveBtnClick);
        // onClick на Кнопку Удалить 
        $('#gridRmItem').click(objItem.rmBtnClick);

        var url = utils.url({type:'manager', contr:'complist', query:{contid:objItem.contid}});
        $('#backBtn').attr('href', url);

        // end $(document).ready
    });

</script>