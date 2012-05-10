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
                    <a href="?$t=manager&$c=action" title="Comp List">
                        <img src="<?= self::res('images/refresh_32.png') ?>" alt="Comp List"/><span>Comp List</span>
                    </a>
                </li>

            </ul>
        </div>


        <div class="content" id="mainpanel">
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
            <img id="itemAdd" class="button" src="<?= self::res('images/plus_32.png') ?>" alt="Добавить"/>
            <img id="itemRm" class="button" src="<?= self::res('images/del_24.png') ?>" alt="Удалить"/>
            <img id="blockItemSave" class="button" src="<?= self::res('images/save_24.png') ?>" alt="Сохранить"/>

            <div id="blockItemGrid" style="width:700px;height:250px;"></div>
        </div>
    </div>
</div>

<div id="compTree" style="width:150px;height:150px;"></div>

<script type="text/javascript">
var imgTheme = '<?= self::res('images/') ?>';

var contrName = 'wareframe';
var callType = 'manager';
utils.setType(callType);
utils.setContr(contrName);
HAjax.setContr(contrName);
HAjax.setType(callType);

var wareframe = {
    acId:'',
    wfId:'',
    blId:'',
    tree:{},
    block:{
        file:{},
        del:[],
        newId:-1
        // Номер пустого блока, увеличивается с добавлением пустых блокв, emptyItemNum:1

    },
    fs:{},
    wf:{},
    utils:{},
    item:{
        list:{}
        // Было ли перетаскивание компонента в таблице, dragDrop:false
    },
    //$:{},
    FOLDER_FREE:0,
    FOLDER_COMP:1,
    FOLDER_TPL:2,
    FOLDER_EMPTY:3
};

wareframe.item.loadBlockItemSuccess = function (pData) {
    if (pData['error']) {
        alert(pData['error']['msg']);
        return;
    }
    var grid = wareframe.grid;

    grid.clearAll();
    grid.parse(pData, 'xml');

    for (var i = 0; i < grid.rowsCol.length; i++) {
        var id = grid.getRowId(i);
        var acId = grid.getUserData(id, 'acId');
        var img = wareframe.utils.getImgSettings(id)
        grid.cells(id, 1).setValue(img);
        if (acId != wareframe.acId) {
            grid.cells(id, 2).setTextColor('#00008B');
            grid.cells(id, 0).setDisabled(true);
        } // if
    } // for
    // func. wareframe.item.loadBlockItemSuccess
}

/**
 * OnClick по блоку
 */
wareframe.block.click = function (pBlockId, pBlockTree) {
    var file = pBlockTree.getUserData(pBlockId, 'file');
    wareframe.tree.fs.selectItem(file);
    var type = pBlockTree.getUserData(pBlockId, 'type');
    if (type != wareframe.FOLDER_FREE && type != wareframe.FOLDER_COMP) {
        return;
    }

    // Проверяем, ново добавленный ли это блок, если да, то делать запрос не надо
    var isNew = isNaN(parseInt(pBlockId));
    if (!isNew) {
        return;
    }
    var wfId = wareframe.wfId ? wareframe.wfId : wareframe.tree.wf.getSelectedItemId();
    var acId = wareframe.acId;
    HAjax.loadBlockItem({dataType:'xml', query:{blid:pBlockId, acid:acId, wfid:wfId}});
    //wareframe.grid.loadJSONEx('blockItem', {blid: pBlockId, acid:acId, wfid:wfId}, wareframe.item.loadblockItem);
    // func. wareframe.block.click
}

/**
 * OnClick по файлу/папке в дереве WF
 */
wareframe.wf.click = function (pWfId, pWfTree) {
    // Если не файл, то выходим
    if (pWfTree.getUserData(pWfId, 'type') != wareframe.FOLDER_COMP) {
        return;
    }
    wareframe.grid.clearAll();
    wareframe.block.del = [];
    // Делаем запрос на дерево блоков
    HAjax.loadBlockTree({query:{wfid:pWfId}});
}

