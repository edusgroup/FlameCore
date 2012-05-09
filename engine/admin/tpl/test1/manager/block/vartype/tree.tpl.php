
<div class="dt">Компонент:</div>
<div class="dd">
    <img src="<?= self::res('images/folder_16.png') ?>" id="compBtn"/>
    <span id="compPath"></span>
</div>
<div class="dt">
    Тип данных:
</div>
<div class="dd">
    TreeId:
    <img src="<?= self::res('images/folder_16.png') ?>" id="contBt"/>
    <span id="contPath"></span>
</div>

<div id="contDlg" style="display: none"></div>
<div id="compDlg" style="display: none"></div>
<script type="text/javascript">
    var varType = {
        tree:{
            data:{
                comp: <?= self::get('compTree') ?>
                ,cont: <?= self::get('contTree', 'null') ?>
            }
            
        },
        compId: <?= self::get('compid', 'null') ?>,
        contId: <?= self::get('contid', 'null') ?>
    }
 
    /**
     * Нажатие на кнопку компоненты. Вызов окна с компонентами
     */
    varType.compBtnClick = function(){
        varType.tree.comp.selectItem(varType.compId);
        $dialog.open('compDlg');
    }
    
    /**
     * Выбор компонента. Закрытие окна
     */
    varType.tree.compDbClick = function(pTreeId, pTree){
        //var itemId = varType.tree.comp.getSelectedItemId();
        if ( varType.compid != pTreeId){
            $('#contPath').html('');
            varType.contId = null;
        }
        varType.compId = pTreeId;
        $('#compPath').html(utils.getTreeUrl(pTree, pTreeId));
        $dialog.close('compDlg');
    }
    
    /**
     * Выбор контента. Вызов окна с контентом
     */
    varType.contBtnClick = function(){
        if ( !varType.compId ){
            alert('Выбирите компонент');
            return;
        }
        if ( varType.contId ){
            varType.tree.cont.selectItem(varType.contId);
            $dialog.open('contDlg');
            return;
        }
        HAjax.loadContTree({query: {compid: varType.compId}});
        // TODO: Поставить прогресс бар
    }
    
    varType.loadContTree = function(pData){
        var contTree = varType.tree.cont;
        contTree.deleteChildItems(0);
        contTree.loadJSONObject(pData);
        $dialog.open('contDlg');
    }
    
    varType.tree.contDbClick = function(){
        var itemId = varType.tree.cont.getSelectedItemId();
        varType.contId = itemId;
        var text = utils.getTreeUrl(varType.tree.cont, itemId);
        $('#contPath').html(text);
        $dialog.close('contDlg');
    }
    
    varType.tree.data.comp = {
        tree: { id: 'compDlg', json: varType.tree.data.comp }
        ,dbClick: varType.tree.compDbClick
    }
    
    varType.tree.data.cont = {
        tree:{ id: 'contDlg', json: varType.tree.data.cont }
        ,dbClick: varType.tree.contDbClick
    }
    
    varType.saveData = function(pData){
        if (pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        alert('Данные успешно сохранены');
    }
    
    $(document).ready(function(){
        $('#compBtn').click(varType.compBtnClick);
        $('#contBt').click(varType.contBtnClick);
        
        dhtmlxInit.init({
            'comp': varType.tree.data.comp,
            'cont': varType.tree.data.cont});
        varType.tree.comp = dhtmlxInit.tree['comp'];
        varType.tree.cont = dhtmlxInit.tree['cont'];
    
        HAjax.create({
            loadContTree: varType.loadContTree,
            saveData: varType.saveData
        });
        
        varible.saveDataClick = function(){
            var data = $('#contentForm').serialize();
            data += '&compId='+varType.compId;
            data += '&contId='+varType.contId;
            HAjax.saveData({data: data, methodType: 'POST'});
            // func. varible.saveDataClick
        }
        
        $('#compPath').html(utils.getTreeUrl(varType.tree.comp, varType.compId));
        $('#contPath').html(utils.getTreeUrl(varType.tree.cont, varType.contId));
        
        //if ( varible.varCount > 1 ){
        //   $('#contDivSel').hide();
        // }
    });

</script>