<link   href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen"/>

<style type="text/css">
    .bold {font-weight: bold}
    .vmiddle{vertical-align: middle; height: 40px}
    .vmiddle img{vertical-align: middle}
    .treeBlock{vertical-align:top; width:200px; height:218px;background-color:#f5f5f5;border :1px solid Silver;; overflow:auto;}
    img.img_button{cursor: pointer}

    div .items .dt{font-weight: bold}
    div .items .dd{ padding-left: 25px}
    div .items .dd2x{ padding-left: 50px}
</style>
<!-- start panel right column -->
<div class="column" >
    <!-- start panel right panel -->
    <div class="panel corners">
        <!-- start panel right title -->
        <div class="title corners_top">
            <div class="title_element">

                <a style="margin-left: 10px" href="" title="В начало">
                    <img src="<? self::res('images/home_16x16.png') ?>" alt="В начало" width="16" height="16" title="В начало"/>
                    В начало /
                </a>
                <span id="history">{Hisotry}</span>
            </div>
        </div><!-- end title -->
        <!-- start panel right content -->
        <div class="content">


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

                <div class="items">
                    <form id="mainForm">
                        <div class="dt">Наследовать от родителя</div>
                        <div class="dd">
                            <label><?= self::checkbox('name="parentLoad" value="1"', self::get('parentLoad') == 1); ?></label>
                        </div>
                        <!--<div class="dt">Категория</div>
                        <div class="dd">
                            <? self::select(self::get('categoryList'), 'name="category"') ?>
                        </div>-->
                        
                        <div class="dt">Шаблон админки</div>
                        <!--<div class="dd">
                            <label><?= self::radio('name="tplType" value="default"', self::get('tplType') == 'default'); ?>
                            По умолчанию</label>
                        </div>
                        <div class="dd">
                            <label><?= self::radio('name="tplType" value="user"', self::get('tplType') == 'user'); ?>
                            Пользовательский</label>
                        </div>
                        <div class="dd2x">
                            <? self::selectIdName(self::get('tplUserList'), 'name="tplUser"') ?>
                        </div>
                        <div class="dd">
                            <label><?= self::radio('name="tplType" value="ext"', self::get('tplType') == 'ext'); ?>
                            Встроенный</label>
                        </div>
                        <div class="dd2x">
                            <? self::selectIdName(self::get('tplExtList'), 'name="tplExt"') ?>
                        </div>
                        <div class="dd">
                            <label><?= self::radio('name="tplType" value="builder"', self::get('tplType') == 'builder'); ?> 
                            FormBuilder</label>
                        </div>-->

                        <div class="dd">
                            <a id="tplBtn" href="#tplTreeDlg" class="btn">
                                <img src="<?= self::res('images/folder_16.png') ?>" alt="Класс компонента"/>
                                <span id="tplFileText"></span>
                            </a>
                        </div>

                        <div class="dt">Функциональный класс</div>
                        <!--<div class="dd">
                            <label><?= self::radio('name="classType" value="default"', self::get('classType') == 'default'); ?> 
                            По умолчанию</label>
                        </div>
                        <div class="dd">
                            <label><?= self::radio('name="classType" value="user"', self::get('classType') == 'user'); ?>
                            Пользовательский</label>
                        </div>
                        <div class="dd2x">
                            <? self::selectIdName(self::get('classUserList'), 'name="classUser"') ?>
                        </div>
                        <div class="dd">
                            <label><?= self::radio('name="classType" value="ext"', self::get('classType') == 'ext'); ?>
                            Встроенный</label>
                        </div>
                        <div class="dd2x">
                            <? self::selectIdName(self::get('classExtList'), 'name="classExt"') ?>
                        </div>-->

                        <div class="dd">
                            <a id="classBtn" href="#classTreeDlg" class="btn">
                                <img src="<?= self::res('images/folder_16.png') ?>" alt="Класс компонента"/>
                                <span id="classFileText"></span>
                            </a>
                        </div>
                        
                        
                        <div><a href="#" id="extendsSettings" style="display: none">Расширенные настройки &raquo;</a></div>
                    </form>
                    
                </div>
            </div><!-- end panel right content -->

        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<div id="classTreeDlg" style="width:250px;height:350px; display: none"></div>
<div id="tplTreeDlg" style="width:250px;height:350px; display: none"></div>

<script type="text/javascript">
    var contrName = 'compprop';
    var callType = 'manager';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
    
    var compPropData = {
        // ContId
        contid: <?= self::get('contId') ?>,
        // Есть ли расширенные настройки
        extSettings: <?= self::get('extSettings') ?>,
        // Json данные для построение дерева классов
        classTreeJson: <?= self::get('classTree') ?>,
        // Json данные для построение дерева шаблонов
        tplTreeJson: <?= self::get('tplTree') ?>,
        // Ранее сохранёные данные (если они есть )
        loadData: <?=self::get('loadData') ?>,
        // Свойства компонента, который мы настраиваем
        compProp: <?=self::get('compProp') ?>
    } // var compPropData

    var compPropMvc = (function(){
        var options = {};
        var classFile = '';
        // Дерево классов
        var classTree;
        // Дерево шаблонов
        var tplTree;
        // Выбранное значение в дереве классов
        var classTreeSelectId;
        // Выбранное значение в дереве шаблонов
        var tplTreeSelectId;

        function saveBtnClick(){
            var data = $('#'+options.mainForm).serialize();
            data += '&classFile='+classTreeSelectId;
            data += '&tplFile=' + tplTreeSelectId;
            HAjax.saveData({
                data: data,
                methodType: 'POST',
                query: {
                    contid: compPropData.contid
                }
            });
            return false;
            // func. saveBtnClick
        }

        /**
         * Инициализация деревьев
         */
        function initTreeCreate(){
            dhtmlxInit.init({
                'class': {
                    tree:{ json: compPropData.classTreeJson, id:'classTreeDlg' },
                    dbClick: classBrunchClick
                },
                'tpl': {
                    tree:{ json: compPropData.tplTreeJson, id:'tplTreeDlg' },
                    dbClick: tplBrunchClick
                }
            });

            classTree = dhtmlxInit.tree['class'];
            tplTree = dhtmlxInit.tree['tpl'];
            // func. initTreeCreate
        }

        function selectCategoryChange(pEvent){
            var category = pEvent.currentTarget.value;
            HAjax.loadCategoryData({data:{
                contid: compPropData.contid,
                category: category
            }});
            // Ощичаем старый набор методом
            $(options.classExtSelObj).find('option').remove().end();
            $(options.classUserSelObj).find('option').remove().end();
            $(options.tplExtSelObj).find('option').remove().end();
            $(options.tplUserSelObj).find('option').remove().end();
            // func. selectCategoryChange
        }

        function cbLoadCategoryDataSuccess(pData){
            if ( pData['error'] ){
                alert(pData['error']['msg']);
                return;
            }
            var $select = $(options.classExtSelObj);
            $.each(pData['classExtList'], function(key, value) {
                $select.append($("<option></option>").attr("value",value).text(value));
            });
            var $select = $(options.classUserSelObj);
            $.each(pData['classUserList'], function(key, value) {
                $select.append($("<option></option>").attr("value",value).text(value));
            });
            var $select = $(options.tplExtSelObj);
            $.each(pData['tplExtFile'], function(key, value) {
                $select.append($("<option></option>").attr("value",value).text(value));
            });
            var $select = $(options.tplUserSelObj);
            $.each(pData['tplUserList'], function(key, value) {
                $select.append($("<option></option>").attr("value",value).text(value));
            });

            // func. cbLoadCategoryDataSuccess
        }

        function cbSaveData(pData){
            if ( pData['error'] ){
                alert(pData['error']['msg']);
                return;
            }
            var url = utils.url({
                type: 'comp',
                contr: compPropData.contid,
                method: 'compProp'
            });
            $('#'+options.extSettBtn).toggle(pData['extSettings']==1).attr('href', url);

            alert('Данные сохранены');
            // func. cbSaveData
        }

        function classBrunchClick(pBrunchId, pTree){
            // Получаем тип ветки: 1-папка, 0-файл
            var type = pTree.getUserData(pBrunchId, 'type');
            // Выбрать можно только файл
            if (type != 1) {
                return false;
            }
            var text = utils.getTreeUrl(pTree, pBrunchId);
            classTreeSelectId = pBrunchId;
            $(options.classFileText).html(text);
            $.fancybox.close();
            // class classBrunchClick
        }

        function tplBrunchClick(pBrunchId, pTree){
            // Получаем тип ветки: 1-папка, 0-файл
            var type = pTree.getUserData(pBrunchId, 'type');
            // Выбрать можно только файл
            if (type != 1) {
                return false;
            }
            var text = utils.getTreeUrl(pTree, pBrunchId);
            tplTreeSelectId = pBrunchId;
            $(options.tplFileText).html(text);
            $.fancybox.close();
            // class tplBrunchClick
        }

        function beforeClassDlgShow(){
            classTree.selectItem(classTreeSelectId);
            // func. beforeTplDlgShow
        }

        function beforeTplDlgShow(){
            tplTree.selectItem(tplTreeSelectId);
            // func. beforeTplDlgShow
        }

        function initLoadData(){
            // Есть ли ранее сохрённые данные по объекту
            if ( compPropData.loadData ){
                // Значение ранее сохранённого файла классов для админки
                var classBrId = compPropData.loadData['classFile'];
                var classText = utils.getTreeUrl(classTree, classBrId);
                // Значение ранее сохранённого файла классов для админки
                var tplBrId = compPropData.loadData['tplFile'];
                var tplText = utils.getTreeUrl(tplTree, tplBrId);
            }else{
                // Выставляем значения по умолчанию
                var compName = compPropData.compProp['classname'];
                var classBrId = '/base/'+compName+'.php';
                var classText = utils.getTreeUrl(classTree, classBrId );

                var tplBrId = '/base/'+compName+'.tpl.php';
                var tplTtext = utils.getTreeUrl(tplTree, tplBrId );
            } // if

            $(options.classFileText).html(classText);
            classTreeSelectId = classBrId;

            $(options.tplFileText).html(tplTtext);
            tplTreeSelectId = tplBrId;
            // func. initLoadData
        }

        function init(pOptions){
            options = pOptions;

            // Если расширенные настройки
            if ( compPropData.extSettings == 1 ){
                // Если есть, то формируем URL для ссылки
                var url = utils.url({
                    type: 'comp',
                    contr: compPropData.contid,
                    method: 'compProp'
                });
                $('#'+options.extSettBtn).attr('href', url).show();
                // if extSettings
            }

            // Ссылка для кнопки Назад
            $('#'+options.backBtn).attr('href', utils.url({
                contr: 'complist',
                query: {
                    contid: compPropData.contid
                }
            }));

            $('#'+options.saveBtn).click(saveBtnClick);

            HAjax.create({
                saveData: cbSaveData,
                loadCategoryData: cbLoadCategoryDataSuccess
            });

            initTreeCreate();
            initLoadData();

            $(options.classBtn).fancybox({
                beforeShow: beforeClassDlgShow
            });
            $(options.tplBtn).fancybox({
                beforeShow: beforeTplDlgShow
            });
            // func. init
        }

        return {
            init: init
        }
    })();

    $(document).ready(function(){
        compPropMvc.init({
            backBtn: 'backBtn',
            saveBtn: 'saveBtn',
            mainForm: 'mainForm',
            categorySelObj: 'select[name="category"]',
            classExtSelObj: 'select[name="classExt"]',
            classUserSelObj: 'select[name="classUser"]',
            tplExtSelObj: 'select[name="tplExt"]',
            tplUserSelObj: 'select[name="tplUser"]',
            extSettBtn: 'extendsSettings',
            // Кнопка выборка класса в дереве. При клике появлется дерево
            classBtn: '#classBtn',
            // Кнопка выборка шаблонов в дереве. При клике появлется дерево
            tplBtn: '#tplBtn',
            // бокс для выборанного текста в дереве классов
            classFileText: '#classFileText',
            // бокс для выборанного текста в дереве шаблонов
            tplFileText: '#tplFileText'
        });
    });
</script>