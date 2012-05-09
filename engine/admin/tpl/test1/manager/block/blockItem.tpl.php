<link href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen"/>

<style type="text/css">
    div .items .dt {
        font-weight: bold
    }

    div .items .dd {
        padding-left: 25px;
    }

    div.bothPanel {
        float: left;
        margin-right: 10px;
    }

    select.contCaption {
        width: 200px;
    }
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
                    <a id="backBtn" title="Назад">
                        <img src="<?= self::res('images/back_32.png') ?>" alt="Назад"/><span>Назад</span>
                    </a>
                </li>

                <li>
                    <a id="saveBtn" title="Сохранить">
                        <img src="<?= self::res('images/save_32.png') ?>" alt="Сохранить"/><span>Сохранить</span>
                    </a>
                </li>

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

                <li>
                    <a href="?$t=manager&$c=wareframe" title="WF">
                        <img src="<?= self::res('images/wf_32.png') ?>" alt="Comp List"/><span>WareFrame</span>
                    </a>
                </li>

            </ul>
        </div>
        <div class="content" id="mainpanel">

            <div class="items">
                <form id="mainForm">
                    <div class="dt">Описание</div>
                    <div class="dd">
                        <?= self::get('descr') ?>
                    </div>

                    <div class="dt">Системное название</div>
                    <div class="dd">
                        <?= self::get('sysname') ?>
                    </div>

                    <div class="dt">Компонент</div>
                    <div class="dd">
                        <?= self::get('compname') ?>
                    </div>

                    <div class="dt">Частн. настройки</div>
                    <div class="dd">
                        <a href="#" id="customSettings">
                            <img src="<?= self::res('images/edit_16.png') ?>"/>
                            Настроить
                        </a>
                    </div>

                    <div class="dt">Доступ</div>
                    <div class="dd">
                        <a href="#" id="userAccessBtn">
                            <img src="<?= self::res('images/edit_16.png') ?>"/>
                            Настроить
                        </a>
                    </div>

                    <div class="dt">Шаблон компонента</div>
                    <div class="dd">
                        <a id="tplBtn" href="#tplDlg">
                            <img class="folderBtn" alt="Шаблон компонента"/>
                            <span id="tplFileText"></span>
                        </a>
                    </div>

                    <div class="dt">Класс компонента</div>
                    <div class="dd">
                        <a id="classBtn" href="#classDlg">
                            <img class="folderBtn" alt="Класс компонента"/>
                            <span id="classFileText"></span>
                        </a>
                    </div>

                    <div class="dt">Метод класса</div>
                    <div class="dd">
                        <select name="methodName" id="methodName"></select>
                    </div>

                    <div class="dt">Url Tpl</div>
                    <div class="dd" id="urlTplBox">

                    </div>

                    <div class="dt">Static content</div>
                    <div class="dd">
                        <img src="<?= self::res('images/del_16.png') ?>" alt="Очистить" id="contStatClearBtn"/>
                        <a id="contBtn" href="#contDlg">
                            <img class="folderBtn" alt="Выбрать контент"/>
                            <span id="statContText"></span>
                        </a>
                    </div>

                    <div class="dt">Var Content</div>
                    <div class="dd" id="varContDiv">
                        <? self::selectIdName($this->get('varList'), 'name="varName" id="varName"'); ?>
                    </div>

                    <div class="onlyFolder">
                        <div class="dt">Var Table</div>
                        <div class="dd">
                            <? self::selectIdName($this->get('varList'), 'name="varTableName" id="varTableName"'); ?>
                        </div>
                    </div>

                    <!--<div class="dt">Настройки URL</div>
                    <div class="dd">
                        <a href="#urlSettingsDlg" id="urlSettings">
                            <img src="<?= self::res('images/settings_16.png') ?>" alt="Настроки URL"/>
                            Настроить
                        </a>
                    </div>-->

                    <div class="dt">Url Regexp</div>
                    <div class="dd">
                        <a href="#add" id="addBtn">
                            <img src="<?= self::res('images/add_32.png') ?>" alt="Добавить"/>
                        </a>
                    </div>
                    <div class="dd" id="contentList"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="urlSettingsDlg" style="width:300px;height:150px; display: none">
    Сделать
