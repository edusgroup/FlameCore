<link href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

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
        background-color: #f5f5f5;
        border: 1px solid Silver;;
        overflow: auto;
    }

    img.img_button {
        cursor: pointer
    }

    .list_item {
        vertical-align: top;
        padding-left: 10px
    }

    div .dt {
        font-weight: bold
    }

    div .dd {
        padding-left: 25px
    }

    .hidden {
        display: none
    }
</style>

<!-- start panel right column -->
<div class="column">
    <!-- start panel right panel -->
    <div class="panel corners">
        <!-- start panel right title -->
        <div class="title corners_top">
            <div class="title_element">

                <a style="margin-left: 10px" href="" title="В начало">
                    <img src="<? self::res('images/home_16x16.png') ?>" alt="В начало" width="16" height="16"
                         alt="В начало"/>
                    В начало /
                </a>
                <span id="history">{Hisotry}</span>
            </div>
        </div>
        <!-- end title -->
        <!-- start panel right content -->
        <div class="content" id="mainpanel">


            <div class="boxmenu corners">
                <ul class="menu-items">
                    <li>
                        <a href="#back" id="backBtn" title="Назад">
                            <img src="<?= self::res('images/back_32.png') ?>" alt="Назад"/><span>Назад</span>
                        </a>
                    </li>
                    <li>
                        <a href="#save" id="saveBtn" title="Сохранить">
                            <img src="<?= self::res('images/save_32.png') ?>" alt="Сохранить"/><span>Сохранить</span>
                        </a>
                    </li>

                </ul>
            </div>


            <div class="content" id="mainpanel">
                <form id="menuForm">
                    <table>
                        <tr class="bold">
                            <td class="vmiddle img_button" style="width: 200px">
                                <img id="dirAdd" class="img_button" src="<?= self::res('images/dadd_24.png') ?>"
                                     alt="Добавить папку"/>
                                <img id="rmObj" class="img_button" src="<?= self::res('images/del_24.png') ?>"
                                     alt="Удалить объект"/>
                                <img id="rename" class="img_button" src="<?= self::res('images/edit_24.png') ?>"
                                     alt="Переименовать объект"/>
                                <img id="cotrol" class="img_button" src="<?= self::res('images/link_24.png') ?>"
                                     alt="Загрузка данных"/>
                            </td>
                            <td><!-- Без кнопок --></td>
                        </tr>
                        <tr>
                            <td id="menuTree" class="treeBlock"><!-- Для дерева --></td>
                            <td class="list_item" id="menudata"><!-- Для свойств --></td>
                        </tr>

                    </table>
                </form>


            </div>
            <!-- end panel right content -->
        </div>
        <!-- end panel right content -->
    </div>
    <!-- end panel right panel -->
</div><!-- end panel right column -->

<script type="text/javascript">

    var menu = {
        tree:{
            menu:null,
            data:{
                menu:null
            }
        },
        compid: <?= self::get('compId') ?>,
        contid: <?= self::get('contId') ?>
    }

    var contrName = menu.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);

    menu.saveClick = function () {
        var menuId = menu.tree.menu.getSelectedItemId();
        var data = $('#menuForm').serialize();
        data += '&menuid=' + menuId;
        //data += '&fileImg=' + menu.fileImg;
        HAjax.saveData({data:data, methodType:'POST'});
        return false;
        // func. menu.saveClick
    }

    menu.saveDataSuccess = function (pData) {
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        }

        alert('Данные успешно сохранены');
        // func. menu.saveDataSuccess
    }

    menu.tree.menuClick = function (pItemId, pTree) {

        var param = 'contid=' + menu.contid + '&menuid=' + pItemId;
        var url = utils.url({method:'loadMenuData', query:param });
        $('#menudata').load(url);
        // func. menu.tree.menuClick
    }

    menu.tree.data.menu = {
        tree:{
            id:'menuTree',
            json: <?= self::get('menuTree') ?>
        },
        dirAdd:{
            url:{ method:'dirAdd'},
            id:'#dirAdd'
        },
        rmObj:{
            url:{ method:'rmObj'},
            id:'#rmObj'
        },
        renameObj:{
            url:{ method:'renameObj'},
            id:'#rename'
        },
        clickEnd:menu.tree.menuClick
    }

    menu.selectFileClick = function () {
        var menuId = menu.tree.menu.getSelectedItemId();
        var urlWindow = utils.url({
            method:'fileManager',
            query:{menuid:menuId, type:'img'}
        });
        window.open(urlWindow, 'Выберите файл',
            'width=800,height=600,scrollbars=yes,resizable=yes,location=no,status=yes,menubar=yes');
    }

    $(document).ready(function () {
        dhtmlxInit.init({'menu':menu.tree.data.menu});
        menu.tree.menu = dhtmlxInit.tree['menu'];

        // Кнопка Назад
        $('#backBtn').attr('href', utils.url({
            type:'manager',
            contr:'complist'
        }));
        $('#saveBtn').click(menu.saveClick);

        HAjax.create({
            saveData:menu.saveDataSuccess
        });

        // func. $(document).ready
    });
</script>