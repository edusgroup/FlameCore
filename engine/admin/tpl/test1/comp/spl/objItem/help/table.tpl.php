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

                            <div id="gridItem" style="width:900px;height:350px;"></div>
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
    var objItemData = {
        imgTheme: '<?= self::res('images/') ?>',
        gridData: '<?= self::get('listXML'); ?>',
        contid: <?=self::get('contId')?>
    }

    var contrName = objItemData.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);

    var objItemMvc = (function () {
        var options = {};
        var grid = null;

        /**
         * Добавление в таблицу новой строки
         */
        function addBtnClick() {
            grid.addRow(grid.newId, [0, '', '', '', 0], 0);//grid.getRowsNum());
            --grid.newId;
            // func. addBtnClick
        }

        /**
         * Сохранение данных из таблицы
         */
        function saveBtnClick() {
            var json = grid.serializeToJSON();
            if (json.total_count == 0) {
                return false;
            }
            var data = 'data=' + $.toJSON(json.rows);
            HAjax.saveTableItemData({data:data, methodType:'POST'});
            // func. saveBtnClick
        }

        function cbSaveTableItemDataSuccess(pData) {
            if (pData['error']) {
                alert(pData['error']['msg']);
                return;
            }

            if ( pData['seoUrl'] ){
                for (var id in pData['seoUrl']) {
                    var seoUrl = pData['seoUrl'][id];
                    grid.cells(id, 3).setValue(seoUrl);
                } // for
            } // if

            for (var oldId in pData['newId']) {
                var newId = pData['newId'][oldId];
                var img = setGridImgEdit(newId);
                grid.cells(oldId, 1).setValue(img);
                grid.changeRowId(oldId, newId);
            } // for
            grid.clearChangedState();
            alert('Данные сохранены');
            // func. cbSaveTableItemDataSuccess
        }

        function rmBtnClick() {
            if (!confirm('Уверены что хотите удалить?')) {
                return false;
            }
            // Берём все выделенные строчки по 0 столбцу
            var rowsId = grid.getCheckedRows(0);
            HAjax.rmTableItem({data:{rowsId:rowsId}, methodType:'POST'});
            // func. rmBtnClick
        }

        function cbRmTableItemSuccess(pData) {
            if (pData['error']) {
                alert(pData['error']['msg']);
                return;
            }
            for (var num in pData['list']) {
                grid.deleteRow(pData['list'][num]);
            }
            alert('Данные удалены');
            // func. cbRmTableItemSuccess
        }

        function cbLoadGridSuccess(pData) {
            if ( !pData ){
                return false;
            }
            grid.clearAll();
            grid.parse(pData, 'xml');

            for (var i = 0; i < grid.rowsCol.length; i++) {
                var id = grid.getRowId(i);
                var img = setGridImgEdit(id);
                grid.cells(id, 1).setValue(img);
            }
            // func. cbLoadGridSuccess
        }

        function setGridImgEdit(pId) {
            return objItemData.imgTheme + 'edit_16.png^Настройки^javascript:objItemMvc.editobjItem(' + pId + ')^_self';
            // func. setGridImgEdit
        }

        function editobjItem(pId) {
            var url = utils.url({method:'item', query:{id:pId}});
            utils.go(url);
            // func. editobjItem
        }

        function init(pOptions) {
            options = pOptions;
            $('#tableBoxLoad').html($('#objItemTmpBox').html());

            // Создаём таблицу
            grid = new dhtmlXGridObject('gridItem');
            grid.newId = -1;
            grid.setImagePath("res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/imgs/");
            grid.setHeader(",,Заголовок,СЕО URL,Публ");
            grid.setColTypes("ch,img,ed,ed,ch");
            grid.setColAlign("center,center,left,left,center");
            grid.setInitWidths("32,32,*,*,50");
            grid.setColsName(['', '', 'caption', 'seoUrl', 'isPublic']);
            grid.setSkin("light");
            grid.enableAutoHeight(false);
            grid.init();

            cbLoadGridSuccess(objItemData.gridData);

            HAjax.create({
                rmTableItem:cbRmTableItemSuccess,
                saveTableItemData:cbSaveTableItemDataSuccess
            });

            // OnClick на Кнопку добавить
            $('#gridAddItem').click(addBtnClick);
            // onClick на Кнопку Сохранить
            $('#gridSaveItem').click(saveBtnClick);
            // onClick на Кнопку Удалить
            $('#gridRmItem').click(rmBtnClick);

            var url = utils.url({type:'manager', contr:'complist', query:{contid:objItemData.contid}});
            $('#backBtn').attr('href', url);
            // func. init
        }

    return {
        init: init,
        editobjItem: editobjItem
    }
    // func. imgGalleryMvc
    })();

    $(document).ready(function () {
        objItemMvc.init({

        });

        // end $(document).ready
    });

</script>