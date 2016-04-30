<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

<style>
    div .dt{font-weight: bold}
    div .dd{ padding-left: 25px}
    .hidden{display: none}
    #dataObj{
        height: 300px;
        width: 100%;
    }
    .clear{
        clear: both;
    }
</style>

<div class="column" >
    <div class="panel corners">

        <div class="title corners_top">
            <div class="title_element">
                <span id="history"><?=self::get('caption')?></span>
            </div>
        </div>

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
            <div>Caption: <?=self::get('caption')?></div>
            <div>Path: <?=self::get('loadDir')?></div>
            <div>
                <textarea id="dataObj"><?= self::get('data') ?></textarea>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
    
    var objItem = {
        contid: <?= self::get('contId') ?>,
        objItemId: <?= self::get('objItemId') ?>,
        data: '<?= self::get('data') ?>'
    }

    var contrName = objItem.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);

    HAjax.create({
        saveData: objItem.saveData
    });
    
    objItem.saveBtnClick = function(){
        var data = {
            'data': jQuery('#dataObj').val(),
            'contid': objItem.contid,
            'id': objItem.objItemId
        }
        HAjax.saveData({data: data, methodType: 'POST'});
        return false;
    }
    
   /* objItem.saveData = function(pData){
        if ( pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        alert('Данные успешно сохранены');
    }*/


    jQuery(document).ready(function(){
        // Кнопка Назад
        jQuery('#backBtn').attr('href', utils.url({}));
        jQuery('#saveBtn').click(objItem.saveBtnClick);

    });
</script>