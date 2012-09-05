<DIV class="items">
    <form id="acForm">
        <DIV class="dt">Доступность:</div>
        <div class="dd"><?= self::checkbox('name="enable" value="1"', self::get('enable', 1) == 1) ?></div>
        <? if (self::get('propType')) { ?>
            <div class="dt">Переменные:</div>
            <div class="dd">
                <a href="#varible" title="Упраление переменными" id="varBtn">
                    <img src="<?= self::res('images/edit_16.png') ?>"/>
                    <span><?= self::get('varName') ?></span></a>
            </div>
        <? } ?>
        <div class="dt">Редирект:</div>
        <div class="dd">
            <?= self::checkbox('name="isRedir" value="1"', self::get('isRedir') == 1) ?>
            <?= self::text('name="redirect"', self::get('redirect')) ?>
        </div>

        <div class="dt">Контроллер:</div>
        <div class="dd">
            <? self::selectIdName($this->get('contrList'), 'name="contrList"', self::get('controller')); ?>
        </div>

        <div class="dt">Robots:</div>
        <div class="dd">
            <? self::selectKeyName($this->get('robotsRuleList'), 'name="robots"'); ?>
        </div>

        <div class="dt">WareFrame:</div>
        <div class="dd">
            <a href="#" id="wfEditBtn">
                <img src="<?= self::res('images/edit_16.png') ?>"/>
            </a>

            <a href="#wfBox" id="wfDlgBtn">
                <img src="<?= self::res('images/folder_16.png') ?>"/>
            </a>

            <input type="hidden" id="wfVal" name="wfVal"/>
            <span id="wfPath"></span>
        </div>

        <div class="dt">
            Только. зарег. пользов.
        </div>
        <div class="dd">
            <?= self::checkbox('name="reguser" value="1"', self::get('userReg', 0) == 1) ?>
        </div>

        <div class="dt">
            Только. группы
        </div>
        <div class="dd">
            <a href="#groupBox" id="groupBtn">
                <img src="<?= self::res('images/folder_16.png') ?>"/>
            </a>
        </div>
        <div class="dd" id='relationBox'>

        </div>

    </form>
</div>
<script>
    action.wfId = <?= self::get('wfId', -1) ?>;
    action.varCount = <?= self::get('varCount') ?>;
    action.usGroupData = <?= self::get('usGroupData') ?>;

    var relationBox = '';
    for( var i in action.usGroupData ){
        var itemId = action.usGroupData[i];
        action.tree.group.setCheck(itemId, 1);
        relationBox += action.tree.group.getItemText(itemId) + ', ';
    } // for
            
    $('#relationBox').html(relationBox);
    
    $('#wfDlgBtn').fancybox({beforeShow: action.beforeShowWfBox});
    $('#wfPath').html(utils.getTreeUrl(action.tree.wf, action.wfId));
    $('#wfVal').val(action.wfId);
    $('#varBtn').click(action.varBtnClick);
    
    $('#wfEditBtn').click(action.wfEditBtnClick);
    
    if ( action.varCount ){
        $('select[name="robots"]').attr("disabled","disabled"); 
    }
    
    $('#groupBtn').fancybox({
        "beforeClose": action.groupBoxBeforeClose
    });
</script>