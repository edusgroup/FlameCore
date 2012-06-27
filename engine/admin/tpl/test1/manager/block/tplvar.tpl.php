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
                        <a href="/?$t=manager&$c=wareframe" title="WF">
                            <img src="<?= self::res('images/wf_32.png') ?>" alt="WF" /><span>Wareframe</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=complist" title="Component List">
                            <img src="<?= self::res('images/refresh_32.png') ?>" alt="Component List" /><span>Component List</span>
                        </a>
                    </li>
                </ul>
            </div>
                <h6>Список переменных</h6>
                <div>
                    <?foreach(self::get('varList') as $name => $title ){?>
                        <p><?=$title?:$name?></p>
                        <input type="text" name="<?=$name?>"/>
                    <?}?>
                </div>

            <div class="content">

                <form id="mainForm">


                </form>

            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->


<script type="text/javascript">
    var contrName = 'blockItem';
    var callType = 'manager';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
   
    var tplVarData = {

    };
    
    var tplVarMvc = (function(){
        var options = {};

        // Клик по кноке Сохранить
        function saveBtnClick(){
            var data = $(options.mainForm).serialize();
            
            HAjax.dataSave({
                data: data,
                query:{

                }, 
                methodType: 'POST'
            });
            // func. saveBtnClick
        }
        
        // callback сохранения данных
        function dataSaveSuccess(pData){
            if ( pData['error'] ){
                alert(pData['error']['msg']);
                return;
            }
            
            alert('Данные успешно сохранены');
            // func. saveDataSuccess
        }

        function init(pOptions){
            options = pOptions;

            // Кнопка Назад
            $(options.backBtn).attr('href', utils.url({
                contr:'action'
            }));
            // Кнопка Сохранить
            $(options.saveBtn).click(saveBtnClick);

            HAjax.create({
                dataSave: dataSaveSuccess
            });
            // func. init
        }

        return {
            init: init
        }  
    })();
    
    $(document).ready(function(){
        
        tplVarMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn',
            mainForm: '#mainForm'
        });         
    }); // $(document).ready

</script>