wareframe.wf.loadBlockTree = function (pData) {
    if (pData['error']) {
        alert(pData['error']['msg']);
        return;
    }
    // Записываем выбранный ID ветки страниц
    wareframe.wfid = pData['wfid'];

    var blockTree = wareframe.tree.block;

    blockTree.deleteChildItems(0);
    if (!pData['tree']) {
        //TODO: Вывести о том что блоков нет, выбирете файл из шаблонов
        return;
    }
    // Загружаем выбранные данные
    blockTree.loadJSONObject(pData['tree']);
    wareframe.block.file = {};

    // Передалать вывод сообщения на экран
    for (var i in pData['err']) {
        alert(pData['err'][i]);
    }

    //ini.get('blockItemBody').html('');
}

// dbClick по шаблону в дереве шаблонов fsTree
wareframe.fs.dbClick = function (pFSTreeItemId, pFsTree) {
    // Если не файл выходим
    if (pFsTree.getUserData(pFSTreeItemId, 'type') != wareframe.FOLDER_COMP) {
        alert('Выберите файл');
        return;
    }
    // Если ни какой блок не выбран, заставляем выбрать
    var blockTree = wareframe.tree.block;
    var blockId = blockTree.getSelectedItemId();
    var fileId = blockTree.getUserData(blockId, 'fileId');
    var blockType = blockTree.getUserData(blockId, 'type');
    if (blockType != undefined && blockType != wareframe.FOLDER_FREE && blockType != wareframe.FOLDER_TPL) {
        alert('Выберите пустой блок');
        return;
    }

    var wfId = wareframe.getWFId();
    if (!wfId) {
        alert('Выберите страницу');
        return;
    } // if

    blockId = blockId == '' ? 'root' : blockId;

    fileId = fileId == '' ? blockId.split(':')[1] : fileId;
    fileId = fileId == undefined ? blockId : fileId;

    HAjax.tplToBlock({query:{file:pFSTreeItemId, id:blockId, fileId:fileId}});
    // func. wareframe.fs.dbClick
}

wareframe.item.rmClick = function () {
    // Запрашиваем удаление папки
    if (!confirm('Удалить элементы?')) {
        return;
    }
    var blId = wareframe.tree.block.getSelectedItemId();
    var rowsId = wareframe.grid.getCheckedRows(0);
    HAjax.rmBlockItem({data:{idlist:rowsId, blid:blId, acid: wareframe.acId }, methodType:'POST'});
    // func. wareframe.item.rmClick
}

wareframe.item.rmBlockItemSuccess = function (pData) {
    if (pData['error']) {
        alert(pData['error']['msg']);
        return;
    }
    for (var num in pData['list']) {
        wareframe.grid.deleteRow(pData['list'][num]);
    }
    if (wareframe.grid.getRowsNum() == 0) {
        var blId = pData['blid'];
        wareframe.tree.block.setItemImage(blId, 'folderEmpty.gif');
        wareframe.tree.block.setUserData(blId, 'type', wareframe.FOLDER_FREE);
    }
    alert('Данные удалены');
    // func. wareframe.item.rmBlockItemSuccess
}

wareframe.block.rmClick = function () {
    var treeBlock = wareframe.tree.block;
    var blockItemId = treeBlock.getSelectedItemId();
    blockItemId = blockItemId ? blockItemId : 0;

    var type = treeBlock.getUserData(blockItemId, 'type');
    var itemAcId = treeBlock.getUserData(blockItemId, 'acId');

    itemAcId = itemAcId ? itemAcId : '';
    if (itemAcId != wareframe.acId) {
        alert('Нельзя удалить родительский объект');
        return;
    } // if

    if (type == wareframe.FOLDER_COMP) {
        alert('В блоке находятся компоненты');
        return;
    } // if

    // Проверка пустая ли папка
    if (type == wareframe.FOLDER_FREE) {
        return;
    } // if

    // Запрашиваем удаление папки
    if (!confirm('Очистить ветку?')) {
        return;
    } // if

    // Не пустой ли блок это
    /// Пустая ли папка, т.е если ли другие приклеплённые объкты внутри
    if (type != wareframe.FOLDER_TPL && type != wareframe.FOLDER_EMPTY ) {
        alert('В Блока находятся другие блоки');
        return;
    }

    var file = wareframe.block.file;

    // Если это пустой шаблон
    var childCount = treeBlock.hasChildren(blockItemId);
    if (childCount == 0) {
        var parentId = treeBlock.getParentId(blockItemId);
        treeBlock.deleteItem(blockItemId, true);
        treeBlock.setItemImage(parentId, 'folderEmpty.gif');
        treeBlock.setUserData(parentId, 'type', 0);
        if (!isNaN(parseInt(parentId))) {
            var delId = parentId == 0 ? blockItemId.split(':')[1] : parentId;
            wareframe.block.del.push(delId);
        }
        var fileId = treeBlock.getUserData(parentId, 'fileId');
        fileId = fileId == '' ? parentId.split(':')[1] : fileId;
        for (var i in file) {
            if (file[i].fileId == fileId) {
                delete file[i];
            } // if
        } // for

        return;
    } // if Очистка пустого шаблона

    // Можно ли папку удалить
    if (!wareframe.block.isDel(treeBlock, blockItemId)) {
        return;
    }

    // Добавляет в буффер, только те которые созданы и сохранены
    var delId = parseInt(blockItemId);
    if (!isNaN(delId) && delId >= 0){
       wareframe.block.del.push(blockItemId);
    }

    var fileId = treeBlock.getUserData(blockItemId, 'fileId');
    fileId = fileId == '' ? blockItemId.split(':')[1] : fileId;

    for (var i in file) {
        if (file[i].fileId == fileId) {
            delete file[i];
        } // if
    } // for

    // Делаем пустой папкой
    treeBlock.setUserData(blockItemId, 'type', wareframe.FOLDER_FREE);
    // Удаляем дочернии ветки
    treeBlock.deleteChildItems(blockItemId);
    // Выставляем картинки - пустая папка
    treeBlock.setItemImage(blockItemId, 'folderEmpty.gif');

    // func. wareframe.block.rmClick
}

