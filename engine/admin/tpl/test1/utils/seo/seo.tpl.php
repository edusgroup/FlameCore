<link   href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<!--<script src="res/plugin/classes/html.js" type="text/javascript"></script>-->
<style>
    div.left{float: left; margin-right: 10px;}
    br.clearBoth { clear:both;}
    div.buttonPanel{height: 30px}
    div.treePanel{height:218px;background-color:#f5f5f5;border :1px solid Silver; overflow:auto; width: 200px;}
    #secondPanel {display: none; width: 400px}
    div.dt{font-weight: bold}
    div.dd{ padding-left: 25px}
    div.dd2{ padding-left: 50px}

    div.submitDiv{margin-top: 20px}
	
	textarea.textareabox{
		width: 300px;
		height: 50px;
	}
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


            <div>
                @TODO CREATE SHOW MSGBOX<br/>
                <h6>Seo Data</h6>

                <div class="bothPanel">
                    <div class="treePanel left" id="acTreeBox"></div>
                    <div class="left">
                        <form id="paramBox"></form>
                    </div>
                </div>


            </div><!-- end panel right content -->

        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<script type="text/javascript">

    var seoData = {
          acTree: <?= self::get('acTree') ?>
    }

    var callType = 'utils';
    utils.setType(callType);
    HAjax.setType(callType);
    var contrName = 'seo';
    utils.setContr(contrName);
    HAjax.setContr(contrName);

    var seoMvc = (function(){
        var options = {};
        var tree = {};

        // Клик по кноке Сохранить
        function saveBtnClick(){
            var data = $('#'+options.paramBox).serialize();
            data += '&itemid=' + tree.ac.getSelectedItemId();
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

        function acTreeClick(pItemId, pTree){
            var url = utils.url({
                method: 'loadParam',
                query: {itemid: pItemId}
            });
            $('#'+options.paramBox).load(url);
            // func. acTreeClick
        }

        function compListChange(pEvent){
            var compId =  pEvent.target.value;
            if ( compId == -1 ){
                $('#paramBox select[name=method]>option').remove()
                return;
            }
            var data = {
                biCompId: compId
            }
            HAjax.loadClassMethod({data: data});
            // func. compListChange
        }

        function loadClassMethodSuccess(pData){
            if ( pData['error'] ){
                alert(pData['error']['msg']);
                return;
            }
            // Ощичаем старый набор методом
            var $select = $('#paramBox select[name=method]');
            var $methodName = $select.find('option').remove().end();
            // Добавляем новый набор методов
            $.each(pData, function(key, value) {
                $select
                    .append($("<option></option>")
                    .attr("value",value)
                    .text(value));
            });
            // func. loadClassMethodSuccess
        }

        function init(pOptions){
            options = pOptions;

            // Кнопка Назад
            $('#'+options.backBtn).attr('href', utils.url({
                type: 'manager',
                contr: 'complist'
            }));
            // Кнопка Сохранить
            $('#'+options.saveBtn).click(saveBtnClick);

            HAjax.create({
                saveData: saveDataSuccess,
                loadClassMethod: loadClassMethodSuccess
            });

            // Создаём деревья
            dhtmlxInit.init({
                'acTree': {
                    tree: {id: options.acTreeBox, json: seoData.acTree },
                    clickEnd: acTreeClick
                }
            });
            tree.ac  = dhtmlxInit.tree['acTree'];
            // func. init
        }
        return {
            init: init,
            compListChange: compListChange
        }
    })();


    $(document).ready(function(){

        seoMvc.init({
            backBtn: 'backBtn',
            saveBtn: 'saveBtn',
            paramBox: 'paramBox',
            acTreeBox: 'acTreeBox'
        });
    }); // $(document).ready

</script>