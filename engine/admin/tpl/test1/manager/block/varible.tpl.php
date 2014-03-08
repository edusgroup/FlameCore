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
    .tab{padding-left: 50px}
    div .items .dt{font-weight: bold}
    div .items .dd{ padding-left: 25px}
</style>


<script type="text/javascript">
    
    var contrName = 'varible';
    var callType = 'manager';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
    
    var varible = {
        acid:  <?= self::get('acId') ?>,
        varCount:  <?= self::get('varCount') ?>,
        varTypeVal: '<?=$this->get('varType')['val']?>'
    }

    function typeVarChange(type){
        // Подгрузаем HTML блок в код
        var query = { acid: varible.acid, type: type};
        var url = utils.url({method: 'loadTypeVar', query:query});
        $('#typeBox').load(url);
        // func. pElement
    }
    
    /**
     * Подгрузает данные, когда выбран определённый тип переменной
     * @param selectObj pElement элемент select-html
     */
    function onVarTypeChange(pElem){
        // Получаем тип переменной: Дерево, компонент
        var type = pElem.currentTarget.value;
        typeVarChange(type);
    }
    
    varible.saveDataClick = function(){
        alert('Выбирите типа переменной');
    }
    
    varible.saveBtnClick = function(){
        varible.saveDataClick();
        return false;
    }

    $(document).ready(function(){
        $('#varType').change(onVarTypeChange);
        $('#saveBtn').click(varible.saveBtnClick);
        $('#backBtn').attr('href', utils.url({contr:'action', query:{'acid': varible.acid}}));
        $('#acid').val(varible.acid);
        typeVarChange(varible.varTypeVal);

    });
 
</script>

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
                        <a href="#save" id="saveBtn" title="Сохранить">
                            <img src="<?= self::res('images/save_32.png') ?>" alt="Сохранить" /><span>Сохранить</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="content">

                <form id="contentForm">
                    <input type="hidden" name="acid" id="acid"/>
                    <div class="items">
                        <div class="dt">Назание action:</div>
                        <div class="dd"><?= self::get('varName') ?></div>

                        <div class="dt">Описание:</div>
                        <div class="dd"><?= self::textarea('name="descrip"', self::get('descrip')); ?></div>

                        <div class="dt">Источник данных</div>
                        <div class="dd"><? self::selectIdName($this->get('varType'), 'id="varType" name="varType"'); ?></div>

                        <div id="typeBox"><? self::includeBlock('typeBox'); ?></div>
                    </div>
                </form>

            </div><!-- end panel right content -->

        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->