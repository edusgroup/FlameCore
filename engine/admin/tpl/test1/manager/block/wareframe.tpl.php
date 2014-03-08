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
<script src="res/plugin/dhtmlx-2.5-pro/ext/dhtmlxgrid_drag.js"></script>


<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>
<!-- END DXHTML COMPONENT -->

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<script src="res/plugin/classes/dhtmlx/grid.js" type="text/javascript"></script>

<style>
    .containerTableStyle {
        overflow: auto;
        position: relative;
        top: 0;
        font-size: 12px;
        background-color: #f5f5f5;
        border: 1px solid Silver;
    }
</style>


<style type="text/css">
    .bold {
        font-weight: bold
    }

    .vmiddle {
        vertical-align: middle;
        height: 40px
    }

    .vmiddle img {
        vertical-align: middle
    }

    .treeBlock {
        vertical-align: top;
        width: 200px;
        height: 218px;
        border: 1px solid Silver;

    }

    img.button {
        cursor: pointer
    }

        /*.blockItem{border-collapse: collapse; border: 1px solid black}
        .blockItem tr td {
            border: 1px solid black;
            background: #FFC;
            border-collapse: collapse;
        }

        .blockItemChild td{
            background-color: greenyellow ! important;
        }*/
</style>

<div class="column">
    <div class="panel corners">

        <div class="title corners_top">
            <div class="title_element">
                <span id="history">{HISTORY}</span>
            </div>
        </div>

        <div class="boxmenu corners">
            <ul class="menu-items">
                <li>
                    <a href="?$t=manager&$c=action" title="Route">
                        <img src="<?= self::res('images/action_32.png') ?>" alt="Route"/><span>Route</span>
                    </a>
                </li>

                <li>
                    <a href="?$t=manager&$c=complist" title="Comp List">
                        <img src="<?= self::res('images/refresh_32.png') ?>" alt="Comp List"/><span>Comp List</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="content" id="mainpanel">
            <div>Шаблоны: <?=self::get('tplPath');?></div>

            <table>
                <tr class="bold">
                    <td class="vmiddle button tdWF">
                        Страницы
                        <img id="fileAdd" class="button" src="<?= self::res('images/fadd_24.png') ?>"
                             alt="Создать страницу"/>
                        <img id="dirAdd" class="button" src="<?= self::res('images/dadd_24.png') ?>"
                             alt="Удалить папку"/>
                        <img id="rmObj" class="button" src="<?= self::res('images/del_24.png') ?>"
                             alt="Удалить объект"/>
                        <img id="rename" class="button" src="<?= self::res('images/edit_24.png') ?>"
                             alt="Удалить объект"/>
                    </td>
                    <td class="vmiddle button">
                        Блоки
                        <img id="blockSave" class="button" src="<?= self::res('images/save_24.png') ?>"
                             alt="Сохранить"/>
                        <img id="blockRm" class="button" src="<?= self::res('images/del_24.png') ?>" alt="Очистить"/>

                        <img id="linkBtn" class="button" src="<?= self::res('images/btn/link_24.png') ?>" alt="Связать" href="#linkDlg"/>
                        <img id="linkRmBtn" class="button" src="<?= self::res('images/btn/linkRm_24.jpg') ?>" alt="Удалить связь" href="#linkRmDlg"/>

                    </td>
                    <td>ФС</td>
                </tr>
                <tr>
                    <td id="wfTree" class="treeBlock tdWF"></td>
                    <td id="blockTree" class="treeBlock"></td>
                    <td id="fsTree" class="treeBlock"></td>
                </tr>
            </table>
        </div>

    </div>
</div>

<div class="column">
    <div class="panel corners">
        <div class="content" id="">
            <div>
                <img id="itemAdd" class="button" src="<?= self::res('images/plus_32.png') ?>" alt="Добавить"/>
                <img id="itemRm" class="button" src="<?= self::res('images/del_24.png') ?>" alt="Удалить"/>
                <img id="blockItemSave" class="button" src="<?= self::res('images/save_24.png') ?>" alt="Сохранить"/>
            </div>
            <div id="blockItemGrid" style="width:700px;height:250px;"></div>
        </div>
    </div>
</div>

<div id="compTree" style="width:200px;height:300px;"></div>
<div id="linkDlg" style="display:none">
	<div id="linkWfTreeBox" class="treeBlock" style="float: left;"></div>
    <div id="linkBlockTreeBox" class="treeBlock" style="float: left;"></div>
</div>

<script type="text/javascript">

var contrName = 'wareframe';
var callType = 'manager';
utils.setType(callType);
utils.setContr(contrName);
HAjax.setContr(contrName);
HAjax.setType(callType);