wareframe.block.isDel = function (pBlockTree, pItemId) {
    var childCount = pBlockTree.hasChildren(pItemId);
    for (var index = 0; index < childCount; index++) {
        var childItem = pBlockTree.getChildItemIdByIndex(pItemId, index);
        if (pBlockTree.getUserData(childItem, 'type') != 0) {
            alert('Папка не пуста');
            return false;
        }
    }
    return true;
    // func. wareframe.block.isDel
}

//var fileNum = -1;

/**
 * Ajax
 */
wareframe.fs.tplToBlockSuccess = function (pData) {
    var blockTree = wareframe.tree.block;

    // Если в FS ни чего не выбранно,  pData['id'] = root
    // blockItemId - это выделенная папка в FS, куда мы добавляет папки
    var blockItemId = pData['id'];

    // Разрешено ли писать в эту папку, файл
    if (!wareframe.block.isDel(blockTree, blockItemId)) {
        return;
    }

    var file = wareframe.block.file;

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
            wareframe.block.del.push(blockItemId);
        }
        for (var i in file) {
            if (file[i].fileId == pData['fileId']) {
                delete file[i];
            } // if
        } // for
    } // if

    // Удаляем всех дитей выделенной ветки в ФС
    blockTree.deleteChildItems(blockItemId == 'root' ? 0 : blockItemId);

    // Если это не рутовая папка, то ставим тип что папка занята
    if (blockItemId != 'root') {
        blockTree.setUserData(blockItemId, 'type', 2);
        blockTree.setItemImage(blockItemId, 'folderClosed.gif');
    }

    // Если это рут, папка, значит там всё перезатерается
    if (blockItemId == 'root') {
        // Ощищаем список папок
        file = wareframe.block.file = {};
        // Будем идти с новых ID
        wareframe.block.newId = -1;
        // Помечаем что нужно удалить всё
        wareframe.block.del = 'all';
    } // if

    // Получаем ID для блока
    var itemId = blockItemId == 'root' ? "root" : wareframe.block.newId--;
    file[itemId] = {file:pData['file'], block:'', fileId:''};
    if (blockItemId != 'root') {
        file[itemId].block = blockTree.getUserData(blockItemId, 'blockName');

        file[itemId].fileId = pData['fileId'];//blockItemId;//brunchId == 0 ? -1 : brunchId;
    }

    // Пустой шаблон
    var emptyBlock = pData['list'].length == 0;
    if (emptyBlock) {
        pData['list'][0] = pData['file'];
    }

    var parentBrunchId = blockItemId == 'root' ? 0 : blockItemId;
    for (blName in pData['list']) {
        var newId = wareframe.block.newId--;
        var name = pData['list'][blName];
        name = name ? name : blName;
        blockTree.insertNewItem(parentBrunchId, newId, name, null, "folderEmpty.gif", 0, 0, 'CHILD');
        blockTree.setUserData(newId, 'type', 0);
        blockTree.setUserData(newId, 'fileId', itemId);
        blockTree.setUserData(newId, 'blockName', blName);
    } // for

    blockTree.changeItemId(blockItemId, itemId);
    blockTree.setUserData(itemId, 'fileId', pData['fileId']);

    if (emptyBlock) {
        blockTree.setUserData(newId, 'type', wareframe.FOLDER_EMPTY);
        blockTree.setUserData(newId, 'blockName', 'empty');
        blockTree.setItemImage(newId, 'folderClosed.gif');
        blockTree.setUserData(newId, 'fileId', itemId);
    }//

    // func. wareframe.fs.tplToBlockSuccess
}

