<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

<style>
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
            <div>
                <p>Файл обработки:</p>
                <?=self::select(self::get('classList'), 'name="action"');?>

            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<script type="text/javascript">

    var formData = {
        contid: <?= self::get('contId') ?>
    }


    var callType = 'comp';
    utils.setType(callType);
    HAjax.setType(callType);
    var contrName = formData.contid;
    utils.setContr(contrName);
    HAjax.setContr(contrName);

    var formMvc = (function(){
        var options = {};
        var tree = {};

        // Клик по кноке Сохранить
        function saveBtnClick(){
            var data = 'action='+$('select[name="'+options.actionSelect+'"]').val();
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

            // func. init
        }
        return {
            init: init
        }
    })();


    $(document).ready(function(){
        formMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn',
            actionSelect: 'action'
        });
    }); // $(document).ready

</script>