</div>

<div id="tplDlg" style="width:150px;height:150px; display: none"></div>

<div id="actionDlg" style="width:150px;height:150px; display: none"></div>

<div id="classDlg" style="width:150px;height:150px; display: none"></div>

<div id="contDlg" style="width:400px;height:150px; display: none">
    <div class="bothPanel" id="contTree">
    </div>
    <div id="contSelect" class="bothPanel" style="display: none;">
        <div>
            <select class="contCaption"></select>
        </div>
        <div>
            <input type="button" value="Выбрать" id="contSelectBtn"/>
        </div>
    </div>
</div>

<script type="text/javascript">
var contrName = 'blockItem';
var callType = 'manager';
utils.setType(callType);
utils.setContr(contrName);
HAjax.setContr(contrName);
HAjax.setType(callType);

//TODO: Перевести аттрибут name в TR и переделать получение в методах
// name через TR
var blockItem = {
    acId:'<?= self::get('acId') ?>',
    id: <?= self::get('blockItemId') ?>,
    resUrl:'<?= self::res('images/') ?>',
    // Есть ли у компонента таблицы
    onlyFolder: <?= self::get('onlyfolder') ?>,
    // Сохранённые данные
    saveData: <?= self::get('saveData', 'null') ?>,
    // Методы сохранённого класса
    classData: <?= self::get('classData', '[]') ?>,
    // Значаение contId выбранные для regexp Url
    regxList: <?= self::get('regxList', '{}') ?>,
    // Название табличной части у статичного компонента.
    // Используется только при onlyFolder=1 и при сохранённых данных
    statName:'<?= self::get('statName') ?>',
    // Равен ли acIdBlock текущему acId. т.е. правим страницу через
    // URL tree или через Wareframe
    acParent: <?= self::get('acParent') ?>,
    // Деревья
    tree:{},
    // Название выбранного шаблона для компонента
    tplFile:'',
    // Название выбранного  для компонента
    classFile:'',
    // Номер URL regexp элемента
    contCurrent:0,
    // Id DIV для URL regexp
    regxSelId:'',
    // Доп Id Контента для URL regexp( если компонен имеет деление на таблицу)
    tableId:'',
    // Для чего показывается окно: url или static
    contType:'',
    // Выбранное значение static компонента
    statId:'',
    varId:-1,
    // Для тех компонентов у которых в классе нужно заполнить urlTpl
    urlTplList: <?= self::get('urlTplList', '{}') ?>,
    // Редактируемый urlTpl
    urlTplCurrent:null
} // var blockItem

/**
 * OnDbClick по ветке в дереве классов. Выбор класса и подгрузка его методов
 */
blockItem.classTreeDbClick = function (pItemId, pTree) {
    // Получаем тип ветки: 1-папка, 0-файл
    var type = pTree.getUserData(pItemId, 'type');
    // Выбрать можно только файл
    if (type != 1) {
        return false;
    }
    // Запоминаем наш выбор
    blockItem.classFile = pItemId;
    // Отображаем на странице наш выбор
    var text = utils.getTreeUrl(pTree, pItemId);
    $('#classFileText').html(text);

    // Загружаем методы класса
    HAjax.loadClassMethod({query:{'class':pItemId, blockitemid:blockItem.id }});

    // Закрываем диалоговое окно
    $.fancybox.close();
    // func. blockItem.classTreeDbClick
}

/**
 * OnDbClick по ветке в дереве шаблонов. Выбор шаблона
 */
