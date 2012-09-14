<div class="dt">Компонент:</div>
<div class="dd">
    <a href="#compDlg" id="compBtn">
        <img src="<?= self::res('images/folder_16.png') ?>" />
    </a>
    <span id="compPath"></span>
</div>

<div class="dt">Функциональный класс</div>
<div class="dd">
    <a id="classBtn" href="#classTreeDlg" class="btn">
        <img src="<?= self::res('images/folder_16.png') ?>" alt="Класс компонента"/>
        <span id="classFileText"></span>
    </a>
</div>

<div class="dt">Методы:</div>
<div class="dd"><select name="methodName" id="methodName"></select></div>

<div class="dt">Контент:</div>
<div class="dd">
    <a href="#contDlg" id="contBtn">
        <img src="<?= self::res('images/folder_16.png') ?>" />
    </a>
    <span id="contPath"></span>
</div>


<div id="contDlg" style="display: none; width:250px;height:350px; "></div>
<div id="compDlg" style="display: none; width:250px;height:350px; "></div>
<div id="classTreeDlg" style="width:250px;height:350px; display: none"></div>

<script type="text/javascript">

varible.saveDataClick = function () {
    var data = $('#contentForm').serialize();
    data += '&compId=' + varibleData.compTreeSelectId;
    data += '&' + varibleMvc.getSaveData();
    data += '&contId=' + varibleData.contTreeSelectId;
    HAjax.saveData({data:data, methodType:'POST'});
    // func. saveDataClick
}


var varibleData = {
    // Json данные для построение дерева классов
    classTreeJson: <?= self::get('classTree', 'null') ?>,
    methodName: '<?= self::get('methodName') ?>',
    // Выбранное значение в дереве классов
    classTreeSelectId: '<?= self::get('classFile')?>',
    methodList: <?= self::get('methodList', '[]')?>,
    compTreeJson: <?= self::get('compTree') ?>,
    contTreeJson: <?= self::get('contTree', 'null') ?>,
    compTreeSelectId: <?= self::get('compid', 'null') ?>,
    contTreeSelectId: <?= self::get('contid', 'null') ?>
} // varibleData

