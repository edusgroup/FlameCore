<link href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

<style>
    div .dt {
        font-weight: bold
    }

    div .dd {
        padding-left: 25px
    }
	
	div.treePanel {
        height: 218px;
        background-color: #f5f5f5;
        border: 1px solid Silver;
        overflow: auto;
        width: 200px;
    }
</style>

<!-- start panel right column -->
<div class="column" >
    <!-- start panel right panel -->
    <div class="panel corners">
        <!-- start panel right title -->
        <div class="title corners_top">
            <div class="title_element">
                <span><a href="/?$t=manager&$c=site"><?=self::get('$siteName')?></a></span>
            </div>
        </div><!-- end title -->
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
                        <a href="/?$t=manager&$c=wareframe" title="WF">
                            <img src="<?= self::res('images/wf_32.png') ?>" alt="WF" /><span>Wareframe</span>
                        </a>
                    </li>
                    <li>
                        <a href="/?$t=manager&$c=complist" title="Component List">
                            <img src="<?= self::res('images/refresh_32.png') ?>" alt="Component List" /><span>Component List</span>
                        </a>
                    </li>

                    <li>
                        <a href="#save" id="saveBtn" title="Сохранить">
                            <img src="<?= self::res('images/save_32.png') ?>" alt="Сохранить" /><span>Сохранить</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="content">
                <div id="fileTreeBox" class="treePanel"></div>
            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<script type="text/javascript">

    var freeScriptData = {
        fileTreeJson: <?= self::get('fileTree', '{}') ?>,
		contid: <?= self::get('contId') ?>,
		saveData: <?= self::get('saveData', 'null') ?>,
    }; // oiLasterData

    var contrName = freeScriptData.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);

    var freeScriptMvc = (function(){
        var options = {};
        var fileTree;

        // Клик по кноке Сохранить
        function saveBtnClick(){
            var data = 'file='+fileTree.getSelectedItemId();
            HAjax.saveData({method: 'saveData', data: data, methodType: 'POST'});
            // func. saveBtnClick
        }

        // callback сохранения данных
        function saveDataSuccess(pData){
            if ( pData['error'] ){
                alert(pData['error']['msg']);
                return;
            }

            alert('Данные успешно сохранены');
            // func. saveDataSuccess
        }
		
		function initTree() {
            dhtmlxInit.init({
                'fileTree':{
                    tree:{
                        id:'fileTreeBox', json:freeScriptData.fileTreeJson
                    }
                }
            }); // init

            fileTree = dhtmlxInit.tree['fileTree'];
            // func. initTrees
        }

        function init(pOptions){
            options = pOptions;
			
			initTree();

            // Кнопка Назад
            $(options.backBtn).attr('href', utils.url({
                type: 'manager',
                contr: 'complist'
            }));
            // Кнопка Сохранить
            $(options.saveBtn).click(saveBtnClick);

            HAjax.create({
                saveData: saveDataSuccess
            });
			if ( freeScriptData.saveData ){ 
				fileTree.selectItem('/'+freeScriptData.saveData['file'], false);
			}

            // func. init
        }
        return {
            init: init
        }
    })();


    $(document).ready(function(){
        freeScriptMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn'
        });
    }); // $(document).ready

</script>