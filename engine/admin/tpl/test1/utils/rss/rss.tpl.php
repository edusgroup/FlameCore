<link   href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<!--<script src="res/plugin/classes/html.js" type="text/javascript"></script>-->
<style>
    div.treePanel{
        height:218px;
        background-color:#f5f5f5;
        border :1px solid Silver;
        overflow:auto;
        width: 200px;
    }
    div.bothpanel > div{
        float: left;
        margin-right: 10px;
    }
    div .dt{font-weight: bold}
    div .dd{ padding-left: 25px}
</style>

<!-- start panel right column -->
<div class="column" >
    <!-- start panel right panel -->
    <div class="panel corners">
        <!-- start panel right title -->
        <div class="title corners_top">
            <div class="title_element">

                <a style="margin-left: 10px" href="" title="В начало">
                    <img src="<? self::res('images/home_16x16.png') ?>" alt="В начало" width="16" height="16" alt="В начало"/>
                    В начало /
                </a>
                <span id="history">{Hisotry}</span>
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
                        <a href="/?$t=manager&$c=wareframe" id="" title="WF">
                            <img src="<?= self::res('images/wf_32.png') ?>" alt="WF" /><span>Wareframe</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=complist" id="" title="Component List">
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

                <div class="bothpanel">
                    <div id="contDiv" class="treePanel"></div>
                    <div id="propBox">
                        <div class="dt">Заголовок</div>
                        <div class="dd"><?=self::text('name="title"', self::get('title'))?></div>

                        <div class="dt">Описание</div>
                        <div class="dd"><?=self::text('name="descr"', self::get('descr'))?></div>
                    </div>
                </div>

            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<script type="text/javascript">
    var contrName = 'rss';
    var callType = 'utils';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
   
    var rssData = {
        contTree: <?= self::get('contTree') ?>,
        rss: <?= self::get('rss') ?:'[]' ?>
    };
    
    var rssMvc = (function(){
        var options = {};
        var tree = {};

        // Клик по кноке Сохранить
        // Клик по кноке Сохранить
        function saveBtnClick(){
            var sel = tree.compcont.getAllCheckedBranches();
            var propData = '';
            $('#' + options.propBox + ' :text').each(function(pNum, pItem){
                propData += '&'+pItem.name + '='+pItem.value;
            });

            HAjax.saveData({
                data: 'sel=' + sel + propData,
                methodType: 'POST'
            });
            // func. saveBtnClick
        }

        function initTrees(){
            dhtmlxInit.init({
                'contTree':{
                    tree: {
                        id:'contDiv'
                        ,json: rssData.contTree
                    } // tree
                    ,checkbox: true 
                } // contTree
            }); // init
            tree.compcont = dhtmlxInit.tree['contTree'];
            tree.compcont.enableThreeStateCheckboxes(0);
            
            for( var i in rssData.rss ){
                var id = rssData.rss[i];
                tree.compcont.setCheck(id, 1);
            } // for
            // func. initTrees
        }
        
        // callback сохранения данных
        function saveDataSuccess(pData){
            if ( pData['error'] ){
                alert(pData['error']['msg']);
                return;
            }
            treeCheckDel = [];
            treeCheckSel = [];
            alert('Данные успешно сохранены');
            // func. saveDataSuccess
        }
        
        function init(pOptions){
            options = pOptions;
            
            initTrees();
            
            // Кнопка Назад
            $(options.backBtn).attr('href', utils.url({
                contr:'tree'
            }));
            // Кнопка Сохранить
            $(options.saveBtn).click(saveBtnClick);
            
            HAjax.create({
                saveData: saveDataSuccess
            });
            // func. init
        }
        
        return {
            init: init
        }  
    })();
    
    $(document).ready(function(){
        
        rssMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn',
            treeDiv: '#treeDiv',
            propBox: 'propBox'
        });         
    }); // $(document).ready

</script>