var wareframeMvc = (function(){
    // Настроечные опции
    var options;
    // ID нового компонента, дикриментируется в itemAddBtnClick
    var itemNewId = -1;
    // ID для нового блока
    var blockNewId = -1;
    // Таблица с компонентами
    var itemGrid;
    // Дерево блоков
    var blockTree;
    // Дерево wareframe
    var wfTree;
    // Дерево шаблонов
    var fsTree;
    // Дерево wf для linkItem( не создаётся автоматически
    var mainLinkTree = null;
    // Дерево block для linkItem( не создаётся автоматически
    var blockLinkTree = null;
    // Буффер для новых линков, очищается при сохранении блоков
    var linkBlockBuff = {};

	// Шаблон без компонентов, можно добивить новый шаблон или новые компоненты
    var FOLDER_FREE = 0;
    var FOLDER_COMP = 1;
    var FOLDER_TPL = 2;
	// Шаблон без блоков в шаблоне, пустая папка
    var FOLDER_EMPTY = 3;
    var FOLDER_LINK = 4;

    // Буфер файлов для блоков. Вспомогательная переменная
    var fileBlockBuffer = {};
    // Буффер. Какие блоки нужно удалить
    var blockRmBuff = [];
    // Осуществляет ли перенос компонента по таблице
    var isItemDradDrop = false;
    // Какую ветку нужно выделять в linkBlockTree, если известно какая эта ветка( после сохранения уже известно )
    var linkBlockBrunchSelect = null;

    /**
     * Уладение блока в дереве
     */
    function blockRmBtnClick() {
        var blockItemId = blockTree.getSelectedItemId();
        blockItemId = blockItemId ? blockItemId : 0;

        var type = blockTree.getUserData(blockItemId, 'type');
        var itemAcId = blockTree.getUserData(blockItemId, 'acId');

        itemAcId = itemAcId ? itemAcId : '';
        if (itemAcId != wareframeData.acId) {
            alert('Нельзя удалить родительский объект');
            return;
        } // if

        if (type == FOLDER_COMP) {
            alert('В блоке находятся компоненты');
            return;
        } // if

        // Проверка пустая ли папка
        if (type == FOLDER_FREE) {
			alert('Блок и так пустой');
            return;
        } // if

        // Запрашиваем удаление папки
        if (!confirm('Очистить ветку?')) {
            return;
        } // if

        // Не пустой ли блок это
        /// Пустая ли папка, т.е если ли другие приклеплённые объкты внутри
        if (type != FOLDER_TPL && type != FOLDER_EMPTY ) {
            alert('В Блока находятся другие блоки');
            return;
        }

        // Если это пустой шаблон
        var childCount = blockTree.hasChildren(blockItemId);
        if (childCount == 0) {
            var parentId = blockTree.getParentId(blockItemId);
            blockTree.deleteItem(blockItemId, true);
            blockTree.setItemImage(parentId, 'folderEmpty.gif');
            blockTree.setUserData(parentId, 'type', FOLDER_FREE);
            if (!isNaN(parseInt(parentId))) {
                var delId = parentId == 0 ? blockItemId.split(':')[1] : parentId;
                blockRmBuff.push(delId);
            }
            var fileId = blockTree.getUserData(parentId, 'fileId');
            fileId = fileId == '' ? parentId.split(':')[1] : fileId;
            for (var i in fileBlockBuffer) {
                if (fileBlockBuffer[i].fileId == fileId) {
                    delete fileBlockBuffer[i];
                } // if
            } // for

            return;
        } // if Очистка пустого шаблона

        // Можно ли папку удалить
        if (!isBlockRm(blockTree, blockItemId)) {
            return;
        }

        // Добавляет в буффер, только те которые созданы и сохранены
        var delId = parseInt(blockItemId);
        if (!isNaN(delId) && delId >= 0){
            blockRmBuff.push(blockItemId);
        }

        var fileId = blockTree.getUserData(blockItemId, 'fileId');
        fileId = fileId == '' ? blockItemId.split(':')[1] : fileId;

        for (var i in fileBlockBuffer) {
            if (fileBlockBuffer[i].fileId == fileId) {
                delete fileBlockBuffer[i];
            } // if
        } // for

        // Делаем пустой папкой
        blockTree.setUserData(blockItemId, 'type', FOLDER_FREE);
        // Удаляем дочернии ветки
        blockTree.deleteChildItems(blockItemId);
        // Выставляем картинки - пустая папка
        blockTree.setItemImage(blockItemId, 'folderEmpty.gif');
        // func. blockRmBtnClick
    }

    /**
     * Сохранение информации по блокам
     */
    function blockSaveBtnClick() {
        if (!confirm('Сохранить?')) {
            return;
        }
        // TODO: вставить проверку на то что, что то есть в блоках
        var wfId = getWFId();
        var acId = wareframeData.acId;

        var file = $.toJSON(fileBlockBuffer);
        var del = $.toJSON(blockRmBuff);
        var link = $.toJSON(linkBlockBuff);
        HAjax.saveBlock({data:{file:file, wfid:wfId, acid:acId, del:del, link: link}, methodType:'POST'});
        // func. blockSaveBtnClick
    }

    /**
     * Удаление данных в таблице компонентов
     */
    function itemRmBtnClick() {
        var blId = blockTree.getSelectedItemId();
        var type = blockTree.getUserData(blId, 'type');
        var linkData = blockTree.getUserData(blId, 'link');
        if ( type == FOLDER_LINK && ( wareframeData.acId == '0' || linkData.acId != '0') ){
            alert('В ветке типа LINK-wf нельзя производить сохранение');
            return false;
        } // if
        // Запрашиваем удаление папки
        if (!confirm('Удалить элементы?')) {
            return;
        }
        var rowsId = itemGrid.getCheckedRows(0);
        HAjax.rmBlockItem({
            data:{
                idlist:rowsId,
                blid:blId,
                acid: wareframeData.acId,
                wfid: getWFId()
            },
            methodType:'POST'});
        // func. itemRmBtnClick
    }

    /**
     * Добавление нового компонента в блок
     */
    function itemAddBtnClick() {
        var blockId = blockTree.getSelectedItemId();
        if (blockId == '') {
            alert('Выбирите блок');
            return;
        }
        if (blockId < 0) {
            alert('Сохраните блоки');
            return;
        }

        var type = blockTree.getUserData(blockId, 'type');
        var linkData = blockTree.getUserData(blockId, 'link');
        // Пропускаем только линки wareframe и только в окне редактирования action->wf
        if ( type == FOLDER_LINK && ( wareframeData.acId == '0' || linkData.acId != '0') ){
            alert('В ветку типа LINK-wf нельзя добавлять объекты');
            return;
        } // if

        itemGrid.addRow(itemNewId, '', itemGrid.getRowsNum());
        --itemNewId;
        // func. itemAddBtnClick
    }

    /**
     * Обработчик на перенос компонента в таблице
     */
    function itemOnDrop() {
        isItemDradDrop = true;
        // func. itemOnDrop
    }

    /**
     * Обработчик. Перед переносом компонента по таблице
     * @param rId
     * @return {Boolean}
     */
    function itemOnBeforeDrag(rId) {
        // Запрещаем при редактировании через actoin,
        // двигать родительские компоненты
        var rowAcId = itemGrid.getUserData(rId, 'acId');
        return wareframeData.acId == rowAcId || rowAcId == '';
        // func. itemOnBeforeDrag
    }

    /**
     * Обработчик. На двойной клик по компоненту в таблице
     * @param rId
     * @return {Boolean}
     */
    function itemOnRowDblClicked(rId) {
        // Запрещаем при редактировании через actoin,
        // редактировать родительские компоненты
        var rowAcId = itemGrid.getUserData(rId, 'acId');
        return wareframeData.acId == rowAcId || rowAcId == '';
        // func. itemOnRowDblClicked
    }

    function getWFId() {
        return wareframeData.wfId ? wareframeData.wfId : wfTree.getSelectedItemId();
    }

    /**
     * Инициализация таблицы
     */
    function initGridCreate(){
        itemGrid = new dhtmlXGridObject(options.gridBox);
        itemGrid.newId = -1;
        itemGrid.setImagePath("res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/imgs/");
        itemGrid.setHeader(",,Описание,Сист. назв,Компонент");
        itemGrid.setInitWidths("32,32,*,125,125");
        itemGrid.setColsName([0, '', 'name', 'sysname', 'compId']);
        itemGrid.setColTypes("ch,img,ed,ed,stree");
        itemGrid.setSkin("dhx_skyblue");
        itemGrid.enableDragAndDrop(true);
        itemGrid.setSubTree(dhtmlxInit.tree['comp'], 4);
        itemGrid.init();

        itemGrid.attachEvent("onDrop", itemOnDrop);
        itemGrid.attachEvent("onBeforeDrag", itemOnBeforeDrag);
        itemGrid.attachEvent("onRowDblClicked", itemOnRowDblClicked);
        // func. initGridCreate
    }

    /**
     * Callback для blockSaveBtnClick.
     * Статус сохранения блоков
     */
    function cbBlockSaveStatus(pData) {
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        }

        var list = pData['new'];
        var file = fileBlockBuffer;
        var blockTree = wareframeMvc.getTree().block;

        // Выставляем новый ID для папок
        for (var fileId in list) {
            // Получаем по старому ID, новый
            var newId = list[fileId];
            if ( fileId == "root" ){
                var subItems = blockTree.getAllSubItems(0);
            }else{
                var parentid = blockTree.getParentId(fileId);
                blockTree.changeItemId(fileId, newId);
                var subItems = blockTree.getAllSubItems(newId);
            }

            // Беагем по детям
            var childList = subItems.toString().split(',');
            for (var num in childList) {
                var childId = childList[num];
                var childIdNum = parseInt(childId);
                if (isNaN(childIdNum) || childIdNum >= 0) {
                    continue;
                }
                var block = blockTree.getUserData(childId, 'blockName');
                blockTree.setUserData(childId, 'fileId', newId);
                blockTree.changeItemId(childId, block + ':' + newId);
            } // for
        }

        fileBlockBuffer = {};
        blockRmBuff = [];
        linkBlockBuff = {};

        alert('Данные сохранены');

        // func. cbBlockSaveStatus
    }

    /**
     * callBack. Загрузка дерева с блоками.
     */
    function cbLoadBlockTreeData(pData) {
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        }

        if ( pData['treeBox'] == 'linkBlockTree'){
            createLinkBlockTree();
            var blockTree = getTree().blockLink;
        }else{
            var blockTree = getTree().block;
            // Записываем выбранный ID ветки страниц
            wareframeData.wfId = pData['wfid'];
        }
		
        blockTree.deleteChildItems(0);
        if (!pData['tree']) {
            //TODO: Вывести о том что блоков нет, выбирете файл из шаблонов
            return;
        } // if
        // Загружаем выбранные данные
        blockTree.loadJSONObject(pData['tree']);
        if ( linkBlockBrunchSelect ){
            blockTree.selectItem(linkBlockBrunchSelect);
        }
        fileBlockBuffer = {};

        // Передалать вывод сообщения на экран
        for (var i in pData['err']) {
            alert(pData['err'][i]);
        } // if

        // func. cbLoadBlockTreeData
    }


    /**
     * Callback. Загрузка блоков, которые находятся в шаблоне
     */
    function cbLoadTplBlockData(pData) {
        var blockTree = wareframeMvc.getTree().block;

        // Если в FS ни чего не выбранно,  pData['id'] = root
        // blockItemId - это выделенная папка в FS, куда мы добавляет папки
        var blockItemId = pData['id'];

        // Получаем тип данных
        var blockType = blockTree.getUserData(blockItemId, 'type');

        // TODO: заменить на константу
        // 2 - шаблон
        if (blockType == 2) {
            if (!confirm('Заменить ветку?')) {
                return;
            }
            // Если это новая ветка
            if ( parseInt(pData['id']) >= 0 ){
                blockRmBuff.push(blockItemId);
            }
            for (var i in fileBlockBuffer) {
                if (fileBlockBuffer[i].fileId == pData['fileId']) {
                    delete fileBlockBuffer[i];
                } // if
            } // for
        } // if

        // Удаляем всех дитей выделенной ветки в ФС
        blockTree.deleteChildItems(blockItemId == 'root' ? 0 : blockItemId);

        // Если это не рутовая папка, то ставим тип что папка занята
        if (blockItemId != 'root') {
            blockTree.setUserData(blockItemId, 'type', FOLDER_TPL);
            blockTree.setItemImage(blockItemId, 'folderClosed.gif');
        }

        // Если это рут, папка, значит там всё перезатерается
        if (blockItemId == 'root') {
            // Ощищаем список папок
            fileBlockBuffer = {};
            // Будем идти с новых ID
            blockNewId = -1;
            // Помечаем что нужно удалить всё
            blockRmBuff = 'all';
        } // if

        // Получаем ID для блока
        var itemId = blockItemId == 'root' ? "root" : blockNewId--;
        fileBlockBuffer[itemId] = {file:pData['file'], block:'', fileId:''};
        if (blockItemId != 'root') {
            fileBlockBuffer[itemId].block = blockTree.getUserData(blockItemId, 'blockName');
            fileBlockBuffer[itemId].fileId = pData['fileId'];//blockItemId;//brunchId == 0 ? -1 : brunchId;
        }

        // Пустой шаблон
        var emptyBlock = pData['list'].length == 0;
        if (emptyBlock) {
            pData['list'][0] = pData['file'];
        }

        var parentBrunchId = blockItemId == 'root' ? 0 : blockItemId;
        for (blName in pData['list']) {
            var newId = blockNewId--;
            var name = pData['list'][blName];
            name = name ? name : blName;
            blockTree.insertNewItem(parentBrunchId, newId, name, null, "folderEmpty.gif", 0, 0, 'CHILD');
			// Установливаем что в блоке больше компонентов нет и нет блоков
            blockTree.setUserData(newId, 'type', FOLDER_FREE);
            blockTree.setUserData(newId, 'fileId', itemId);
            blockTree.setUserData(newId, 'blockName', blName);
        } // for

        blockTree.changeItemId(blockItemId, itemId);
        blockTree.setUserData(itemId, 'fileId', pData['fileId']);

        if (emptyBlock) {
			// Ветка без блоков
            blockTree.setUserData(newId, 'type', FOLDER_EMPTY);
            blockTree.setUserData(newId, 'blockName', 'empty');
            blockTree.setItemImage(newId, 'folderClosed.gif');
            blockTree.setUserData(newId, 'fileId', itemId);
        }//

        // func. cbLoadTplBlockData
    }

    /**
     * Можно ли удалить ветку из дерева блоков
     * @param pBlockTree
     * @param pItemId
     * @return {Boolean}
     */
    function isBlockRm(pBlockTree, pItemId) {
        var childCount = pBlockTree.hasChildren(pItemId);
        for (var index = 0; index < childCount; index++) {
            var childItem = pBlockTree.getChildItemIdByIndex(pItemId, index);
            if (pBlockTree.getUserData(childItem, 'type') != FOLDER_FREE) {
                alert('Папка не пуста');
                return false;
            } // if
        } // for
        return true;
        // func. isBlockRm
    }


    /**
     * Callback на blockItemBtnClick. Статус сохранения
     * pData - представляем собой объект вид:
     * {'oldId_1':'newId_1', 'oldId_2':'newId_2'}
     */
    function cbBlockItemSaveStatus(pData) {
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        }

        isItemDradDrop = false;

        // Обновляем ID и добавляем картинки
        var listId = pData['listid'];
        for (var oldId in listId) {
            var newId = listId[oldId];
            var img = getItemGridImgSettings(newId)
            itemGrid.cells(oldId, 1).setValue(img);
            itemGrid.changeRowId(oldId, newId);
        }
        // Изменяем в дереве блоков иконку у ветки(блока)
        var blId = pData['blid'];
        var blockTree = wareframeMvc.getTree().block;
        var blockType = blockTree.getUserData(blId, 'type');
        if ( blockType != FOLDER_LINK){
            blockTree.setItemImage(blId, 'comp.gif');
            blockTree.setUserData(blId, 'type', FOLDER_COMP);
        }
        itemGrid.clearChangedState();
        alert('Данные сохранены');
        // func. cbBlockItemSaveStatus
    }


    /**
     * Callback. Загрузка данных в таблицу компонентов
     * @param pData
     */
    function cbBlockItemLoadData(pData) {
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        } // if

        itemGrid.parse(pData, 'xml');

        var blockId = blockTree.getSelectedItemId();
        var blockType = blockTree.getUserData(blockId, 'type');
        var linkData = blockTree.getUserData(blockId, 'link');

        for (var i = 0; i < itemGrid.rowsCol.length; i++) {
            var id = itemGrid.getRowId(i);
            var acId = itemGrid.getUserData(id, 'acId');
            var islock = 0;
            // Разрешено ли редактировать компонента в таблице (в зависимости от линка)
            var isLinkDisable = blockType == FOLDER_LINK && ( wareframeData.acId == '0' || linkData.acId != '0');
            if (acId != wareframeData.acId || isLinkDisable ) {
                itemGrid.cells(id, 2).setTextColor('#00008B');
                itemGrid.cells(id, 0).setDisabled(true);
                islock = 1;
            } // if

            var img = getItemGridImgSettings(id, islock)
            itemGrid.cells(id, 1).setValue(img);
        } // for
        // func. cbBlockItemLoadData
    }

    /**
     * Формирование картинки для таблицы. Изображение редактирования
     * @param pId
     * @return {String}
     */
    function getItemGridImgSettings(pId, pIsLock) {
        return wareframeData.imgThemeUrl + 'edit_16.png^Настройки^javascript:wareframeMvc.goToItemSettings(' + pId + ', '+pIsLock+')^_self';
        // func. getItemGridImgSettings
    }

    /**
     * Переход на настройку компонента
     * @param pId
     */
    function goToItemSettings(pId, pIsLock) {
        utils.go(utils.url({contr:'blockItem', query:'id=' + pId + '&acid=' + wareframeData.acId +'&islock='+pIsLock}));
        // func. goToItemSettings
    }

    /**
     * Callback. Стутс удаления блока из дерева блоков
     * @param pData
     */
    function cbBlockRmBtnClickStatus(pData) {
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        }
        for (var num in pData['list']) {
            itemGrid.deleteRow(pData['list'][num]);
        }
        if (itemGrid.getRowsNum() == 0) {
            var blId = pData['blid'];
            var blockTree = wareframeMvc.getTree().block;
            blockTree.setItemImage(blId, 'folderEmpty.gif');
            blockTree.setUserData(blId, 'type', FOLDER_FREE);
        }
        alert('Данные удалены');
        // func. cbBlockRmBtnClickStatus
    }

    /**
     * Клик по ветке в дереве блоков
     * @param pBlockBrunchId
     * @param pBlockTree
     */
    function blockBrunchClick(pBlockBrunchId, pBlockTree) {
        // Очищаем таблицу
        itemGrid.clearAll();

        var file = pBlockTree.getUserData(pBlockBrunchId, 'file');
        fsTree.selectItem(file);
        var type = pBlockTree.getUserData(pBlockBrunchId, 'type');

        // Если папка без компонентов и это не линк, то грузить ни чего не надо
        if (type != FOLDER_COMP && type != FOLDER_LINK ) {
            return;
        } // if

        var wfId = getWFId();
        var blockBrunchId = pBlockBrunchId;

        var acId = wareframeData.acId;
        HAjax.loadBlockItem({dataType:'xml', query:{blid:blockBrunchId, acid:acId, wfid:wfId }});
        // func. blockBrunchClick
    }

    /**
     * Новый ли блок
     * @return {Boolean}
     */
    function isBlockNew(pBlockId){
        return !isNaN(parseInt(pBlockId));
        // func. isBlockNew
    }

    /**
     * Клик по ветке в дереве wareframe
     * @param pWfId
     * @param pWfTree
     */
    function wfBrunchClick(pWfId, pWfTree) {
        // Если не файл, то выходим
        if (pWfTree.getUserData(pWfId, 'type') != FOLDER_COMP) {
            return;
        }
        itemGrid.clearAll();
        blockRmBuff = [];
        // Делаем запрос на дерево блоков
        HAjax.loadBlockTree({query:{wfid:pWfId, treeBox:'mainBlockTree'}});
        // func. wfBrunchClick
    }

    /**
     * Клик по ветке в дереве шаблонов
     * @param pFSTreeItemId
     * @param pFsTree
     */
    function fcBrunchClick(pFSTreeItemId, pFsTree) {
        // Если не файл выходим
        if (pFsTree.getUserData(pFSTreeItemId, 'type') != FOLDER_COMP) {
            alert('Выберите файл');
            return;
        } // if

        // Если ни какой блок не выбран, заставляем выбрать
        var blockId = blockTree.getSelectedItemId();
        var fileId = blockTree.getUserData(blockId, 'fileId');
        var blockType = blockTree.getUserData(blockId, 'type');
        if (blockType != undefined && blockType != FOLDER_FREE && blockType != FOLDER_TPL) {
            alert('Выберите пустой блок');
            return;
        } // if

        var wfId = getWFId();
        if (!wfId) {
            alert('Выберите страницу');
            return;
        } // if

        blockId = blockId == '' ? 'root' : blockId;

        fileId = fileId == '' ? blockId.split(':')[1] : fileId;
        fileId = fileId == undefined ? blockId : fileId;

        HAjax.tplToBlock({query:{file:pFSTreeItemId, id:blockId, fileId:fileId}});
        // func. fcBrunchClick
    }

    /**
     * Инициализация деревьев
     */
    function initTreeCreate(){
        dhtmlxInit.init({
            'wareframe': {
                tree:{ json: wareframeData.wfTreeJson, id: options.wfTreeBox },
                dirAdd:{ url:{method:'dirAdd'}, id:'#dirAdd' },
                rmObj:{ url:{method:'rmObj'}, id:'#rmObj' },
                fileAdd:{ url:{method:'fileAdd'}, id:'#fileAdd' },
                renameObj:{ url:{method:'renameObj'}, id:'#rename' },
                clickEnd: wfBrunchClick
            },
            'block': {
                tree:{ id: options.blockTreeBox, json: wareframeData.blockTreeJson },
                clickEnd: blockBrunchClick
            },
            'fs': {
                tree:{ json: wareframeData.fsTreeJson, id:'fsTree' },
                dbClick: fcBrunchClick
            },
            'comp': {
                tree:{ json: wareframeData.compTreeJson, id:'compTree' }
            }
        });

        wfTree = dhtmlxInit.tree['wareframe'];
        blockTree = dhtmlxInit.tree['block'];
        fsTree = dhtmlxInit.tree['fs'];
        // func. initTreeCreate
    }

    /**
     * Сохранение компонентов в таблице компонентов по шаблону
     * @return {Boolean}
     */
    function itemSaveBtnClick() {
        var blId = blockTree.getSelectedItemId();
        var type = blockTree.getUserData(blId, 'type');
        var linkData = blockTree.getUserData(blId, 'link');
        if ( type == FOLDER_LINK && ( wareframeData.acId == '0' || linkData.acId != '0') ){
            alert('В ветке типа LINK-wf нельзя производить сохранение');
            return false;
        } // if
        var acId = wareframeData.acId;

        var wfId = getWFId();

        // Если было перетаскивание компонентов, нужно сохранить порядок
        var gridItemIdList = '';
        if (isItemDradDrop) {
            gridItemIdList = itemGrid.getAllItemIds();
        }

        var json = itemGrid.serializeToJSON();
        if (json.total_count == 0 && !isItemDradDrop) {
            return false;
        }
        var query = {acid:acId, blid:blId, wfid:wfId};
        var data = 'data=' + $.toJSON(json.rows) + '&position=' + gridItemIdList;
        HAjax.saveBlockItem({data:data, methodType:'POST', query:query});
        // func. itemSaveBtnClick
    }

    function getTree(){
        return {
            // Главное дерево блоков
            block: blockTree,
            // Дерево с шаблонами
            fs: fsTree,
            // Главное дерево с wareframe
            wf: wfTree,
            // Древо блоков для линкования
            blockLink: blockLinkTree
        } // return
        // func. getTree
    }

    function linkWfTreeBrunchClick(pLinkWfBrunchId, pLinkWfTree){
        HAjax.loadBlockTree({query:{wfid: pLinkWfBrunchId, treeBox:'linkBlockTree'}});
        // func. linkWfTreeBrunchClick
    }

    function linkAcTreeBrunchClick(pLinkAcBrunchId, pLinkAcTree){
        HAjax.loadBlockTree({query:{acid: pLinkAcBrunchId, treeBox:'linkBlockTree'}});
        // func. linkAcTreeBrunchClick
    }

    function linkBlockTreeBrunchDbClick(pBlockLinkBrunchId, pLinkBlockTree){
        // Получаем тип ветки
        var type = pLinkBlockTree.getUserData(pBlockLinkBrunchId, 'type');
        // Если это ветка без компонент, то и линковать нельзя
        if ( type != FOLDER_COMP ){
            alert('Можно выбрать только ветку с компонентами');
            return false;
        } // if
        // Получаем ID ветки основного дерева блоков
        var blockBrunchId = blockTree.getSelectedItemId();
        // Меняем картинка ветки на линковую ветку
        var filename = wareframeData.acId != '0' ? 'linka.gif' : 'linkw.gif';
        blockTree.setItemImage(blockBrunchId, filename);
        // Устанавливаем тип ветки на линк
        blockTree.setUserData(blockBrunchId, 'type', FOLDER_LINK);
        // Получаем wareframe ID
        var linkMainBrunchId = mainLinkTree.getSelectedItemId();
        // Составляем данные для сохранения
        var saveData = {linkMainId: linkMainBrunchId, linkBlockId: pBlockLinkBrunchId};
        // Записываем данные в ветку, вдруг еще раз захотим посмотреть что мы там выбирали
        blockTree.setUserData(blockBrunchId, 'link', saveData);
        // Устанавливаем тип данных - ветка добавляется (del - на удаление)
        saveData['type'] = 'add';
        // Кладём данные в буффер (далее этот буффер отошлёться на сервер)
        linkBlockBuff[blockBrunchId] = saveData;
        // Закрываем окно fancybox
        $.fancybox.close();
        // func. linkBlockTreeBrunchDbClick
    }

    function linkItemFancyBeforeLoadWf(){
        var blockBrunchId = blockTree.getSelectedItemId();
        var type = blockTree.getUserData(blockBrunchId, 'type');
        if ( type != FOLDER_LINK && type != FOLDER_FREE){
            alert('Выбрана не ссылка или не пустая папка');
            return false;
        } // if

        if ( !mainLinkTree ){
            dhtmlxInit.init({
                'mainLinkTree': {
                    tree:{ id: options.linkWfTreeBox, json: wareframeData.wfTreeJson },
                    clickEnd: linkWfTreeBrunchClick
                }
            });
            mainLinkTree = dhtmlxInit.tree['mainLinkTree'];
        } // if ( !mainLinkTree )

        // Получаем ранее сохранённые данные ( если они есть )
        var linkData = blockTree.getUserData(blockBrunchId, 'link');
        linkSelectBrunch(linkData);
        // func.linkItemFancyBeforeLoad
    }

    function linkItemFancyBeforeLoadAc(){
        var blockBrunchId = blockTree.getSelectedItemId();
        var type = blockTree.getUserData(blockBrunchId, 'type');
        if ( type != FOLDER_LINK && type != FOLDER_FREE ){
            alert('Выбрана не ссылка или не пустая папка');
            return false;
        } // if

        // Получаем ранее сохранённые данные ( если они есть )
        var linkData = blockTree.getUserData(blockBrunchId, 'link');
        if ( linkData && linkData.acId == 0){
            alert('Просмот разрешён только в окне редактирования wareframe');
            return false;
        }

        if ( !mainLinkTree ){
            dhtmlxInit.init({
                'mainLinkTree': {
                    tree:{ id: options.linkWfTreeBox, json: wareframeData.actTree },
                    clickEnd: linkAcTreeBrunchClick
                }
            });
            mainLinkTree = dhtmlxInit.tree['mainLinkTree'];
        } // if ( !mainLinkTree )

        linkSelectBrunch(linkData);
        // func.linkItemFancyBeforeLoad
    }

    // Вспомог. фукнции выделение ветки в деревьях линк
    // Используется в функц. linkItemFancyBeforeLoadAc и linkItemFancyBeforeLoadWf
    function linkSelectBrunch(linkData){
        // Если данные есть, то это уже сформированная ссылка, иначе это пустая папка
        if ( linkData ){
            // выделяем ветку в linkWfTree
            mainLinkTree.selectItem(linkData.linkMainId, true);
            // Указываем какую ветку выделить, когда загрузиться дерево linkBlockTree
            linkBlockBrunchSelect = linkData.linkBlockId;
        }else{
            // Убирем выделение
            mainLinkTree.clearSelection();
            // Очищаем дерево pLinkBlockTree
            if ( blockLinkTree ){
                blockLinkTree.deleteChildItems(0);
            }
        } // if
        // func. linkSelectBrunch
    }

    function createLinkBlockTree(){
        if ( !blockLinkTree ){
            dhtmlxInit.init({
                'linkBlockTree': {
                    tree:{ id: options.linkBlockTreeBox},
                    dbClick: linkBlockTreeBrunchDbClick
                }

            });
            blockLinkTree = dhtmlxInit.tree['linkBlockTree'];
        } // if
        // func. createLinkBlockTree
    }

    function linkRmBtnClick(){
        if (!confirm('Убрать связь?')) {
            return;
        }
        var blockBrunchId = blockTree.getSelectedItemId();
        var type = blockTree.getUserData(blockBrunchId, 'type')
        if ( type != FOLDER_LINK ){
            alert('Выбрана не ссылка');
            return;
        }
        blockTree.setItemImage(blockBrunchId, 'folderEmpty.gif');
        linkBlockBuff[blockBrunchId] = {type: 'del'};
        itemGrid.clearAll();

        // func. linkRmBtnClick
    }

    function init(pOptions){
        // Сохняем настройки
        options = pOptions;

        // Вешаем обработчик на кнопку сохранения блоков
        $(options.blockSaveBtn).click(blockSaveBtnClick);
        // Вешаем обработчик на кнопку удаления блоков из дерева блоков
        $(options.blockRmBtn).click(blockRmBtnClick);
        // Вешаем обработчик на кнопку удаления компонента из таблицы компонентов для блока
        $(options.itemRmBtn).click(itemRmBtnClick);
        // Вешаем обработчик на кнопку добавление компонента в таблицу компонентов для блока
        $(options.itemAddBtn).click(itemAddBtnClick);
        // Вешаем обработчик на кнопку сохранения компонентов в таблице компонентов по блоку
        $(options.itemSaveBtn).click(itemSaveBtnClick);
        // Вешаем обработчик на кнопку удаления связи линка на блоке
        $(options.linkRmBtn).click(linkRmBtnClick);

        // Создаём деревья
        initTreeCreate();
        // Создаём таблицу
        initGridCreate();

        // Устанавливаем Ajax вызовы и callBack для них
        HAjax.create({
            // Загрузка дерева блоков
            loadBlockTree: cbLoadBlockTreeData,
            // Загрузка блоков по шаблону
            tplToBlock: cbLoadTplBlockData,
            // Статус сохранения блоков
            saveBlock: cbBlockSaveStatus,
            // Статус сохранения компонентов по блоку
            saveBlockItem: cbBlockItemSaveStatus,
            // Загрузка компонентов в таблицу по блоку
            loadBlockItem: cbBlockItemLoadData,
            // Статус на удаление блоков
            rmBlockItem: cbBlockRmBtnClickStatus
        }); // HAjax.create

        // Если wfId задан, то мы находимся в редактировании action
        // и уже знаем wfId ( он выберается на экране до этого )
        if (wareframeData.wfId) {
            $('.tdWF').hide();
            $(options.linkItemBtn).fancybox({
                beforeLoad: linkItemFancyBeforeLoadAc
            });
            $(options.pageCaption).html(wareframeData.pageCaption);
        }else{
            $(options.linkItemBtn).fancybox({
                beforeLoad: linkItemFancyBeforeLoadWf
            });
        } // if
        // func. init
    }

    return {
        init: init,
        goToItemSettings: goToItemSettings,
        createLinkBlockTree: createLinkBlockTree,
        getTree: getTree
    } // return
})();