blockItem.tplTreeDbClick = function (pItemId, pTree) {
    // Получаем тип ветки: 1-папка, 0-файл
    var type = pTree.getUserData(pItemId, 'type');
    // Выбрать можно только файл
    if (type != 1) {
        return false;
    }
    // Запоминаем наш выбор
    blockItem.tplFile = pItemId;
    // Отображаем на странице наш выбор
    var text = utils.getTreeUrl(pTree, pItemId);
    $('#tplFileText').html(text);

    // Закрываем диалоговое окно
    $.fancybox.close();
    // blockItem.tplTreeDbClick
}

/**
 * Событие fancyBox. Для выборка ветки при повторном пока.
 */
blockItem.beforeShowTplDlg = function () {
    blockItem.tree.tpl.selectItem(blockItem.tplFile);
    // func. blockItem.beforeShowTplDlg
}

/**
 * Событие fancyBox. Для выборка ветки при повторном пока.
 */
blockItem.beforeShowClassDlg = function () {
    blockItem.tree.clss.selectItem(blockItem.classFile);
    // func. blockItem.beforeShowClassDlg
}

/**
 * Callback функция.
 * Обрабатываем методы от класса. Вызывается функцией
 * blockItem.classTreeDbClick
 */
blockItem.loadClassMethodSuccess = function (pData) {
    if (pData['error']) {
        alert(pData['error']['msg']);
        return;
    }
    // Ощичаем старый набор методом
    var $methodName = $('#methodName').find('option').remove().end();
    // Добавляем новый набор методов
    $.each(pData['method'], function (key, value) {
        $methodName
            .append($("<option></option>")
            .attr("value", value)
            .text(value));
    });

    // Создаём
    if (pData['urlTpl']) {
        var $urlTplBox = $('#urlTplBox').html('');
        $.each(pData['urlTpl'], function (key, value) {
            $urlTplBox
                .append('<div>'
                + value + ' '
                + '<img src="' + blockItem.resUrl + 'folder_16.png" '
                + 'onclick="blockItem.showActionTree(\'' + value + '\')"/> '
                + '<span id="' + value + 'Text"></span>'
                + '</div>');
        }); // each
    } // if
    // func. blockItem.loadClassMethodSuccess
}


blockItem.actionTreeDbClick = function (pItemId, pTree) {
    blockItem.urlTplList[blockItem.urlTplCurrent] = pItemId;
    var text = utils.getTreeUrlTpl(pTree, pItemId);
    $('#' + blockItem.urlTplCurrent + 'Text').html(text);
    $.fancybox.close();
    // func. blockItem.actionTreeDbClick
}

blockItem.showActionTree = function (pUrlTpl) {
    blockItem.urlTplCurrent = pUrlTpl;
    $.fancybox({
        href:'#actionDlg'
    });
}

/**
 * onClick по кнопке добавление UrlRegexp
 * Добавляет новые элементы для заполнения
 */
blockItem.addBtnClick = function () {
    var id = blockItem.contCurrent++;
    blockItem.appendAddBtn(id, '');
    // func. blockItem.addBtnClick
}

/**
 * onClick по кнопке у static content.
 * Выберает ветку в cont tree
 */
blockItem.contBtnStatClick = function () {
    // Запоминаем что окно с деревом вышло для обработки статичного
    // контента
    blockItem.contType = 'stat';
    // Выбираем ветку и сразу вызываем её onClick событие(true)
    blockItem.tree.cont.selectItem(blockItem.statId, true);
    // func. blockItem.contBtnStatClick
}

/**
 * onClick по кнопке у url regexp
 * Выберает ветку в cont tree
 */
blockItem.contBtnUrlClick = function () {
    // Получаем ID выбранного Url Regexp
    // substr делаем потом есть префикс cont_
    var id = $(this).parent().attr('id').substr(5);
    // Запоминаем его
    blockItem.regxSelId = id;
    // Запоминаем что окно с деревом вышло для обработки Url Regexp
    blockItem.contType = 'url';
    // Если мы по нему что нить уже записывали
    if (blockItem.regxList[id]) {
        var obj = blockItem.regxList[id];
        // То выбирем ветку в дереве при диалоге с деревом
        // Сразу стартуем событие onClick у дерева.
        blockItem.tree.cont.selectItem(obj['contId'], true);
    }
    // func. blockItem.contBtnUrlClick
}

