<link href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen"/>

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
                        <a href="/?$t=manager&$c=wareframe" id="" title="WF">
                            <img src="<?= self::res('images/wf_32.png') ?>" alt="WF"/><span>Wareframe</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=complist" title="Component List">
                            <img src="<?= self::res('images/refresh_32.png') ?>" alt="Component List"/><span>Component List</span>
                        </a>
                    </li>

                    <li>
                        <a href="#save" id="saveBtn" title="Сохранить">
                            <img src="<?= self::res('images/save_32.png') ?>" alt="Сохранить"/><span>Сохранить</span>
                        </a>
                    </li>

                </ul>
            </div>

            <div class="content">
                <h6>Последние добавл. объекты</h6>
                <div>In <?=self::get('buildClassPathIn')?></div>
                <div>Out <?=self::get('buildClassPathOut')?></div>

                <div class="bothpanel">
                    <div id="contDiv" class="treePanel"></div>
                    <div style="border-left: 1px solid blue ; padding-left: 5px">
                        <form id="propBox">
                            <div class="dt">Количество элементов</div>
                            <div class="dd" style="margin-bottom: 5px">
                                <?=self::text('id="itemsCount" name="itemsCount"', self::get('itemsCount', 10))?>
                            </div>

                            <div class="dt">Тип сжатия</div>
                            <div class="dd" style="margin-bottom: 5px">
                                <select name="resizeType">
                                    <option value="prop">Пропорционально</option>
                                    <option value="square">Квадрат.По центру</option>
                                </select>
                            </div>

                            <div class="dt">Размер превью по ширине</div>
                            <div class="dd" style="margin-bottom: 5px">
                                <?=self::text('name="previewWidth"', self::get('previewWidth', 128))?>
                            </div>

                            <div class="dt">Добавлять мини описание?</div>
                            <div class="dd" style="margin-bottom: 5px">
                                <?=self::checkbox('name="isAddMiniText" value="1"', self::get('isAddMiniText'))?>
                            </div>

                            <div class="dt">Создовать превью?</div>
                            <div class="dd" style="margin-bottom: 5px">
                                <?=self::checkbox('name="isCreatePreview" value="1"', self::get('isCreatePreview'))?>
                            </div>

                            <div class="dt">Build Class</div>
                            <div class="dd">
                                <a id="classBtn" href="#classTreeDlg" class="btn">
                                    <img src="<?= self::res('images/folder_16.png') ?>" alt="Класс компонента"/>
                                    <span id="classFileText"></span>
                                </a>
                            </div>
                        </form>
                   </div>

                </div>
            </div>
            <!-- end panel right content -->
        </div>
        <!-- end panel right content -->
    </div>
    <!-- end panel right panel -->
</div><!-- end panel right column -->

<div id="routeBox" style="width:250px;height:350px; display: none"></div>
<div id="classTreeDlg" style="width:250px;height:350px; display: none"></div>

<script type="text/javascript">
    var oiLasterData = {
        contTreeJson: <?= self::get('contTree') ?>,
        // Выделенные ID элементов дерева compContTree
        selItem: <?= self::get('selItem')?:'{}' ?>,
        contid: <?= self::get('contId') ?>,
        resizeType: '<?= self::get('resizeType') ?>',
        classTreeJson: <?= self::get('classTree') ?>,
        // Выбранное значение в дереве классов
        classTreeSelectId: '<?=self::get('classFile')?>'
    }; // oiLasterData

    var contrName = oiLasterData.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);

    var oiLasterMvc = (function () {
        var options = {};

        // Дерево класов для builder
        var classTree;
        // Дерево контента. Основное дерево.
        var compContTree;

        // Клик по кноке Сохранить
        function saveBtnClick() {
            var sel = compContTree.getAllCheckedBranches();
            var propData = $('#' + options.propBox).serialize();
            propData += '&class='+oiLasterData.classTreeSelectId;

            HAjax.saveData({
                data:'sel=' + sel + '&' + propData,
                methodType:'POST'
            });
            return false;
            // func. saveBtnClick
        }

        function routeTreeDbClick(pTreeId, pTree) {
            $.fancybox.close();
            oiLasterData.tplUrl = utils.getTreeUrlTpl(pTree, pTreeId);
            $('#' + options.tplUrlText).html(oiLasterData.tplUrl);
            // func. routeTreeDbClick
        }

        function classBrunchDbClick(pBrunchId, pTree){
            // Получаем тип ветки: 1-папка, 0-файл
            var type = pTree.getUserData(pBrunchId, 'type');
            // Выбрать можно только файл
            if (type != 1) {
                return false;
            }
            var text = utils.getTreeUrl(pTree, pBrunchId);
            oiLasterData.classTreeSelectId = pBrunchId;
            $(options.classFileText).html(text);
            $.fancybox.close();
            // class classBrunchDbClick
        }

        function initTree() {
            dhtmlxInit.init({
                'contTree':{
                    tree:{
                        id:'contDiv', json:oiLasterData.contTreeJson
                    }, // tree
                    checkbox:true
                }, // contTree
                'classTree':{
                    tree:{
                        id:'classTreeDlg', json: oiLasterData.classTreeJson
                    }, // tree
                    dbClick: classBrunchDbClick
                } // classTree
            }); // init

            classTree = dhtmlxInit.tree['classTree'];
            compContTree = dhtmlxInit.tree['contTree'];
            compContTree.enableThreeStateCheckboxes(0);
            // func. initTrees
        }

        // callback сохранения данных
        function saveDataSuccess(pData) {
            if (pData['error']) {
                alert(pData['error']['msg']);
                return;
            } // if
            alert('Данные успешно сохранены');
            // func. saveDataSuccess
        }

        function beforeClassDlgShow(){
            classTree.selectItem(oiLasterData.classTreeSelectId);
            // func. beforeTplDlgShow
        }

        function initLoadData(){
            if ( !oiLasterData.classTreeSelectId ){
                oiLasterData.classTreeSelectId = '/base/build.php';
            } // if
            var text = utils.getTreeUrl(classTree, oiLasterData.classTreeSelectId);
            $(options.classFileText).html(text);

            for (var i in oiLasterData.selItem) {
                var id = oiLasterData.selItem[i];
                compContTree.setCheck(id, 1);
            } // for
            // func. initLoadData
        }

        function init(pOptions) {
            options = pOptions;

            initTree();

            // Кнопка Назад
            $(options.backBtn).attr('href', utils.url({
                type:'manager',
                contr:'complist'
            }));

            // Кнопка Сохранить
            $(options.saveBtn).click(saveBtnClick);
            HAjax.create({
                saveData:saveDataSuccess
            });

            $(options.classBtn).fancybox({
                beforeShow: beforeClassDlgShow
            });

            $('select[name="resizeType"]').val(oiLasterData.resizeType);

            initLoadData();
            // func. init
        }

        return {
            init:init
        }
        // func. oiLasterMvc
    })();

    $(document).ready(function () {
        oiLasterMvc.init({
            backBtn:'#backBtn',
            saveBtn:'#saveBtn',
            treeDiv:'#treeDiv',
            propBox:'propBox',
            classBtn: '#classBtn',
            classFileText: '#classFileText'
        });
    }); // $(document).ready

</script>