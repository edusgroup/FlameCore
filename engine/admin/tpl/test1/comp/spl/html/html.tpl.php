<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

<style>
    div .dt {
        font-weight: bold
    }

    div .dd {
        padding-left: 25px
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
            <div>
                <form id="mainForm">
                    <div class="dt">Название:</div>
                    <div class="dd"><?=self::text('name="caption"', self::get('caption'))?></div>

                    <div class="dt">Только текст?</div>
                    <div class="dd"><?=self::checkbox('name="isOnlyText"', self::get('isOnlyText'))?></div>

                    <div class="dt">Введите HTML код:</div>
                    <div class="dd"><textarea style="width: 600px; height: 200px;" name="htmlCode"><?=self::get('htmlCode')?></textarea></div>
                </form>
            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<script type="text/javascript">

    var htmlData = {
        contid: <?= self::get('contId') ?>
    }


    var callType = 'comp';
    utils.setType(callType);
    HAjax.setType(callType);
    var contrName = htmlData.contid;
    utils.setContr(contrName);
    HAjax.setContr(contrName);

    var htmlMvc = (function(){
        var options = {};
        var tree = {};

        // Клик по кноке Сохранить
        function saveBtnClick(){
            var data = $('#'+options.mainForm).serialize();
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
        htmlMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn',
            mainForm: 'mainForm'
        });
    }); // $(document).ready

</script>