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

                </ul>
            </div>


            <div class="content">




            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<div id="contBox" style="display: none" class="treePanel"></div>

<script type="text/javascript">
    var contrName = 'event';
    var callType = 'manager';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
   
    var eventData = {
        siteName: '<?=self::get('$siteName')?>'
    };
    
    var eventMvc = (function(){
        var options = {};
        var tree = {};
        
        // Клик по кноке Сохранить
        function saveBtnClick(){
            HAjax.saveData({
                query:{
                    siteName: eventData.siteName
                },
                methodType: 'POST'
            });
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

     
        function init(pOptions){
            options = pOptions;
            
            // Кнопка Назад
            $(options.backBtn).attr('href', utils.url({
                contr: 'action'
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
        
        eventMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn'
        });
    }); // $(document).ready

</script>