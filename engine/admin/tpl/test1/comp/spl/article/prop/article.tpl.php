<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />
<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

<style type="text/css">
    div .dt{font-weight: bold}
    div .dd{ padding-left: 25px}
</style>

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
    <form id="mainForm">
        
        <div class="dt">
            URL:
        </div>
        <div class="dd">
            <?= self::text('name="url"', self::get('url')); ?>
        </div>

    </form>

    <div class="dt">
        Размеры картинок
    </div>
    <div class="dd">
        <div>
            <a id="btnAddSize" href="#formAddSize" title="Добавить" >
                <img src="<?= self::res('images/add_32.png') ?>" width="32" height="32" alt="Добавить"/>
                Добавить
            </a>
        </div>
        <table id="sizeList"></table>
    </div>

</div>

<div style="display:none">
    <form id="formAddSize">
        <p><label for="sizeName">Название размера: </label></p>
        <p><input id="sizeName" type="text" name="name"/></p>

        <p><label for="typeSize">Сторона сжатия:</label></p>
        <p>
            <select name="type" id="typeSize">
                <option value="width">width</option>
                <option value="height">height</option>
            </select>
        </p>

        <p><label for="valSize">Значение: </label></p>
        <p><input type="text" name="val" id="valSize"/></p>

        <p><input type="submit" value="Добавить" id="btnSaveAddSize"/></p>
    </form>
</div>

<script>
    var articleProp = {
        contid: <?= self::get('contId') ?>
    };
    
    var contrName = articleProp.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
    
    articleProp.sizeList = <?= self::get('sizeList') ?>;
    articleProp.imgSrc = '<?= self::res('images/') ?>';
    
    articleProp.btnSaveAddSizeClick = function(){
        var data = $('#formAddSize').serialize();
        data += '&contid=' + articleProp.contid;
        HAjax.addSize({data: data});
        return false;
        // func. articleProp.btnSaveAddSizeClick
    }
    
    articleProp.addSize = function(pData){
        $.fancybox.close();
        if (pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        
        articleProp.addRecordSize(pData);
        // func. articleProp.addSize
    }
    
    articleProp.sizeDeleteClick = function(){
        if ( !confirm('Уверены что хотите удалить?')){
            return;
        }
        var itemId = $(this).attr('itemid');
        var data = 'contid='+articleProp.contid+'&itemid='+itemId;
        HAjax.delItem({data: data});
        // func. articleProp.sizeDeleteClick
    }
    
    articleProp.delItem = function(pData){
        if (pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        $('#sizeItem'+pData['itemid']).remove();
    }
    
    articleProp.addRecordSize = function(pItem){
        $('#sizeList').append(
        '<tr id="sizeItem'+pItem['id']+'">'
            +'<td>'+pItem['name']+' ('+pItem['type']+':'+pItem['val']+')</td>'
            +'<td><img src="'+articleProp.imgSrc+'del_16.png" class="sizeDel" itemid="'+pItem['id']+'"/></td>'
            +'</tr>'
    );
        // func. articleProp.addRecordSize
    }
    
    articleProp.saveBtnClick = function(){
        var data = $('#mainForm').serialize();
        HAjax.savePropData({data: data});
        // func. articleProp.saveBtnClick
    }
    
    articleProp.savePropDataSuccess = function(pData){
        if (pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        alert('Данные сохранены');
        // articleProp.saveDataSuccess
    }
    
    $(document).ready(function(){
        $("#btnAddSize").fancybox({
            titleShow: false,
            scrolling: 'no',
            width: 250
        });
        
        url = utils.url({type: 'manager', contr: 'compprop', query: {contid: articleProp.contid}});
        $('#backBtn').attr('href', url);
        
        $('#saveBtn').click(articleProp.saveBtnClick);
        
        $('.sizeDel').live('click', articleProp.sizeDeleteClick);
        
        HAjax.create({
            addSize: articleProp.addSize,
            delItem: articleProp.delItem,
            savePropData: articleProp.savePropDataSuccess
        });
        
        for(var i in articleProp.sizeList ){
            articleProp.addRecordSize(articleProp.sizeList[i]);
        }
        
        $('#btnSaveAddSize').click(articleProp.btnSaveAddSizeClick);
        // func. $(document).ready
    });
</script>