/**
 * Добавление нового элемента regexp url на страницу
 */
blockItem.appendAddBtn = function (id, regxValue) {
    // Хранит название папки, при возможности редактировать
    var folderImgSrc = 'folder_16.png';
    // Содержит HTML код кнопки для удаления элемента regexp url
    var delImgHtml = '<img src="' + blockItem.resUrl + 'del_16.png" class="rmBtn"/> ';
    // Хранит HTML код, readonly ли поле ввода фильтра regexp
    var readOnly = '';
    // Если мы создали в общей Wareframe и редактируем для конкретного acId
    // то делаем ограничения
    if (!blockItem.acParent) {
        // Меняем картинку папки
        folderImgSrc = 'folderGray_16.png';
        // Убераем кнопку удаления элемента regexp url
        delImgHtml = '';
        // Делаем поле ввода фильтра(regexp) не редактируемым
        readOnly = 'readonly="readonly"';
    }
    // Добавлеям новый элемент
    $('#contentList').append(
        '<div id="cont_' + id + '">'
            + delImgHtml
            + '<input type="text" name="regx[' + id + ']" value="' + regxValue + '"' + readOnly + '/> '
            + '<img src="' + blockItem.resUrl + folderImgSrc + '" class="compBtn"/> <span class="regxContText"></span>'
            + '</div>');
    // см. описания переменной. в кратце acIdParent == acId под которым вошли
    if (blockItem.acParent) {
        // Прописываем на кнопку папки событие onClick, что бы показать диалоговое
        // окно
        $('#cont_' + id + ' .compBtn').click(blockItem.contBtnUrlClick).fancybox({
            href:'#contDlg'
        });
        // Прописываем на кнопку удаления элемента regex url, событие onClick
        $('#cont_' + id + ' .rmBtn').click(blockItem.rmRegxContBtnClick);
    }
    // func. blockItem.appendAddBtn
}

/**
 * onClick по ветке дерева контента
 */
blockItem.contTreeClick = function (pItemId, pTree) {
    // если компонент blockItem имеет onlyFolder = 1
    if (blockItem.onlyFolder) {
        // То подгружаем его табличные данные
        HAjax.loadCompTable({query:{contid:pItemId}});
        return false;
    }
    // Если это кнопка нажата у статики то обработка статики, если regexp url
    // то меняем обработчик на URL regexp
    if (blockItem.contType == 'stat') {
        blockItem.contTreeStatClick(pItemId, pTree);
    } else {
        blockItem.contTreeUrlClick(pItemId, pTree);
    }

    // Закрываем диалоговое окно
    $.fancybox.close();
    // func. blockItem.contTreeClick
}

/**
 * Выбор ветки дерева контента для stat контента. При onlyFolder = 0
 */
blockItem.contTreeStatClick = function (pItemId, pTree) {
    // Запоминаем наш выбор статичного компонента
    blockItem.statId = pItemId;
    // Отображаем наш выбор
    var text = utils.getTreeUrl(pTree, pItemId);
    $('#statContText').html(text);
    // func. blockItem.contTreeStatClick
}

/**
 * Выбор ветки дерева контента для regxp. При onlyFolder = 0
 */
blockItem.contTreeUrlClick = function (pItemId, pTree) {
    // Запоминаем наш выбор статичного компонента
    blockItem.regxList[blockItem.regxSelId] = {contId:pItemId, tableId:''};
    // Отображаем наш выбор
    var text = utils.getTreeUrl(pTree, pItemId);
    $('#cont_' + blockItem.regxSelId + ' span.regxContText').html(text);
    // func. blockItem.contTreeUrlClick
}

