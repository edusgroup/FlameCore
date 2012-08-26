<style>
    div.folder {
        margin: 20px 20px 20px 20px;
        color: #FFFFFF;
    }

    div.folder div.name label {
        text-align: left;
        margin: 0 0 0 0;
        width: 100%;
        padding: 0 0 0 0;
        display: inline;
    }

    div.folder div.item {
        margin-bottom: 10px;
        margin-right: 10px;
        float: left;
        background: #a3a3a3;
        padding: 3px 3px 3px 3px;
    }

    div.folder div.item.select {
        background: #4b65d4;
    }

    div.folder div.item.remove {
        background: #d4270b;
    }

    div.item .preloader {
        background: white url(/res/plugin/fileManager/img/loader/preloader.gif) no-repeat center center;
    }

    div.item .file {
        width: 128px;
        height: 128px;
        background: no-repeat center center;
    }

    div.folder div.panel {
        width: 128px;
        text-align: left;
        height: 20px;
        background: inherit;
        margin: 0 0 0 0;
        padding: 0 0 0 0;
    }

    div.item .name {
        width: 128px;
        height: 20px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        -o-text-overflow: ellipsis;
        -moz-binding: url('/examples/text-overflow.xml#ellipsis');
    }

    div.item .file .action {
        display: none;
    }

    div:hover.item .file .action {
        width: 100%;
        height: 89px;
        background: url(/res/plugin/fileManager/img/black50.png) no-repeat 0 0;
        text-align: center;
        padding-top: 39px;
        display: block;
    }

    div.item .panel .button img {
        cursor: pointer
    }

    div.item .file .action img {
        cursor: pointer;
    }
</style>
<div style="margin: 20px 20px 20px 20px">
    <div>
        <span id="spanButtonPlaceholder"></span>
        <input type="button" style="margin-left: 2px; height: 22px; font-size: 8pt;"
               disabled="disabled" value="Отменить загрузку" id="btnCancel">
    </div>
    <div>
        <div style="width: 100px; float: left;">Созд. превью:</div>
        <div style="width: 200px; float: left;">Размер превью:</div>
        <div style="width: 200px; float: left;">Размер оригин.:</div>
    </div>
    <div style=" clear: both;"></div>
    <div>
        <form id="idSizeListForm">
			<div style="width: 100px; float: left;">
				<input type="checkbox" name="isCrPreview" value="1"/>
			</div>
            <div style="width: 200px; float: left;">
                <? self::selectIdName(self::get('sizeList'), 'name="imgPrevSize"'); ?>
            </div>
            <div style="width: 200px; float: left;">
                <? self::selectIdName(self::get('sizeList'), 'name="imgOrigSize"'); ?>
            </div>
        </form>
    </div>
    <div style=" clear: both;"></div>

    <div id="uploadProgress">

    </div>
</div>
<div>Выдилите файл, что бы его выбрать</div>
<div id="folder" class="folder"></div>

<!-- <form enctype="multipart/form-data" id="formTest" method="post">
   Send this file: <input name="files" type="file">
   <input type="submit" value="Send File">
</form> -->

<script>
// -----------------------------------------------------------------------------
var file = {
    fileList: <?= self::get('fileList') ?>,
    funcNameCallBack: <?= self::get('funcNameCallBack') ?>,
    fileDistUrl:'<?= self::get('fileDistUrl') ?>',
    filePreviewUrl:'<?= self::get('filePreviewUrl') ?>',
    maxFileId:0,
    //folderBox: 'folder',
    contrName:'<?=self::get('contrName')?>',
    callType:'<?=self::get('callType')?>',
    // Названия редактируемого сайта ( нужно для флеша, из-за кук )
    siteName:'<?=self::get('$siteName')?>',
    // Массив выбранных имён файлов
    fileData: <?= self::get('fileData', 'null') ?>
};

utils.setType(file.callType);
utils.setContr(file.contrName);
HAjax.setContr(file.contrName);

var swfu;

file.fileDialogComplete = function (numFilesSelected, numFilesQueued) {
    try {
        if (numFilesSelected < 500) {
            this.startUpload();
        } else
        if (numFilesSelected != 0) {
            alert('Не больше 10 изображений за раз');
        }
    } catch (ex) {
        this.debug(ex);
    }
    // func. fileDialogComplete
}

file.makeFileData = function (pImageData, pIsSelect, pConfData) {
    //var id = file.maxFileId++;
    return {
        id: pImageData['md5'],//'file' + id,
        preview:file.filePreviewUrl + pImageData['preview'],
        filesize: pImageData['filesize'],
        imgsize: pImageData['imgsize'],
        name: pImageData['name'],
        type: pImageData['type'],
        file: file.fileDistUrl + pImageData['name'],
        caption: pConfData['caption'],
        isSelect: pIsSelect,
        button:{
            view:true
        }
    }
    // func. makeFileData
}

file.onUploadSuccess = function (pFile, pData) {
    if (pData['dubl']) {
        return;
    }
    imgGalleryManager.addFile(file.makeFileData(pData, false, {caption: ""}));
    // func. onUploadSuccess
}

