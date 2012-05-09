<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<style>
    div .dt{font-weight: bold}
    div .dd{ padding-left: 25px; }
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
                <form id="mainForm">
                    
                    <div class="dt">
                        Тип комментариев
                    </div>
                    <div class="dd">
                        <select id="artCompType" name="type">
                            <option value="article">Статьи</option>
                            <option value="imggallery">Галлерия изображений</option>
                        </select>
                    </div>

                </form>
            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<script type="text/javascript">
  
    var artComData = {
        contid: <?= self::get('contId') ?>,
        data: <?=self::get('data', null)?>
    }
    
    var contrName = artComData.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
   
    
    
    var artComMvc = (function(){
        var options = {};
        //var gridPageCurrent = 1;
        
        // Клик по кноке Сохранить
        function saveBtnClick(){
            var data = $(options.mainForm).serialize();
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
        

        
        function init(pOptions){
            options = pOptions;
            
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
            
            if ( artComData.data ){
                $(options.selectType).val(artComData.data.type);
            }
          
            // func. init
        }

        
        return {
            init: init
        }  
    })();
    
    $(document).ready(function(){
        
        artComMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn',
            mainForm: '#mainForm',
            selectType: '#artCompType'
        });         
    }); // $(document).ready

</script>