/**
 * Очистка данных статичного контента
 */
blockItem.contStatClearBtnClick = function () {
    if (!confirm('Очистить')) {
        return false;
    }
    blockItem.statId = '';
    blockItem.tableId = '';
    $('#statContText').html('');
    // func. blockItem.contStatClearBtnClick
}

/**
 * Загрузка табличных даных для контента. При onlyFolder = 1
 */
blockItem.loadCompTableSuccess = function (pData) {
    // Очищаем старые табличные данные
    var $contTableName = $('#contSelect select').find('option').remove().end();
    // Добавляем новые
    $.each(pData, function (key, value) {
        $contTableName
            .append($("<option></option>")
            .attr("value", value['id'])
            .text(value['caption']));
    });

    var selId = null;
    // Если это обработка статичного контента, то получаем его ранее сохранённый
    // выбор, если это URL regxp получаем его ранее сохранёные выбор
    if (blockItem.contType == 'stat') {
        selId = blockItem.tableId;
    } else {
        selId = blockItem.regxSelId;
        if (blockItem.regxList[selId]) {
            selId = blockItem.regxList[selId]['tableId']
        }
    }
    // Выбыраем в списке табличного контента ранее сохранёный выбор, если он был
    $('#contSelect select').val(selId);
    // func. blockItem.loadCompTableSuccess
}

/**
 * OnClick по select при выборе контента.  При onlyFolder = 1
 */
blockItem.contSelectBtnClick = function () {
    var tree = blockItem.tree.cont;
    var itemId = tree.getSelectedItemId();
    // Select возле дерева, хранить названия контента
    var $select = $('#contSelect select');
    // Выбранно значение из списка ( ID content )
    var contSelId = $select.val();
    // Если ни чего не выбранно, просто выходим
    if (contSelId != null) {
        // =====================================================================
        if (blockItem.contType == 'stat') {
            // Запоминаем наш выбор
            blockItem.statId = itemId;
            blockItem.tableId = contSelId;
            // Отображаем наш выбор на странице
            var text = utils.getTreeUrl(tree, itemId);
            text += '/' + $select.find('option:selected').html();
            $('#statContText').html(text);
            // =====================================================================
        } else {
            // Отображаем наш выбор на странице
            var text = utils.getTreeUrl(tree, itemId);
            text += '/' + $select.find('option:selected').html();
            $('#cont_' + blockItem.regxSelId + ' span.regxContText').html(text);
            // Запоминаем наш выбор
            blockItem.regxList[blockItem.regxSelId] = {contId:itemId, tableId:contSelId};
        }
    }
    // =====================================================================
    // Закрываем диалоговое окно
    $.fancybox.close();
    // func. blockItem.contSelectBtnClick
}

/**
 * Удаление Regexp URL элемента
 */
blockItem.rmRegxContBtnClick = function () {
    // Подтвержаем что мы хотим удалить данные
    if (!confirm('Уверены что хотите удалить?')) {
        return false;
    }
    // Получаем DIV объекта
    var $parent = $(this).parent()
    var id = $parent.attr('id').substr(5);
    // Удаляем ранее сохранённые данные, если они были
    delete blockItem.regxList[id];
    $parent.remove();
    return false;
    // func. blockItem.rmRegxContBtnClick
}

/**
 * onClick по кнопке сохранения всех данных
 */
blockItem.saveBtnClick = function () {
    var data = $('#mainForm').serialize();
    // Выбранный шаблон
    data += '&tplFile=' + blockItem.tplFile;
    // Выбранный класс
    data += '&classFile=' + blockItem.classFile;
    // Выбранные табличные данные, если у компонента onlyFolder = 1
    data += '&tableId=' + blockItem.tableId;
    // Выбранные контента для статичного контента
    data += '&statId=' + blockItem.statId;
    // Regexp URL данные, будут сохранены в виде массива
    for (var item in blockItem.regxList) {
        data += '&cont[' + item + '][contid]=' + blockItem.regxList[item]['contId'];
        data += '&cont[' + item + '][tableid]=' + blockItem.regxList[item]['tableId'];
    } // for

    for (var name in blockItem.urlTplList) {
        data += '&urlTpl[' + name + ']=' + blockItem.urlTplList[name]
    } // for

    HAjax.saveData({methodType:'POST',
        query:{acid:blockItem.acId, id:blockItem.id},
        data:data});
    // func. blockItem.saveBtnClick
}

