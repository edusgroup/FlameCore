<link   href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<!--<script src="res/plugin/classes/html.js" type="text/javascript"></script>-->
<style>
    #recordBox div.record{
        margin-bottom: 10px;
        border: 1px #DDDDDD solid;
        padding: 5px 5px 5px 5px;
    }

    #recordBox input.recName{
        width: 120px;
    }

    #recordBox .methodSel{
        width: 120px;
    }
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
                        <a href="/?$t=manager&$c=wareframe"  title="WF">
                            <img src="<?= self::res('images/wf_32.png') ?>" alt="WF" /><span>Wareframe</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=complist"  title="Component List">
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
                    <div>
                        <a href="#ar" id="addRecordBtn" title="Добавить новую запись">
                            <img src="<?= self::res('images/add_32.png') ?>" alt="Добавить"/>
                        </a>
                    </div>
                    <div id="recordBox"></div>
                </form>

            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<div id="compTreeBox" style="width:200px;height:300px; display: none"></div>

<div id="classTreeBox" style="width:200px;height:300px; display: none"></div>

<script type="text/javascript">
    var contrName = 'ajax';
    var callType = 'utils';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
   
    var ajaxData = {
        resUrl:'<?= self::res('images/') ?>',
        compTreeJson: <?=self::get('compTreeJson')?>
    } // ajaxData
    
    var ajaxMvc = (function(){
        var newRecId = -1;
        var options = {};
        // Буффер записей на удаление
        var recRmBuff = [];
        // буффер записей
        var recLoadBuff = {};
        // Вылеленный record Id
        var selRecordId;

        var compTree, classTree;
        
        // Клик по кноке Сохранить
        function saveBtnClick(pEvent){
            //var data = $(options.editor).val();
            var data = $(options.mainForm).serialize();
            HAjax.saveData({data: data, methodType: 'POST'});
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

        function recordBoxAdd(pId){
            var html = '<div class="record" id="rec'+pId+'">\
            <div>\
                <a href="#" title="Удалить"><img src="' + ajaxData.resUrl + 'del_16.png" type="rmRec"/> Удалить</a>\
            </div>\
            <div>\
                Название: <input type="text" class="recName" name="rec'+pId+'"/>\
            </div>\
            \
            <div>\
                Компонент: <a href="#" title="Выберите компонент">\
                <img alt="Выбрать компонент" src="'+ajaxData.resUrl+'folder_16.png" type="selComp"/>\
                <span class="text compText" type="selComp">*</span></a>\
            </div>\
            \
            <div>\
                Класс: <a href="#" title="Выберите класс">\
                <img alt="Выбрать класс" src="'+ajaxData.resUrl+'folder_16.png" type="selClass"/>\
                <span class="text classText" type="selClass">*</span></a>\
            </div>\
            \
            <div>\
                Метод: <select class="methodSel"></select>\
            </div>\
            </div>';

            $(options.recordBox).append(html);

            // func. recordBox
        }

        function recordRm(pRecId){
            // Подтвержаем что мы хотим удалить данные
            if (!confirm('Уверены что хотите удалить?')) {
                return false;
            }
            if ( pRecId >= 0 ){
                recRmBuff.push(pRecId);
            } // if
            $('#rec'+pRecId).remove();
            // func. recordRm
        }

        function recordSelComp(pRecId){
            selRecordId = pRecId;
            var compId = '0';
            if ( recLoadBuff[pRecId] ){
                compId = recLoadBuff[selRecordId].compId
            }
            compTree.selectItem(compId);
            $.fancybox({href: '#compTreeBox'});
            // func. recordSelComp
        }

        function recordSelClass(pRecId){
            selRecordId = pRecId;
            if ( !recLoadBuff[pRecId] ){
                alert('Выберите компонент');
                return;
            }
            var compId = recLoadBuff[selRecordId].compId
            HAjax.loadClassTree({query:{compId: compId }});
            // func. recordSelClass
        }

        function recordBoxClick(pEvent){
            var recId = $(pEvent.target).parents('.record:first').attr('id').substr(3);
            var recType = $(pEvent.target).attr('type');

            recId = parseInt(recId);
            switch(recType){
                case 'rmRec':
                    recordRm(recId);
                    break;
                case 'selComp':
                    recordSelComp(recId);
                    break;
                case 'selClass':
                    recordSelClass(recId);
                    break;
            } // switch


            return false;
            // func. recordBoxClick
        }

        function compTreeDbClick(pBrunchId, pCompTree){
            // Получаем тип ветки: 1-папка, 0-файл
            var type = pCompTree.getUserData(pBrunchId, 'type');
            // Выбрать можно только файл
            if (type != 1) {
                return false;
            }

            var text = utils.getTreeUrl(pCompTree, pBrunchId);
            $('#rec'+selRecordId+' .compText:first').html(text);
            if ( !recLoadBuff[selRecordId] ){
                recLoadBuff[selRecordId] = {};
            }
            recLoadBuff[selRecordId].compId = pBrunchId;
            recLoadBuff[selRecordId].classFile = '';
            $('#rec'+selRecordId+' .classText:first').html('*');
            $.fancybox.close();
            //func. compTreeDbClick
        }

        function cbLoadClassMethodSuccess(pData){
            if ( pData['error'] ){
                alert(pData['error']['msg']);
                return;
            }

            // func. cbLoadClassMethodSuccess
        }

        function cbLoadClassTreeSuccess(pData){
            if ( pData['error'] ){
                alert(pData['error']['msg']);
                return;
            }

            classTree.deleteChildItems(0);
            // Грузим новое дерево
            classTree.loadJSONObject(pData['classTreeJson']);
            //selCompId = pBrunchId;
            $.fancybox({href: '#classTreeBox'});
            // func. cbLoadClassTreeSuccess
        }

        function classTreeDbClick(pBrunchId, pClassTree){
            var text = utils.getTreeUrl(pClassTree, pBrunchId);
            $('#rec'+selRecordId+' .classText:first').html(text);
            recLoadBuff[selRecordId].classFile = pBrunchId;
            $.fancybox.close();
            var compId = recLoadBuff[selRecordId].compId;
            HAjax.loadClassMethod({query:{compIp: compId, classFile: pBrunchId}});
            // func. classTreeDbClick
        }

        function addRecordBtnClick(){
            recordBoxAdd(newRecId);
            --newRecId;
            return false;
            // func. addRecordBtnClick
        }

        function initTree(){
            // Создаём наши деревья
            dhtmlxInit.init({
                'comp':{
                    tree:{ json: ajaxData.compTreeJson, id:'compTreeBox' },
                    dbClick: compTreeDbClick
                } ,
                'class':{
                    tree:{ id:'classTreeBox'},
                    dbClick: classTreeDbClick
                }});

            compTree = dhtmlxInit.tree['comp'];
            classTree = dhtmlxInit.tree['class'];
            // func. initTree
        }
        
        function init(pOptions){
            options = pOptions;

            initTree();
            
            // Кнопка Назад
            $(options.backBtn).attr('href', utils.url({
                contr:'tree'
            }));

            // Кнопка Сохранить
            $(options.saveBtn).click(saveBtnClick);
            $(options.addRecordBtn).click(addRecordBtnClick);
            $(options.recordBox).click(recordBoxClick);

            HAjax.create({
                saveData: saveDataSuccess,
                loadClassTree: cbLoadClassTreeSuccess,
                loadClassMethod: cbLoadClassMethodSuccess
            });
            // func. init
        }
        
        return {
            init: init
        }  
    })();
    
    $(document).ready(function(){
        ajaxMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn',
            mainForm: '#mainForm',
            addRecordBtn: '#addRecordBtn',
            recordBox: '#recordBox'
        });
    }); // $(document).ready

</script>