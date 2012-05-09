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

                <div id="articleTmpBox">
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
    var article = {
        grid:null,
        imgTheme:'<?= self::res('images/') ?>',
        gridData:'<?= self::get('listXML'); ?>',
        contid: <?=self::get('contId')?>
    }

    var contrName = article.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);

    /**
     * Добавление в таблицу новой строки
     */
    article.addBtnClick = function () {
        article.grid.addRow(article.grid.newId, [0, '', '', '', 0], 0);//article.grid.getRowsNum());
        --article.grid.newId;
    }

    /**
     * Сохранение данных из таблицы
     */
    article.saveBtnClick = function () {
        var json = article.grid.serializeToJSON();
        if (json.total_count == 0) {
            return false;
        }
        var data = 'data=' + $.toJSON(json.rows);
        HAjax.saveTableItemData({data:data, methodType:'POST'});
        // func. saveBtnClick  
    }

    article.saveTableItemDataSuccess = function (pData) {
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        }
        var grid = article.grid;
        for (var oldId in pData['newId']) {
            var newId = pData['newId'][oldId];
            var img = article.setGridImgEdit(newId);
            grid.cells(oldId, 1).setValue(img);
            grid.changeRowId(oldId, newId);
        }
        grid.clearChangedState();
        alert('Данные сохранены');
    }

    article.rmBtnClick = function () {
        if (!confirm('Уверены что хотите удалить?')) {
            return false;
        }
        // Берём все выделенные строчки по 0 столбцу
        var rowsId = article.grid.getCheckedRows(0);
        HAjax.rmTableItem({data:{rowsId:rowsId}, methodType:'POST'});
        // func. rmBtnClick 
    }

    article.rmTableItemSuccess = function (pData) {
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        }
        for (var num in pData['list']) {
            article.grid.deleteRow(pData['list'][num]);
        }
        alert('Данные удалены');
    }

    article.loadGridSuccess = function (pData) {
        var grid = article.grid;
        grid.clearAll();
        grid.parse(pData, 'xml');

        for (var i = 0; i < grid.rowsCol.length; i++) {
            var id = grid.getRowId(i);
            var img = article.setGridImgEdit(id);
            grid.cells(id, 1).setValue(img);
        }
    }

    article.setGridImgEdit = function (pId) {
        return article.imgTheme + 'edit_16.png^Настройки^javascript:article.editArticle(' + pId + ')^_self';
    }

    article.editArticle = function (pId) {
        var url = utils.url({method:'item', query:{id:pId}});
        utils.go(url);
    }

    $(document).ready(function () {
        $('#tableBoxLoad').html($('#articleTmpBox').html());

        // Создаём таблицу
        article.grid = new dhtmlXGridObject('gridItem');
        article.grid.newId = -1;
        article.grid.setImagePath("res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/imgs/");
        article.grid.setHeader(",,Заголовок,СЕО URL,Публ");
        article.grid.setColTypes("ch,img,ed,ed,ch");
        article.grid.setColAlign("center,center,left,left,center");
        article.grid.setInitWidths("32,32,*,250,50");
        article.grid.setColsName(['', '', 'caption', 'seoUrl', 'isPublic']);
        article.grid.setSkin("light");
        article.grid.enableAutoHeight(true);
        article.grid.init();

        article.loadGridSuccess(article.gridData);

        HAjax.create({
            rmTableItem:article.rmTableItemSuccess,
            saveTableItemData:article.saveTableItemDataSuccess
        });

        // OnClick на Кнопку добавить
        $('#gridAddItem').click(article.addBtnClick);
        // onClick на Кнопку Сохранить
        $('#gridSaveItem').click(article.saveBtnClick);
        // onClick на Кнопку Удалить 
        $('#gridRmItem').click(article.rmBtnClick);

        var url = utils.url({type:'manager', contr:'complist', query:{contid:article.contid}});
        $('#backBtn').attr('href', url);

        // end $(document).ready
    });

</script>