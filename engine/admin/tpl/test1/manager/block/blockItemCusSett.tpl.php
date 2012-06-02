<!-- DXHTML COMPONENT -->
<link   href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>
<!-- END DXHTML COMPONENT -->

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<style>
    div .dt{font-weight: bold}
    div .dd{ padding-left: 25px}
    div.treePanel{height:218px;background-color:#f5f5f5;border :1px solid Silver; overflow:auto; width: 200px;}
</style>

<!-- start panel right column -->
<div class="column" >
    <!-- start panel right panel -->
    <div class="panel corners">
        <!-- start panel right content -->
        <div class="content" id="mainpanel">

            <div class="boxmenu corners">
                <ul class="menu-items">

                    <li>
                        <a href="#back" id="backBtn" title="Назад">
                            <img src="<?= self::res('images/back_32.png') ?>" alt="Назад" /><span>Назад</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="#save" id="saveBtn" title="Сохранить">
                            <img src="<?= self::res('images/save_32.png') ?>" alt="Сохранить" /><span>Сохранить</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=wareframe" id="" title="WF">
                            <img src="<?= self::res('images/wf_32.png') ?>" alt="WF" /><span>Wareframe</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=complist" id="" title="Component List">
                            <img src="<?= self::res('images/refresh_32.png') ?>" alt="Component List" /><span>Component List</span>
                        </a>
                    </li>
                </ul>
            </div>


            <div class="content">

                <form id="mainForm">

                    <div class="dt">Контент</div>
                    <div class="dd">
                        <a href="#contBox" id="contTreeBtn">
                            <img src="<?= self::res('images/folder_16.png') ?>"/>
                        </a>
                        <span id="contTreePath"></span>
                        <input type="hidden" name="contid" />
                    </div>
                    
                    <div id="compContBox"></div>
                </form>

            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<div id="contBox" style="display: none" class="treePanel"></div>

<script type="text/javascript">
    var contrName = 'blockItem';
    var callType = 'manager';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
   
    var biCustSettData = {
        contTree: <?= self::get('contTree') ?>,
        blockItemId: <?= self::get('blockItemId') ?>,
        acId: '<?= self::get('acId') ?>',
        custContId: <?= self::get('custContId', 'null') ?>
    };
    
    var biCustSettMvc = (function(){
        var options = {};
        var tree = {};
        
        // Клик по кноке Сохранить
        function saveBtnClick(){
            var data = $(options.mainForm).serialize();
            
            HAjax.custSettSave({
                data: data,
                query:{
                    blockitemid: biCustSettData.blockItemId
                }, 
                methodType: 'POST'
            });
            // func. saveBtnClick
        }
        
        // callback сохранения данных
        function custSettSaveSuccess(pData){
            if ( pData['error'] ){
                alert(pData['error']['msg']);
                return;
            }
            
            alert('Данные успешно сохранены');
            // func. saveDataSuccess
        }
        
        function loadCustCompBox(pItemId){
            var url = utils.url({
                type: 'comp',
                contr: pItemId,
                method: 'blockItemShow',
                query:{
                    acid: biCustSettData.acId,
                    blockitemid: biCustSettData.blockItemId
                }
            });
            $(options.compContBox).load(url);
            // func. loadCustCompBox
        }
        
        /**
         * Двойной клик по объект дерева контента
         * Выбор ветки и закрытие окна
         */
        function contTreeDbClick(pItemId, pTree){
            // Запоминаем наш выбор
            $(options.inputContId).val(pItemId);
            // Отображаем на странице наш выбор
            var text = utils.getTreeUrl(pTree, pItemId);
            $(options.contTreePath).html(text);
            $.fancybox.close();
            loadCustCompBox(pItemId);
            // func. contTreeDbClick
        }
     
        function init(pOptions){
            options = pOptions;
            
            // Кнопка Назад
            $(options.backBtn).attr('href', utils.url({
                contr:'blockItem',
                query: {
                    id: biCustSettData.blockItemId,
                    acid: biCustSettData.acId
                }
            }));
            // Кнопка Сохранить
            $(options.saveBtn).click(saveBtnClick);
            $(options.contTreeBtn).fancybox();
            
            HAjax.create({
                custSettSave: custSettSaveSuccess
            });
            
            // Создаём деревья
            dhtmlxInit.init({
                'contTree': {
                    tree: {id:'contBox', json: biCustSettData.contTree },
                    dbClick: contTreeDbClick
                }
            });
            tree.cont  = dhtmlxInit.tree['contTree'];

            // Клик по кнопке выбора групп
            $(options.contTreeBtn).fancybox();
            
            // Есть ли сохранённые данные
            if ( biCustSettData.custContId ){
                $(options.inputContId).val(biCustSettData.custContId);
                var text = utils.getTreeUrl(tree.cont, biCustSettData.custContId);
                $(options.contTreePath).html(text);
                loadCustCompBox(biCustSettData.custContId);
            } // if custContId

            // func. init
        }

        return {
            init: init
        }  
    })();
    
    $(document).ready(function(){
        
        biCustSettMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn',
            mainForm: '#mainForm',
            contTreeBtn: '#contTreeBtn',
            contTreePath: '#contTreePath',
            compContBox: '#compContBox',
            inputContId: '#mainForm input[name="contid"]'
        });         
    }); // $(document).ready

</script>