<link href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen"/>

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
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
                <form id="mainForm">
                    <h6>Создание каталога ссылок для объектов</h6>
                    <div class="bothpanel">
                        <div>
                            <div class="dt">Шаблон ссылки</div>
                            <div class="dd">
                                <a href="#routeBox" id="tplUrlBtn">
                                    <img src="<?= self::res('images/folder_16.png') ?>" alt="Шаблон ссылки"/>
                                    <span id="tplUrlText"></span>
                                </a>
                            </div>
                        </div>
                        <div>
                            <div class="dt">Название:</div>
                            <div class="dd"><?=self::text('name="caption"', self::get('caption'))?></div>
                        </div>
                    </div>
                </form>
                <div style="clear:both; padding-bottom: 10px;"></div>
                <div class="bothpanel">
                    <div id="contDiv" class="treePanel"></div>
                    <div>

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

<script type="text/javascript">
    var catalogContData = {
        contTree: <?= self::get('contTree') ?>,
        routeTree: <?= self::get('routeTree') ?>,
        catalog: <?= self::get('catalog')?:'{}' ?>,
        contid: <?= self::get('contId') ?>,
        tplUrl: <?= self::get('tplUrl')?:'""' ?>
    };

    var contrName = catalogContData.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);

    var catalogContMvc = (function () {
        var options = {};
        var tree = {};
        //var treeCheckSel = [];
        //var treeCheckDel = [];

        // Клик по кноке Сохранить
        function saveBtnClick() {
            var sel = tree.compcont.getAllCheckedBranches();
            var data = $('#'+options.mainForm).serialize();
            HAjax.saveData({
                data:'sel=' + sel + '&urltpl=' + catalogContData.tplUrl + '&'+data,
                methodType:'POST'
            });
            // func. saveBtnClick
        }

        function routeTreeDbClick(pTreeId, pTree) {
            $.fancybox.close();
            catalogContData.tplUrl = utils.getTreeUrlTpl(pTree, pTreeId);
            $('#' + options.tplUrlText).html(catalogContData.tplUrl);
            // func. routeTreeDbClick
        }

        function initTrees() {
            dhtmlxInit.init({
                'contTree':{
                    tree:{
                        id:'contDiv',
                        json:catalogContData.contTree
                    }, // tree,
                    checkbox:true
                }, // contTree
                'routeTree':{
                    tree:{
                        id:'routeBox',
                        json:catalogContData.routeTree
                    }, // tree
                    dbClick:routeTreeDbClick
                } // routeTree
            }); // init
            tree.compcont = dhtmlxInit.tree['contTree'];
            tree.route = dhtmlxInit.tree['routeBox'];

            //tree.compcont.setOnCheckHandler(treeContCheck);

            tree.compcont.enableThreeStateCheckboxes(0);

            for (var i in catalogContData.catalog) {
                var id = catalogContData.catalog[i];
                tree.compcont.setCheck(id, 1);
            } // for
            // func. initTrees
        }

        // callback сохранения данных
        function saveDataSuccess(pData) {
            if (pData['error']) {
                alert(pData['error']['msg']);
                return;
            }
            alert('Данные успешно сохранены');
            // func. saveDataSuccess
        }

        function init(pOptions) {
            options = pOptions;
            initTrees();
            // Кнопка Назад
            $(options.backBtn).attr('href', utils.url({
                type:'manager',
                contr:'complist'
            }));
            // Кнопка Сохранить
            $(options.saveBtn).click(saveBtnClick);

            $('#'+options.tplUrlBtn).fancybox();
            $('#'+options.tplUrlText).html(catalogContData.tplUrl);

            HAjax.create({
                saveData: saveDataSuccess
            });
            // func. init
        }

        return {
            init:init
        }
    })();

    $(document).ready(function () {

        catalogContMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn',
            treeDiv: '#treeDiv',
            tplUrlBtn: 'tplUrlBtn',
            tplUrlText: 'tplUrlText',
            mainForm: 'mainForm'
        });
    }); // $(document).ready

</script>