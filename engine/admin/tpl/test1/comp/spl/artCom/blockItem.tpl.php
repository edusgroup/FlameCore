<div class="dt">Var Content</div>
<div class="dd">
    <? self::selectIdName($this->get('varList'), 'name="varName" id="varName"'); ?>
</div>
<div class="dt">Шаблон списка комментариев</div>
<div class="dd">
    <a id="tplListBtn" href="#tplTreeBox">
        <img class="folderBtn" src="<?= self::res('images/folder_16.png') ?>" alt="Шаблон компонента"/>
        <span class="tplTreeText"></span>
        <input type="hidden" name="tplListItemId"/>
    </a>
</div>
<div class="dt">Шаблон коментария</div>
<div class="dd">
    <a id="tplComBtn" href="#tplTreeBox">
        <img class="folderBtn" src="<?= self::res('images/folder_16.png') ?>" alt="Шаблон компонента"/>
        <span class="tplTreeText"></span>
        <input type="hidden" name="tplComItemId"/>
    </a>
</div>

<div id="tplTreeBox" style="display: none" class="treePanel"></div>

<script>
    var artCompBlockItemData = {
        tplTree: <?= self::get('tplTree') ?>,
        comData: <?= self::get('artComData', 'null'); ?>
    };
    
    var artCompBlockItemMvc = (function(){
        var options = {};
        var tree = {};
        // Какой tpl выберается при открытом окне tplTree
        // list - список комментариев
        // com - шаблон комментария
        var tplType = '';
        
        function tplTreeDbClick(pItemId, pTree){
            var inputHidden = tplType == 'list' ? options.tplListItemId : options.tplComItemId;
            // Выставляем значение скрытого input-а
            $(inputHidden).val(pItemId);
            
            // Отображаем на странице наш выбор
            var text = utils.getTreeUrl(pTree, pItemId);
            var textPath = tplType == 'list' ? options.tplListPathText : options.tplComPathText;
            $(textPath).html(text);
                        
            $.fancybox.close();
            // func. tplTreeDbClick
        }
        
        function init(pOptions){
            
            options = pOptions;
            
            $(options.tplListBtn).fancybox({
                beforeShow: function(){ tplType = 'list';}
            });
            $(options.tplComBtn).fancybox({
                beforeShow: function(){ tplType = 'com';}
            });
            
            // Создаём деревья
            dhtmlxInit.init({
                'tpl': {
                    tree: {id: 'tplTreeBox', json: artCompBlockItemData.tplTree },
                    dbClick: tplTreeDbClick
                }
            });
            tree.tpl = dhtmlxInit.tree['tpl'];
            
            // Есть ли сохранённые данные
            if ( artCompBlockItemData.comData ){
                
                var varName = artCompBlockItemData.comData['actionId'];
                $(options.varName).val(varName);
                
                // ============= Шаблон списка комментариев
                var tplListFile = artCompBlockItemData.comData['tplListFile'];
                // Устанавливаем скрытой переменой загруженное значение
                $(options.tplListItemId).val(tplListFile);
                // Отображаем на странице наш выбор
                var text = utils.getTreeUrl(tree.tpl, tplListFile);
                $(options.tplListPathText).html(text);
                
                // ============= Шаблон комментария
                var tplComFile = artCompBlockItemData.comData['tplComFile'];
                // Устанавливаем скрытой переменой загруженное значение
                $(options.tplComItemId).val(tplComFile);
                // Отображаем на странице наш выбор
                var text = utils.getTreeUrl(tree.tpl, tplComFile);
                $(options.tplComPathText).html(text);
            } // if compData
         
            // func. init
        }

        return {
            init: init
        }  
    })();
    
    
    
    $(function($) {
        artCompBlockItemMvc.init({
            tplListBtn: '#tplListBtn',
            tplListPathText: '#tplListBtn span.tplTreeText:first',
            tplListItemId: '#tplListBtn input[name="tplListItemId"]',
            
            tplComBtn: '#tplComBtn',
            tplComPathText: '#tplComBtn span.tplTreeText:first',
            tplComItemId: '#tplComBtn input[name="tplComItemId"]',
            
            varName: '#varName'
        });
    });  
</script>