var wareframeData = {
    // Данные для построения дерева блоков
    blockTreeJson: <?= self::get('blockTree')?:'null' ?>,
    // Данные для построение дерева wareframe
    wfTreeJson: <?= self::get('wfTree')?:'null' ?>,
    // Данные для построение дерева с шаблонами сайта
    fsTreeJson: <?= self::get('filesysTree') ?>,
    // Данные для построения дерева с доступными компонентами в админке
    compTreeJson: <?= self::get('compTree') ?>,
    // ID экшена
    acId: '<?= self::get('acId') ?>',
    wfId: '<?= self::get('wfId') ?>',
    // URL адрес картинок
    imgThemeUrl: '<?= self::res('images/') ?>',
    actTree: <?= self::get('actTree', 'null') ?>,
    pageCaption: '<?= self::get('pageCaption') ?>'
} // var wareframeData

$(document).ready(function(){
    wareframeMvc.init({
        // Кнопка для сохранения данных по блоками в дереве блоков
        blockSaveBtn: '#blockSave',
        // Кнопка для удаление блока из дерева блоков
        blockRmBtn: '#blockRm',
        // Кнопка удалить блоки из таблицы с компонентами
        itemRmBtn: '#itemRm',
        // Кнопка добавить компонент в блок
        itemAddBtn: '#itemAdd',
        // Контенер для таблицы с компонентами для блоков
        gridBox: 'blockItemGrid',
        // Контенер для дерева с блоками
        blockTreeBox: 'blockTree',
        // Контенер для дерева с wareframe
        wfTreeBox: 'wfTree',
        // Кнопка сохранения компонентов в таблице компонентов по блоку
        itemSaveBtn: '#blockItemSave',
        // Кнопка сохдания ссылки для блока
        linkItemBtn: '#linkBtn',
        // Контенер для дерева с wareframe для ссылок
        linkWfTreeBox: 'linkWfTreeBox',
        // Контенер для дерева с wareframe для ссылок
        linkBlockTreeBox: 'linkBlockTreeBox',
        // Контенер со всеми кнопками
        //gridBtnBox: '#gridBtnBox',
        // Кнопка устранения связи линка с блока
        linkRmBtn: '#linkRmBtn',
        pageCaption: '#history'
    }); // wareframeMvc.init()
}); // $(document).ready

</script>