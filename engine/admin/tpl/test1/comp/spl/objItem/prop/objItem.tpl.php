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
    var objItemProp = {
        contid: <?= self::get('contId') ?>
    };
    
    var contrName = objItemProp.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
    
    objItemProp.sizeList = <?= self::get('sizeList') ?>;
    objItemProp.imgSrc = '<?= self::res('images/') ?>';
    
    objItemProp.btnSaveAddSizeClick = function(){
        var data = $('#formAddSize').serialize();
        data += '&contid=' + objItemProp.contid;
        HAjax.addSize({data: data});
        return false;
        // func. objItemProp.btnSaveAddSizeClick
    }
    
    objItemProp.addSize = function(pData){
        $.fancybox.close();
        if (pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        
        objItemProp.addRecordSize(pData);
        // func. objItemProp.addSize
    }
    
    objItemProp.sizeDeleteClick = function(){
        if ( !confirm('Уверены что хотите удалить?')){
            return;
        }
        var itemId = $(this).attr('itemid');
        var data = 'contid='+objItemProp.contid+'&itemid='+itemId;
        HAjax.delItem({data: data});
        // func. objItemProp.sizeDeleteClick
    }
    
    objItemProp.delItem = function(pData){
        if (pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        $('#sizeItem'+pData['itemid']).remove();
    }
    
    objItemProp.addRecordSize = function(pItem){
        $('#sizeList').append(
        '<tr id="sizeItem'+pItem['id']+'">'
            +'<td>'+pItem['name']+' ('+pItem['type']+':'+pItem['val']+')</td>'
            +'<td><img src="'+objItemProp.imgSrc+'del_16.png" class="sizeDel" itemid="'+pItem['id']+'"/></td>'
            +'</tr>'
    );
        // func. objItemProp.addRecordSize
    }
    
    objItemProp.saveBtnClick = function(){
        var data = $('#mainForm').serialize();
        HAjax.savePropData({data: data});
        // func. objItemProp.saveBtnClick
    }
    
    objItemProp.savePropDataSuccess = function(pData){
        if (pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        alert('Данные сохранены');
        // objItemProp.saveDataSuccess
    }
    
    $(document).ready(function(){
        $("#btnAddSize").fancybox({
            titleShow: false,
            scrolling: 'no',
            width: 250
        });
        
        url = utils.url({type: 'manager', contr: 'compprop', query: {contid: objItemProp.contid}});
        $('#backBtn').attr('href', url);
        
        $('#saveBtn').click(objItemProp.saveBtnClick);
        
        $('.sizeDel').live('click', objItemProp.sizeDeleteClick);
        
        HAjax.create({
            addSize: objItemProp.addSize,
            delItem: objItemProp.delItem,
            savePropData: objItemProp.savePropDataSuccess
        });
        
        for(var i in objItemProp.sizeList ){
            objItemProp.addRecordSize(objItemProp.sizeList[i]);
        }
        
        $('#btnSaveAddSize').click(objItemProp.btnSaveAddSizeClick);
        // func. $(document).ready
    });
</script>