var varibleMvc = (function () {

    var classTree;
    var contTree;
    var compTree;


    function initTree() {
        dhtmlxInit.init({
            'comp': {
                tree:{ id:'compDlg', json: varibleData.compTreeJson },
                dbClick: compTreeDbClick
            },
            'cont':{
                tree:{ id:'contDlg', json: varibleData.contTreeJson },
                dbClick: contTreeDbClick
            },
            'class':{
                tree:{ id:'classTreeDlg', json:varibleData.classTreeJson },
                dbClick:classTreeDbClick
            }
        });
        compTree = dhtmlxInit.tree['comp'];
        contTree = dhtmlxInit.tree['cont'];
        classTree = dhtmlxInit.tree['class'];
        // func. initTree
    }
    
    function beforeCompDlgShow(){
        compTree.selectItem(varibleData.compTreeSelectId);
        // func. beforeCompDlgShow
    }

    function beforeContDlgShow(){
        if (!varibleData.compTreeSelectId) {
            alert('Выбирите компонент');
            return;
        }
        contTree.selectItem(varibleData.contTreeSelectId);
        // func. beforeContDlgShow
    }

    function cbSaveDataSuccess(pData) {
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        }
        alert('Данные успешно сохранены');
        // func. cbSaveDataSuccess
    }

    function initLoad(){
        var itemId = varibleData.classTreeSelectId;
        classTree.selectItem(itemId);
        var text = utils.getTreeUrl(classTree, itemId);
        $(options.classFileText).html(text);

        cbCompLoadMethods(varibleData.methodList)

        text = utils.getTreeUrl(contTree, varibleData.contTreeSelectId);
        $('#contPath').html(text);
        
        text = utils.getTreeUrl(compTree, varibleData.compTreeSelectId);
        $('#compPath').html(text);
        // func. initLoad
    }

    function cbCompLoadMethods(pData) {
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        }
        var $methodName = $('#methodName').find('option').remove().end();
        $.each(pData, function (key, value) {
            $methodName
                    .append($("<option></option>")
                    .attr("value", value)
                    .text(value));
        });
        // func. cbCompLoadMethods
    }

    /**
     * OnDbClick по ветке в дереве классов. Выбор класса и подгрузка его методов
     */
    function classTreeDbClick(pItemId, pTree) {
        // Получаем тип ветки: 1-папка, 0-файл
        var type = pTree.getUserData(pItemId, 'type');
        // Выбрать можно только файл
        if (type != 1) {
            return false;
        }
        // Запоминаем наш выбор
        varibleData.classTreeSelectId = pItemId;
        // Отображаем на странице наш выбор
        var text = utils.getTreeUrl(pTree, pItemId);
        $(options.classFileText).html(text);
        // Удаляем все ненужные ветки

        var varStorage = $('#varStorage').val();

        // Загружаем методы класса
        HAjax.compLoadMethods({
            query:{
                classFile: pItemId,
                compId: varibleData.compTreeSelectId
            }
        });

        // Закрываем диалоговое окно
        $.fancybox.close();
        // func. blockItem.classTreeDbClick
    }

    /**
     * Двойной клик по дереву контента. Выбор статического контента
     */
    function contTreeDbClick(pItemId, pTree) {
        varibleData.contTreeSelectId = pItemId;
        var text = utils.getTreeUrl(pTree, pItemId);
        $('#contPath').html(text);
        $.fancybox.close();
        // func. contTreeDbClick
    }

    function cbCompLoadCompData(pData){
        if (pData['error']) {
            alert(pData['error']['msg']);
            return;
        }
        classTree.loadJSONObject(pData['classTree']);
        contTree.loadJSONObject(pData['contTree']);
        // func. cbCompLoadCompData
    }

    function beforeClassDlgShow() {
        if (!varibleData.compTreeSelectId) {
            alert('Выбирите компонент');
            return false;
        }
        classTree.selectItem(varibleData.classTreeSelectId);
        // func. beforeTplDlgShow
    }

    function getSaveData() {
        return 'classFile=' + varibleData.classTreeSelectId;
        // func. getSaveData
    }

    function compTreeDbClick(pItemId, pTree){
        // Получаем тип ветки: 1-папка, 0-файл
        var type = pTree.getUserData(pItemId, 'type');
        // Выбрать можно только файл
        if (type != 1) {
            return false;
        }

        if (varibleData.compTreeSelectId == pItemId) {
            return false;
        }

        varibleData.compTreeSelectId = pItemId;
        $('#compPath').html(utils.getTreeUrl(pTree, pItemId));

        // Убираем старый текст у classTree
        $(options.classFileText).html('');
        // Убираем ранее сохранённое значение у classTree
        varibleData.classTreeSelectId = '';
        varibleData.contTreeSelectId = '';

        // Убираем все методы
        cbCompLoadMethods([]);

        // Чистим дерево контента
        contTree.deleteChildItems(0);
        // Чистим дерево класса
        classTree.deleteChildItems(0);


        $('#contPath').html('');

        HAjax.compLoadCompData({data:{
            compid: pItemId
        }});

        $.fancybox.close();
        // func. compTreeDbClick
    }

    function init(pOptions) {
        options = pOptions;

        initTree();
        initLoad();

        HAjax.create({
            compLoadMethods: cbCompLoadMethods,
            compLoadCompData: cbCompLoadCompData,
            saveData: cbSaveDataSuccess
        });

        $(options.classBtn).fancybox({
            beforeOpen:beforeClassDlgShow
        });

        $(options.contBtn).fancybox({
            beforeOpen:beforeContDlgShow
        });

        $(options.methodNameObj).val(varibleData.methodName);

        $(options.compBtn).fancybox({
            beforeOpen:beforeCompDlgShow
        });

        // func. init
    }

    return{
        init:init,
        getSaveData:getSaveData
    }
})();

$(document).ready(function () {

    varibleMvc.init({
        methodNameObj:'methodName',
        classBtn:'#classBtn',
        classFileText:'#classFileText',
        contBtn: '#contBtn',
        compBtn: '#compBtn',
        varStorage: '#varStorage'
    });

    // func $.ready
});
</script>