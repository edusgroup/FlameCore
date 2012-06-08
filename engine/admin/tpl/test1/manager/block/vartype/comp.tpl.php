<div class="dt">Компонент:</div>
<div class="dd">
    <img src="<?= self::res('images/folder_16.png') ?>" id="compBtn"/>
    <span id="compPath"></span>
</div>

<div class="dt">Контент:</div>
<div class="dd">
    <img src="<?= self::res('images/folder_16.png') ?>" id="contBtn"/>
    <span id="contPath"></span>
</div>

<div class="dt">Тип класса</div>
<div class="dd">
    <select name="classType" id="classType">
        <option value="core">Встроенный</option>
        <option value="user">Пользовательский</option>
    </select>
</div>

<div class="dt">Классы:</div>
<div class="dd"><select name="className" id="className"></select></div>

<div class="dt">Методы:</div>
<div class="dd"><select name="methodName" id="methodName"></select></div>

<div id="contDlg" style="display: none"></div>
<div id="compDlg" style="display: none"></div>

<script type="text/javascript">
    
    var varComp = {
        tree:{
            data:{
                comp: <?= self::get('compTree') ?>,
                cont: <?= self::get('contTree', 'null') ?>
            }
            
        },
        compId: <?= self::get('compid', 'null') ?>,
        contId: <?= self::get('contid', 'null') ?>,
        className: <?= self::get('className', 'null') ?>,
        classType: <?= self::get('classType', 'null') ?>,
        methodName: <?= self::get('methodName', 'null') ?>,
        isClassFileLoad: false
    }

    /**
     * Нажатие на кнопку компоненты. Вызов окна с компонентами
     */
    varComp.compBtnClick = function(){
        varComp.tree.comp.selectItem(varComp.compId);
        $dialog.open('compDlg');
        // func. compBtnClick
    }
    
    /**
     * Выбор компонента. Закрытие окна
     */
    varComp.tree.compDbClick = function(){
        var itemId = varComp.tree.comp.getSelectedItemId();
        if ( varComp.compid != itemId){
            $('#contPath').html('');
            varComp.contId = null;
        }
        varComp.compId = itemId;
        $('#compPath').html(utils.getTreeUrl(varComp.tree.comp, itemId));
        $('#classType').val('core');
        $dialog.close('compDlg');
        
        var varStorage = $('#varStorage').val();
        HAjax.compLoadCompData({data:{
            compid: itemId,
            varStorage: varStorage,
            classType: 'core'
        }});
        // func. compDbClick
    }

    varible.saveDataClick = function(){
        var stat = $('#dataTypeStat').attr('checked');
        if ( stat && !varComp.contId ){
            alert('Выбирите стат. контент');
            return;
        } // if(stat)
            
        var data = $('#contentForm').serialize();
        data += '&compId='+varComp.compId;
        data += '&contId='+varComp.contId;
        HAjax.saveData({data: data, methodType: 'POST'});
        // func. saveDataClick
    }
    
    varComp.saveDataSuccess = function(pData){
        if (pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        alert('Данные успешно сохранены');
        // func. saveDataSuccess
    }
    
    varComp.varStorageChange = function(){
        if ( varComp.isClassFileLoad ){
            HAjax.compLoadCompData({data:{
                compid: varComp.compId,
                varStorage: this.value,
                classType: 'core'
            }});
        }
        // func. varStorageChange
    }
    
    varComp.classNameChange = function(){
        var varStorage = $('#varStorage').val();
        var classType = $('#classType').val();
        var data = {
            compid: varComp.compId,
            varStorage: varStorage,
            className: this.value,
            classType: classType
        };
        HAjax.compLoadMethodData({data: data});
        // func. varComp.methodChange
    }
    
    varComp.compLoadCompDataSuccess = function(pData){
        if (pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        $('#methodName').find('option').remove().end();


        var $className = $('#className').find('option').remove().end()
                            .append("<option value=''>Выберите файла</option>");
        $.each(pData, function(key, value) {   
            $className
            .append($("<option></option>")
            .attr("value",value)
            .text(value)); 
        });
        varComp.isClassFileLoad = true;
        // func. compLoadCompDataSuccess
    }
    
    /**
     * Выбор контента. Вызов окна с контентом
     */
    varComp.contBtnClick = function(){
        if ( !varComp.compId ){
            alert('Выбирите компонент');
            return;
        }
        if ( varComp.contId ){
            varComp.tree.cont.selectItem(varComp.contId);
            $dialog.open('contDlg');
            return;
        }
        HAjax.loadContTree({query: {compid: varComp.compId}});
        // TODO: Поставить прогресс бар
    }
    
    varComp.loadContTree = function(pData){
        var contTree = varComp.tree.cont;
        contTree.deleteChildItems(0);
        contTree.loadJSONObject(pData);
        $dialog.open('contDlg');
    }
    
    varComp.compLoadMethodDataSuccess = function(pData){
        if (pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        var $methodName = $('#methodName').find('option').remove().end();
        $.each(pData, function(key, value) {   
            $methodName
            .append($("<option></option>")
            .attr("value",value)
            .text(value)); 
        });
    }
    
    varComp.tree.contDbClick = function(){
        var itemId = varComp.tree.cont.getSelectedItemId();
        varComp.contId = itemId;
        var text = utils.getTreeUrl(varComp.tree.cont, itemId);
        $('#contPath').html(text);
        $dialog.close('contDlg');
    }
    
    varComp.tree.data.comp = {
        tree: { id: 'compDlg', json: varComp.tree.data.comp }
        ,dbClick: varComp.tree.compDbClick
    }
    
    varComp.tree.data.cont = {
        tree:{ id: 'contDlg', json: varComp.tree.data.cont }
        ,dbClick: varComp.tree.contDbClick
    }

    var varibleData = {

    }

    var varibleMvc = (function () {

        function classTypeObjChange(pEvent) {
            // Очищаем текст возле папки
            $('#' + options.classNameObj).html('');
            // Ощичаем список старых методов по классу
            $('#' + options.methodNameObj).find('option').remove();
            var varStorage = $('#'+options.varStorageObj).val();
            // Подгружаем новое дерево
            HAjax.compLoadCompData({data:{
                compid: varComp.compId,
                varStorage: varStorage,
                classType: pEvent.currentTarget.value
            }});

            // func. classTypeObjChange
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
            blockItem.classFile = pItemId;
            // Отображаем на странице наш выбор
            var text = utils.getTreeUrl(pTree, pItemId);
            $('#' + options.classFileText).html(text);
            // Удаляем все ненужные ветки
            blockItem.tree.clss.deleteChildItems(0);
            // Загружаем методы класса
            HAjax.loadClassMethod({
                query:{
                    'class': pItemId,
                    blockitemid: blockItemData.blId,
                    classType:  $('#' + options.classTypeObj).val()
                }
            });

            // Закрываем диалоговое окно
            $.fancybox.close();
            // func. blockItem.classTreeDbClick
        }

        function loadClassListSuccess(pData) {
            if (pData['error']) {
                alert(pData['error']['msg']);
                return;
            }
            // func. loadClassTreeSuccess
        }

        function init(pOptions) {
            options = pOptions;

            $('#' + options.classTypeObj).change(classTypeObjChange);

            HAjax.create({
                loadClassTree: loadClassListSuccess
            });
            // func. init
        }

        return{
            init:init
        }
    })();
    
    $(document).ready(function(){
        $('#compBtn').click(varComp.compBtnClick);
        $('#className').change(varComp.classNameChange);
        $('#classType').val(varComp.classType);

        varibleMvc.init({
            classTypeObj:'classType',
            classNameObj:'className',
            methodNameObj:'methodName',
            varStorageObj: 'varStorage'
        });
        
        dhtmlxInit.init({
            'comp': varComp.tree.data.comp,
            'cont': varComp.tree.data.cont
        });
        varComp.tree.comp = dhtmlxInit.tree['comp'];
        varComp.tree.cont = dhtmlxInit.tree['cont'];
        
        HAjax.create({
            compLoadCompData: varComp.compLoadCompDataSuccess,
            saveData: varComp.saveDataSuccess,
            compLoadMethodData: varComp.compLoadMethodDataSuccess,
            loadContTree: varComp.loadContTree
        }); // HAjax.create
        
        $('#varStorage').unbind('change').change(varComp.varStorageChange);
        
        $('#compPath').html(utils.getTreeUrl(varComp.tree.comp, varComp.compId));
        $('#contPath').html(utils.getTreeUrl(varComp.tree.cont, varComp.contId));

        $('#contBtn').click(varComp.contBtnClick);
        
        if ( varComp.className){
            varComp.compLoadCompDataSuccess(varComp.className['list']);
            $('#className').val(varComp.className['val']);
        }
        
        if ( varComp.methodName){
            varComp.compLoadMethodDataSuccess(varComp.methodName['list']);
            $('#methodName').val(varComp.methodName['val']);
        }
        // func $.ready
    });
</script>