var imgGalleryManager = (function () {

    var options = {};
    var folderBox = null;
    var changeList = {};

    function _convertButton(pName) {
        //var text = this.button[pName];
        return '<IMG'
            + ' SRC="' + options.pluginRes + 'button/' + pName + '.png"'
            //+' TITLE="'+text+'" ALT="'+text+'"'
            + ' action="preview"'
            + ' CLASS="' + pName + '"'
            + '/>';
        return '';
    }

    function folderBoxClick(pEvent) {
        var obj = pEvent.target;
        var $item = $(obj).parents('div.item:first');
        var itemId = $item.attr('id');
        var action = $(obj).attr('action');
        // Пометка на удаление картинки

        if (action == 'del') {

            if (changeList['r[' + itemId + ']']) {
                $item.removeClass('remove');
                delete changeList['r[' + itemId + ']'];
            } else {
                $item.addClass('remove');
                $item.removeClass('select');
                changeList['r[' + itemId + ']'] = $item.attr('name');
                delete changeList['s[' + itemId + ']'];
            } // if

        } else // if del
        if (action == 'preview') {
            var href = file.fileDistUrl + $item.attr('name');
            $.fancybox({href:href});
        } else
        if (action == 'caption') {
            var $captionObj = $item.find('input.caption');
            var caption = $captionObj.val();
            caption = prompt('Введите название', caption);
            // Если нажата кнопка Отмены или пустой заголовок
            if ( !caption ){
                return;
            }
            $captionObj.val(caption ? caption : '');
        } else { // if caption
            // В остальных случаях выделение
            if (changeList['s[' + itemId + ']']) {
                $item.removeClass('select');
                delete changeList['s[' + itemId + ']'];
            } else {
                $item.addClass('select');
                changeList['s[' + itemId + ']'] = $item.attr('name');
            } // if
        } // if
        // func. folderBoxClick
    }

    function addFile(pConf) {
        if (!pConf.name || !pConf.id) {
            return;
        }

        var itemUrlPreview = pConf.preview ? pConf.preview : '';
        var itemUrlOrig = pConf.file ? pConf.file : '';

        var itemClass = pConf.isSelect ? ' select' : '';

        var item = '<div class="item' + itemClass + '" id="' + pConf.id + '"'
            + ' name="' + pConf.name + '">'
            + '<input type="hidden" name="caption['+pConf.id+']" class="caption" value="' + pConf.caption + '"/>'
            + '<div class="name">'
            + '<img src="' + options.pluginRes + 'icons/delete.png" action="del"/> '
            + '<img src="' + options.pluginRes + 'icons/rename.png" action="caption"/> '
            + pConf.name + '</div>'
            + '<div class="preloader">'
            + '<div class="file" '
            + 'style="background-image: url(\'' + itemUrlPreview + '\')" '
            + 'file="' + itemUrlOrig + '" '
            + 'type="' + pConf.type + '">'
            + '<div class="action">'

        if (pConf.isSelect) {
            changeList['s[' + pConf.id + ']'] = pConf.name;
        }

        for (var buttonName in pConf.button) {
            item += _convertButton(buttonName, options.imgPath);
        }

        item += '</div></div></div>'
            + '<div class="panel">'
            + pConf.imgsize + ' (' + pConf.filesize + ')</div>'
            + '</div>';
        folderBox.append(item);
        // func. addFile
    }

    function init(pOptions) {
        options = pOptions;

        folderBox = $('#' + options.folderBox);
        folderBox.click(folderBoxClick);
        // func. init
    }

    function saveDataSuccess(pData) {
        for (var num in pData['idlist']) {
            var fileId = pData['idlist'][num];
            $('#' + fileId).remove();
        } // if
    }

    return{
        addFile:addFile,
        init:init,
        changeList:changeList,
        saveDataSuccess:saveDataSuccess
    }
})();


$(document).ready(function () {
    fileManager.funcNameCallBack = file.funcNameCallBack;

    $('#btnImgSelect').click(file.btnImgSelect);

    HAjax.create({
        fileRm:file.fileRm
    });

    SWFUploadSettings.file_types = '*.jpg;*.gif;*.png';
    SWFUploadSettings.file_types_description = "Image files";

    SWFUploadSettings.file_post_name = 'files';
    SWFUploadSettings.upload_url = utils.url({method:'uploadFile', query:file.userQuery});
    SWFUploadSettings.upload_url += '&siteName=' + file.siteName;
    // $('#formTest').attr('action', SWFUploadSettings.upload_url);
    SWFUploadSettings.file_dialog_complete_handler = file.fileDialogComplete;
    SWFUploadSettings.button_window_mode = 'opaque';
    SWFUploadSettings.debug = false;

    fileUpload.onUploadSuccess = file.onUploadSuccess;
    swfu = new SWFUpload(SWFUploadSettings);

    // Если данные есть, то ранее было сохранение и в этой переменной находятся сохранённые параметры
	// file.fileData['size']['origSize'] - Размер большой картинки
	// file.fileData['size']['prevSize'] - Размер маленькой картинки
	// file.fileData['isCrPreview'] - нужно ли создовать превью
    if (file.fileData) {
        var $idSizeListForm = $('#idSizeListForm');
        $idSizeListForm.find('select[name="imgPrevSize"]').val(file.fileData['size']['prevSize']);
        $idSizeListForm.find('select[name="imgOrigSize"]').val(file.fileData['size']['origSize']);
		var checked = file.fileData['isCrPreview'] ? 'checked' : '';
        $idSizeListForm.find('input[name="isCrPreview"]').attr('checked', checked); 
    } // if

    imgGalleryManager.init({
        pluginRes:'/res/plugin/fileManager/img/',
        folderBox:'folder'
    });

    // Выделяем уже ранее сохрённые изображения
    for (var i = 0; i < file.fileList.length; i++) {
        var id = file.fileList[i]['md5'];

        var isSelect = false;
        var confItem = {
            caption: ''
        }
        if ( isset(file.fileData, ['data', id]) ){
            isSelect = file.fileData['data'][id]['file'];
            confItem.caption = file.fileData['data'][id]['caption'];
        } // if

        var conf = file.makeFileData(file.fileList[i], isSelect, confItem);//file.fileData[j]);
        imgGalleryManager.addFile(conf);
    } // for i in (file.fileList)
    // window.ready
});
</script>