/**
 * CallBack функция
 * Обработка результата сохранения данных
 */
blockItem.saveDataSuccess = function (pData) {
    if (pData['error']) {
        alert(pData['error']['msg']);
        return;
    }

    alert('Данные успешно сохранены');
    // func. blockItem.saveDataSuccess
}

/**
 * Конфиг дерева шаблонов
 */
var tplTreeConf = {
    tree:{ json: <?= self::get('tplTree') ?>, id:'tplDlg' },
    dbClick:blockItem.tplTreeDbClick
} // var tplTreeConf

/**
 * Конфиг дерева контента
 */
var contTreeConf = {
    tree:{ json: <?= self::get('contTree') ?>, id:'contTree'},
    clickEnd:blockItem.contTreeClick
} // var contTreeConf

var actionTreeConf = {
    tree:{ json: <?= self::get('actionTree') ?>, id:'actionDlg'},
    dbClick:blockItem.actionTreeDbClick
} // var actionTreeConf

/**
 * Конфиг дерева классов
 */
var classTreeConf = {
    tree:{ json: <?= self::get('classTree') ?>, id:'classDlg'},
    dbClick:blockItem.classTreeDbClick
} // var classTreeConf

/**
 * Событие onReady
 */
$(document).ready(function () {
    // Создаём наши деревья
    dhtmlxInit.init({ 'tpl':tplTreeConf,
        'cont':contTreeConf,
        'action':actionTreeConf,
        'class':classTreeConf });
    blockItem.tree.tpl = dhtmlxInit.tree['tpl'];
    blockItem.tree.cont = dhtmlxInit.tree['cont'];
    blockItem.tree.clss = dhtmlxInit.tree['class'];
    blockItem.tree.action = dhtmlxInit.tree['action'];

    HAjax.create({
        loadClassMethod:blockItem.loadClassMethodSuccess,
        loadCompTable:blockItem.loadCompTableSuccess,
        saveData:blockItem.saveDataSuccess
    });

    if (blockItem.acParent) {
        // Обработка onClick на кнопке классов
        $('#classBtn').fancybox({
            beforeShow:blockItem.beforeShowClassDlg
        });
        // Обработка onClick на кнопке шаблонов
        $('#tplBtn').fancybox({
            beforeShow:blockItem.beforeShowTplDlg
        });
        // Обработка onClick на кнопке контента
        $('#contBtn').click(blockItem.contBtnStatClick).fancybox({
            href:'#contDlg'
        });

        // Обработка onClick на кнопке добавление нового элемента URL regexp
        $('#addBtn').click(blockItem.addBtnClick);
        // Обработка onClick на кнопке очистки статичного контета
        $('#contStatClearBtn').click(blockItem.contStatClearBtnClick);
    } else {
        // Скрываем кнопку добавления элементов URL regexp
        $('#addBtn').hide();
        // Скрываем кнопку очистки статичного контета
        $('#contStatClearBtn').hide();
    } // if blockItem.acParent

    // Если у компонента onlyFolder = 1
    if (blockItem.onlyFolder) {
        // Отображаем select который будет хранить табличные данные контента
        $('#contSelect').show();
        // Ставим на кнопку выбора табличных данных контета обработчик onClick
        $('#contSelectBtn').click(blockItem.contSelectBtnClick);
    } else {
        $('.onlyFolder').hide();
    } // if blockItem.onlyFolder

    // URL кнопки назад
    var url = utils.url({
        contr:'wareframe',
        query:{
            acid:blockItem.acId,
            blockitemid:blockItem.id
        }
    });
    // Кнопка назад
    $('#backBtn').attr('href', url);

    // URL кнопки частных настроек
    var url = utils.url({
        method:'customSettings',
        query:{
            acid:blockItem.acId,
            blockitemid:blockItem.id
        }
    });
    $('#customSettings').attr('href', url);

    // Обработка onClick на кнопку сохранения данных
    $('#saveBtn').click(blockItem.saveBtnClick);

    // Если есть сохранённые данные, нужно отобразить их на странице
    if (blockItem.saveData) {
        for (var key in blockItem.saveData) {
            blockItem[key] = blockItem.saveData[key];
        }
        // Отображаем на экране выбранное значение шаблона
        var text = utils.getTreeUrl(blockItem.tree.tpl, blockItem.tplFile);
        $('#tplFileText').html(text);
        // Отображаем на экране выбранное значение класса
        text = utils.getTreeUrl(blockItem.tree.clss, blockItem.classFile);
        $('#classFileText').html(text);
        // Отображаем на экране выбранное значение статичного контента
        text = utils.getTreeUrl(blockItem.tree.cont, blockItem.statId);

        if (blockItem.onlyFolder && blockItem.tableId != "" && blockItem.statName != "") {
            text += '/' + (blockItem.statName ? blockItem.statName : '[не найден]');
        }
        $('#statContText').html(text);

        // Загружуаем методы класса
        blockItem.loadClassMethodSuccess(blockItem.classData);

        if (blockItem.acParent) {
            // Отображаем на экране выбранное значение метода
            $('#methodName').val(blockItem.methodName);
        } else {
            // Убераем методы, ставим скрытую переменную и выводим ранее выбранное значение
            var html = '<input type="hidden" name="methodName" value="' + blockItem.methodName + '"/>';
            html += blockItem.methodName;
            $('#methodName').parent().append(html).end().remove();
        } // if ( blockItem.acParent )

    } // if ( blockItem.saveData )

    if (blockItem.acId == -1) {
        var html = 'Только при настройке Url Tree';
        html += '<input type="hidden" name="varName" value="' + blockItem.varId + '"/>';
        $('#varContDiv').html(html);
    } else {
        $('#varName').val(blockItem.varId);
        $('#varTableName').val(blockItem.varTableId);
    } // if ( blockItem.acId == -1 )

    // Если есть сохранёные данные по элемента regexp URL, нужно их отобразить
    if (blockItem.regxList) {
        // Бегаем по сохранёным данным
        for (var i in blockItem.regxList) {
            var item = blockItem.regxList[i];
            var id = blockItem.contCurrent++;
            // Добавляем их
            blockItem.appendAddBtn(id, item['regexp']);
            // Отображаем их название
            var text = utils.getTreeUrl(blockItem.tree.cont, item['contId']);

            if (blockItem.onlyFolder) {
                var caption = item['caption'];
                text += '/' + (caption ? caption : '[не найден]' );
            }
            $('#cont_' + id + ' span.regxContText').html(text);

        } // for
    } // if ( blockItem.regxList )

    var folderImgSrc = 'folder_16.png';
    if (!blockItem.acParent) {
        folderImgSrc = 'folderGray_16.png';
    }
    $('img.folderBtn').attr('src', blockItem.resUrl + folderImgSrc);

    if (blockItem.urlTplList) {
        for (var name in blockItem.urlTplList) {
            var acId = blockItem.urlTplList[name];
            var text = utils.getTreeUrlTpl(blockItem.tree.action, acId);
            $('#' + name + 'Text').html(text);
        } // for
    } // if

    $('#userAccessBtn').click(function () {
        var url = utils.url({
            contr:'biUserAccess',
            query:{
                blockItemId:blockItem.id
            }
        });
        utils.go(url);
    });

    // func. $.ready
});


</script>