wareframe.block.saveClick = function () {
    if (!confirm('Сохранить?')) {
        return;
    }
    // TODO: вставить проверку на то что, что то есть в блоках
    var wfId = wareframe.getWFId();
    var acId = wareframe.acId;

    var file = $.toJSON(wareframe.block.file);
    var del = $.toJSON(wareframe.block.del);
    HAjax.saveBlock({data:{file:file, wfid:wfId, acid:acId, del:del}, methodType:'POST'});
    // func. wareframe.block.saveClick
}

wareframe.item.addClick = function () {
    var blockId = wareframe.tree.block.getSelectedItemId();
    if (blockId == '') {
        alert('Выбирите блок');
        return;
    }
    if (blockId < 0) {
        alert('Сохраните блоки');
        return;
    }
    wareframe.grid.addRow(wareframe.grid.newId, '', wareframe.grid.getRowsNum());
    --wareframe.grid.newId;
}

wareframe.utils.getImgSettings = function (pId) {
    return imgTheme + 'edit_16.png^Настройки^javascript:wareframe.utils.showItemSettings(' + pId + ')^_self';
}

wareframe.utils.showItemSettings = function (pId) {
    utils.go(utils.url({contr:'blockItem', query:'id=' + pId + '&acid=' + wareframe.acId}));
}

wareframe.getWFId = function () {
    return wareframe.wfId ? wareframe.wfId : wareframe.tree.wf.getSelectedItemId();
}

wareframe.item.saveItemBtnClick = function () {
    var acId = wareframe.acId;// == -1 ? 'null' : wareframe.acId;
    var blId = wareframe.tree.block.getSelectedItemId();
    var wfId = wareframe.getWFId();

    // Если было перетаскивание компонентов, нужно сохранить порядок
    var gridItemIdList = '';
    if (wareframe.item.dragDrop) {
        gridItemIdList = wareframe.grid.getAllItemIds();
    }

    var json = wareframe.grid.serializeToJSON();
    if (json.total_count == 0 && !wareframe.item.dragDrop) {
        return false;
    }
    var data = 'data=' + $.toJSON(json.rows) + '&position=' + gridItemIdList;
    var query = {acid:acId, blid:blId, wfid:wfId};
    HAjax.saveBlockItem({method:'saveBlockItem', data:data, methodType:'POST', query:query});
    // func. wareframe.item.saveItemBtnClick
}

/**
 * Callback функция на сохранения компоннетов блока
 * pData - представляем собой объект вид:
 * {'oldId_1':'newId_1', 'oldId_2':'newId_2'}
 */
wareframe.item.saveBlockItemSuccess = function (pData) {
    if (pData['error']) {
        alert(pData['error']['msg']);
        return;
    }

    wareframe.item.dragDrop = false;

    // Обновляем ID и добавляем картинки
    var listId = pData['listid'];
    for (var oldId in listId) {
        var newId = listId[oldId];
        var img = wareframe.utils.getImgSettings(newId)
        wareframe.grid.cells(oldId, 1).setValue(img);
        wareframe.grid.changeRowId(oldId, newId);
    }
    // Изменяем в дереве блоков иконку у ветки(блока)
    var blId = pData['blid'];
    wareframe.tree.block.setItemImage(blId, 'comp.gif');
    wareframe.tree.block.setUserData(blId, 'type', wareframe.FOLDER_COMP);
    wareframe.grid.clearChangedState();
    alert('Данные сохранены');
    // func. wareframe.item.saveBlockItemSuccess
}

/**
 * Ajax
 */
