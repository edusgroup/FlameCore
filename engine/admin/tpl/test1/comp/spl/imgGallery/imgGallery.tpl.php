<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<script src="res/plugin/SWFUpload-2.2.0.1/swfupload.js" type="text/javascript"></script>
<script type="text/javascript" src="res/plugin/SWFUpload-2.2.0.1/js/handlers.js"></script>
<link href="res/plugin/SWFUpload-2.2.0.1/css/default.css" rel="stylesheet" type="text/css"/>

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<script src="res/plugin/fileManager/fileManager.js" type="text/javascript"></script>

<!--<script src="res/plugin/classes/html.js" type="text/javascript"></script>-->
<style>
    div.treePanel {
        height: 218px;
        background-color: #f5f5f5;
        border: 1px solid Silver;
        overflow: auto;
        width: 200px;
    }

    div.bothpanel > div {
        float: left;
        margin-right: 10px;
    }

    div .dt {
        font-weight: bold
    }

    div .dd {
        padding-left: 25px
    }
</style>

<!-- start panel right column -->
<div class="column">
    <!-- start panel right panel -->
    <div class="panel corners">
        <!-- start panel right title -->
        <div class="title corners_top">
            <div class="title_element">

                <a style="margin-left: 10px" href="" title="В начало">
                    <img src="<? self::res('images/home_16x16.png') ?>" alt="В начало" width="16" height="16"
                         alt="В начало"/>
                    В начало /
                </a>
                <span id="history">{Hisotry}</span>
            </div>
        </div>
        <!-- end title -->
        <!-- start panel right content -->
        <div class="content" id="mainpanel">

            <div class="boxmenu corners">
                <ul class="menu-items">

                    <li>
                        <a href="#back" id="backBtn" title="Назад">
                            <img src="<?= self::res('images/back_32.png') ?>" alt="Назад"/><span>Назад</span>
                        </a>
                    </li>

                    <li>
                        <a href="#save" id="saveBtn" title="Сохранить">
                            <img src="<?= self::res('images/save_32.png') ?>" alt="Сохранить"/><span>Сохранить</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=wareframe" id="" title="WF">
                            <img src="<?= self::res('images/wf_32.png') ?>" alt="WF"/><span>Wareframe</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=complist" title="Component List">
                            <img src="<?= self::res('images/refresh_32.png') ?>" alt="Component List"/><span>Component List</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="content">
                <? self::includeBlock('imgGallery'); ?>
            </div>
            <!-- end panel right content -->
        </div>
        <!-- end panel right content -->
    </div>
    <!-- end panel right panel -->
</div><!-- end panel right column -->

<div id="routeBox" style="width:250px;height:350px; display: none"></div>

<script type="text/javascript">
    var imgGalleryData = {
        contid: <?= self::get('contId') ?>
    };

    var contrName = imgGalleryData.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);

    var imgGalleryMvc = (function () {
        var options = {};
        var tree = {};

        // Клик по кноке Сохранить
        function saveBtnClick() {
            var $idSizeListForm = $('#idSizeListForm');
            var data = {};
            $.extend( data, imgGalleryManager.changeList);
            data['prevSize'] = $idSizeListForm.find('select[name="imgPrevSize"]').val();
            data['origSize'] = $idSizeListForm.find('select[name="imgOrigSize"]').val();
			
			// Бегаем по настройкам checkbox, и сохраняем только выставленные
			$idSizeListForm.find('input:checked').each(function(index, obj){
                data[obj.name] = obj.value;
            });

			// бегаем по файлам
            $('#'+options.folderBox + ' input.caption').each(function(index, obj){
                data[obj.name] = obj.value;
            });

            HAjax.saveData({
                data: data,
                methodType:'POST'
            });
            return false;
            // func. saveBtnClick
        }

        // callback сохранения данных
        function saveDataSuccess(pData) {
            if (pData['error']) {
                alert(pData['error']['msg']);
                return;
            }
            imgGalleryManager.saveDataSuccess(pData);
            alert('Данные успешно сохранены');
            // func. saveDataSuccess
        }

        function init(pOptions) {
            options = pOptions;
            // Кнопка Назад
            $('#'+options.backBtn).attr('href', utils.url({
                type:'manager',
                contr:'complist'
            }));
            // Кнопка Сохранить
            $('#'+options.saveBtn).click(saveBtnClick);
            HAjax.create({
                saveData:saveDataSuccess
            });
            // func. init
        }

        return {
            init: init
        }
        // func. imgGalleryMvc
    })();

    $(document).ready(function () {
        imgGalleryMvc.init({
            backBtn:'backBtn',
            saveBtn:'saveBtn',
            folderBox: 'folder'
        });
    }); // $(document).ready

</script>