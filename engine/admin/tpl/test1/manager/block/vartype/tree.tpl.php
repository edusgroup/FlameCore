<div class="dt">Компонент:</div>
<div class="dd">
    <a href="#compDlg" id="compBtn">
        <img src="<?= self::res('images/folder_16.png') ?>"/>
        <span id="compPath"></span>
    </a>
</div>
<div class="dt">
    Тип данных:
</div>
<div class="dd">
    TreeId:
    <a id="contBtn" href="#contDlg">
        <img src="<?= self::res('images/folder_16.png') ?>"/>
        <span id="contPath"></span>
    </a>
</div>

<div id="contDlg" style="width:250px;height:350px; display: none"></div>
<div id="compDlg" style="width:250px;height:350px; display: none"></div>
<script type="text/javascript">

    var varTypeData = {
        compTreeSelectId: <?= self::get('compid', 'null') ?>,
        contTreeSelectId: <?= self::get('contid', 'null') ?>,
        contTreeJson: <?= self::get('contTree', 'null') ?>,
        compTreeJson: <?= self::get('compTree', 'null') ?>
    }

    varible.saveDataClick = function () {
        var data = $('#contentForm').serialize();
        data += '&compId=' + varTypeData.compTreeSelectId;
        data += '&contId=' + varTypeData.contTreeSelectId;
        HAjax.saveData({data:data, methodType:'POST'});
        // func. varible.saveDataClick
    }

    var varibleMvc = (function () {
        var options;
        var compTree;
        var contTree;

        function initTree() {
            dhtmlxInit.init({
                'comp':{
                    tree:{ id:'compDlg', json:varTypeData.compTreeJson }, dbClick:compTreeDbClick
                },
                'cont':{
                    tree:{ id:'contDlg', json:varTypeData.contTreeJson }, dbClick:contTreeDbClick
                }
            });
            compTree = dhtmlxInit.tree['comp'];
            contTree = dhtmlxInit.tree['cont'];
            // func. initTree
        }

        /**
         * Нажатие на кнопку компоненты. Вызов окна с компонентами
         */
        function beforeCompDlgShow() {
            compTree.selectItem(varTypeData.compTreeSelectId);
            // func. beforeCompDlgShow
        }

        function beforeContDlgShow() {
            contTree.selectItem(varTypeData.contTreeSelectId);
            // func. beforeContDlgShow
        }

        /**
         * Выбор компонента. Закрытие окна
         */
        function compTreeDbClick(pTreeId, pTree) {
            if (varTypeData.compTreeSelectId == pTreeId) {
                return false;
            }
            $('#compPath').html(utils.getTreeUrl(pTree, pTreeId));

            $('#contPath').html('');
            varTypeData.compTreeSelectId = pTreeId;
            varTypeData.contTreeSelectId = '';
            contTree.deleteChildItems(0);

            HAjax.compLoadCompData({
                query:{compid:pTreeId}
            });

            $.fancybox.close();
            // func. compTreeDbClick
        }

        function initLoad() {
            var text = utils.getTreeUrl(compTree, varTypeData.compTreeSelectId);
            $('#compPath').html(text);
            text = utils.getTreeUrl(contTree, varTypeData.contTreeSelectId)
            $('#contPath').html(text);

            // func. initLoad
        }

        function cbSaveData(pData) {
            if (pData['error']) {
                alert(pData['error']['msg']);
                return;
            }
            alert('Данные успешно сохранены');
            // func. cbSaveData
        }

        function cbCompLoadCompData(pData) {
            contTree.loadJSONObject(pData['contTree']);
            // func. cbCompLoadCompData
        }

        function contTreeDbClick(pItemId, pContTree) {
            varTypeData.contTreeSelectId = pItemId;
            var text = utils.getTreeUrl(pContTree, pItemId);
            $('#contPath').html(text);
            $.fancybox.close();
            // func. contTreeDbClick
        }

        function init(pOptions) {
            options = pOptions;

            initTree();
            initLoad();

            HAjax.create({
                compLoadCompData:cbCompLoadCompData,
                saveData:cbSaveData
            });

            $(options.compBtn).fancybox({
                beforeShow:beforeCompDlgShow
            });

            $(options.contBtn).fancybox({
                beforeShow:beforeContDlgShow
            });

            // func. init
        }

        return{
            init:init
        }
    })(); // varibleMvc


    $(document).ready(function () {
        varibleMvc.init({
            compBtn:'#compBtn',
            contBtn:'#contBtn'
        });
    });

</script>