wareframe.block.saveBlockSuccess = function (pData) {
    if (pData['error']) {
        alert(pData['error']['msg']);
        return;
    }

    var list = pData['new'];
    var file = wareframe.block.file;
    var blockTree = wareframe.tree.block;

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

    wareframe.block.file = {};
    wareframe.block.del = [];

    alert('Данные сохранены');

    // func. wareframe.block.saveBlockSuccess
}

wareframe.item.onBeforeDrag = function (rId) {
    // Запрещаем при редактировании через actoin,
    // двигать родительские компоненты
    var acId = this.getUserData(rId, 'acId');
    return wareframe.acId == acId || acId == '';
}

wareframe.item.onDrop = function () {
    wareframe.item.dragDrop = true;
}

wareframe.onRowDblClicked = function (rId) {
    // Запрещаем при редактировании через actoin,
    // редактировать родительские компоненты
    var acId = this.getUserData(rId, 'acId');
    return wareframe.acId == acId || acId == '';
}

// ID экшена
wareframe.acId = '<?= self::get('acId') ?>';
// ID страницы
wareframe.wfId = '<?= self::get('wfId') ?>';
// ID блока
//wareframe.blId = '<?= self::get('blId') ?>';

/**
 * Дерево с блоками
 */
var blockTree = {
    tree:{ id:'blockTree', json: <?= self::get('blockTree') ?> },
    clickEnd:wareframe.block.click
}

/**
 * Дерево с шаблонами сайта
 */
var filesysTree = {
    tree:{ json: <?= self::get('filesysTree') ?>, id:'fsTree' },
    dbClick:wareframe.fs.dbClick
}

var compTree = {
    tree:{ json: <?= self::get('compTree') ?>, id:'compTree' }
}

/**
 * Дерево созданных Wareframe
 */
var wfTree = {
    tree:{ json: <?= self::get('wfTree') ?>, id:'wfTree' },
    dirAdd:{ url:{method:'dirAdd'}, id:'#dirAdd' },
    rmObj:{ url:{method:'rmObj'}, id:'#rmObj' },
    fileAdd:{ url:{method:'fileAdd'}, id:'#fileAdd' },
    renameObj:{ url:{method:'renameObj'}, id:'#rename' },
    clickEnd:wareframe.wf.click
}

$(document).ready(function () {
    dhtmlxInit.init({'wareframe':wfTree, 'block':blockTree, 'fs':filesysTree, 'comp':compTree });
    wareframe.tree.wf = dhtmlxInit.tree['wareframe'];
    wareframe.tree.block = dhtmlxInit.tree['block'];
    wareframe.tree.fs = dhtmlxInit.tree['fs'];

    if (wareframe.wfId) {
        $('.tdWF').hide();
    }

    HAjax.create({
        loadBlockTree:wareframe.wf.loadBlockTree,
        tplToBlock:wareframe.fs.tplToBlockSuccess,
        saveBlock:wareframe.block.saveBlockSuccess,
        saveBlockItem:wareframe.item.saveBlockItemSuccess,
        loadBlockItem:wareframe.item.loadBlockItemSuccess,
        rmBlockItem:wareframe.item.rmBlockItemSuccess
    });


    var grid = wareframe.grid = new dhtmlXGridObject('blockItemGrid');
    grid.newId = -1;
    grid.setImagePath("res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/imgs/");
    grid.setHeader(",,Описание,Сист. назв,Компонент");
    grid.setInitWidths("32,32,*,125,125");
    grid.setColsName([0, '', 'name', 'sysname', 'compId']);
    grid.setColTypes("ch,img,ed,ed,stree");
    grid.setSkin("dhx_skyblue");
    grid.enableDragAndDrop(true);
    grid.setSubTree(dhtmlxInit.tree['comp'], 4);
    grid.init();

    grid.attachEvent("onDrop", wareframe.item.onDrop);
    grid.attachEvent("onBeforeDrag", wareframe.item.onBeforeDrag);
    grid.attachEvent("onRowDblClicked", wareframe.onRowDblClicked);

    // Кнопка сохранения дерева блоков
    $('#blockSave').click(wareframe.block.saveClick);
    $('#blockRm').click(wareframe.block.rmClick);
    $('#itemRm').click(wareframe.item.rmClick);
    $('#itemAdd').click(wareframe.item.addClick);

    $('#blockItemSave').click(wareframe.item.saveItemBtnClick);
    // func. $(document).ready(function()
});

</script>