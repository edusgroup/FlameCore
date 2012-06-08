<link   href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

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
                    <form id="formCont">
                        <div class="dt">Наследовать от родителя</div>
                        <div class="dd">
                            <label><?= self::checkbox('name="parentLoad" value="1"', self::get('parentLoad') == 1); ?></label>
                        </div>
                        <div class="dt">Категория</div>
                        <div class="dd">
                            <? self::select(self::get('categoryList'), 'name="category"') ?>
                        </div>
                        
                        <div class="dt">Шаблон админки</div>
                        <div class="dd">
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
                        </div>

                        <div class="dt">Функциональный класс</div>
                        <div class="dd">
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
                        </div>
                        
                        
                        <div><a href="#" id="extendsSettings" style="display: none">Расширенные настройки &raquo;</a></div>
                    </form>
                    
                </div>
            </div><!-- end panel right content -->

        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->
<script type="text/javascript">
    var contrName = 'compprop';
    var callType = 'manager';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
    
    var compprop = {
        contid: <?= self::get('contId') ?>,
        extSettings: <?= self::get('extSettings') ?>
    };
    
    compprop.saveBtnClick = function(){
        var data = $('#formCont').serialize();
        HAjax.saveData({data: data, methodType: 'POST', query: {contid: compprop.contid}});
        return false;
    }
    
    compprop.saveData = function(pData){
        if ( pData['error'] ){
            alert(pData['error']['msg']);
            return;
        }
        url = utils.url({type: 'comp', contr: compprop.contid, method: 'compProp'});
        $('#extendsSettings').toggle(pData['extSettings']==1).attr('href', url);
        
        if ( pData['ok'] ){
            alert('Данные сохранены');
        }
    }
    
    $(document).ready(function(){
        var url = '';
        if ( compprop.extSettings == 1 ){
            url = utils.url({type: 'comp', contr: compprop.contid, method: 'compProp'});
            $('#extendsSettings').attr('href', url).show();
        }
        
        url = utils.url({contr: 'complist', query: {contid: compprop.contid}});
        $('#backBtn').attr('href', url);
        
        $('#saveBtn').click(compprop.saveBtnClick);
        
        HAjax.create({
            saveData: compprop.saveData
        });
    });
</script>