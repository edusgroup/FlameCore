<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script src="/res/plugin/jquery/jquery-1.5.1.min.js"  type="text/javascript"></script>
        <!-- Add fancyBox main JS and CSS files -->
        <script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
        <link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

        <script src="res/plugin/SWFUpload-2.2.0.1/swfupload.js" type="text/javascript"></script>
        <script type="text/javascript" src="res/plugin/SWFUpload-2.2.0.1/js/handlers.js"></script>
        <link href="res/plugin/SWFUpload-2.2.0.1/css/default.css" rel="stylesheet" type="text/css"/>

        <script src="res/plugin/classes/utils.js" type="text/javascript"></script>
        <script src="res/plugin/fileManager/fileManager.js" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="res/plugin/fileManager/main.css" media="screen" />

    </head>
    <body>

        <div style="margin: 20px 20px 20px 20px">
            <div>
                <span id="spanButtonPlaceholder"></span>
                <input type="button" style="margin-left: 2px; height: 22px; font-size: 8pt;" disabled="disabled" value="Отменить загрузку" id="btnCancel">
                <input type="button" value="Удалить" id="btnDelete"/>
            </div>
            <div id="uploadProgress">

            </div>
        </div>

        <div id="folder" style="margin: 20px 20px 20px 20px"></div>


        <div id="imgSize" style="display:none">
            <div>Исходные размеры:</div>
            <div class="imgsize"></div>
            <div>Размер файла:</div>
            <div class="filesize"></div>

            <div id="selectSizeDiv">Выбирите размер:</div>
            <div>
                <? self::selectIdName(self::get('sizeList'), 'id="imgSizeList"'); ?>
            </div>
            <div><input type="button" value="Выбрать" id="choosetImgBtn"/></div>
        </div>


        <!--<form enctype="multipart/form-data" id="formTest" method="post">
            Send this file: <input name="files" type="file">
            <input type="submit" value="Send File">
        </form>-->
        

        <script>
            // -----------------------------------------------------------------------------

            var file = {
                fileList: <?= self::get('fileList')?:'[]' ?>,
                funcNameCallBack: <?= self::get('funcNameCallBack') ?>,
                fileDistUrl: '<?= self::get('fileDistUrl') ?>',
                filePreviewUrl: '<?= self::get('filePreviewUrl') ?>',
                maxFileId: 0,
                folderContenierId: 'folder',
                filterType: '<?= self::get('filterType') ?>',
                contrName: '<?=self::get('contrName')?>',
                callType: '<?=self::get('callType')?>',
                userQuery: '<?=self::get('userQuery')?>',
                isSizeListShow: '<?=self::get('isSizeListShow')?>',
                siteName: '<?=self::get('$siteName')?>'
            };

            utils.setType(file.callType);
            utils.setContr(file.contrName);
            HAjax.setContr(file.contrName);

            var swfu;

            function cbFileDialogComplete(numFilesSelected, numFilesQueued) {
                try {
                    if ( numFilesSelected < 500 ){
                        this.startUpload();
                    }else
                        if ( numFilesSelected != 0 ){
                            alert('Не больше 10 изображений за раз');
                        }
                } catch (ex) {
                    this.debug(ex);
                }
                // func. cbFileDialogComplete
            }

            function makeFileData(pData){
                var id = file.maxFileId++;
                return {
                    id: 'file'+id,
                    preview: file.filePreviewUrl +pData['preview'],
                    filesize: pData['filesize'],
                    imgsize: pData['imgsize'],
                    name: pData['name'],
                    type: pData['type'],
                    file: file.fileDistUrl + pData['name'],
                    button:{
                        select: true,
                        view: true
                    }
                }
                // func. makeFileData
            }

            function cbUploadSuccess(pFile, pData){
                if ( pData['dubl']){
                    return;
                }
                $('#'+file.folderContenierId).addFile(makeFileData(pData));
                // func. cbUploadSuccess
            }

            function cbMakePreviewUrl(pData) {
                if ( pData['error']){
                    alert(pData['error']['msg']);
                    return;
                } // if
                var previewUrl  = fileManager.selectItem.preview;
                fileManager.returnInEditor(fileManager.funcNameCallBack, pData['url'], previewUrl);
                // func. cbMakePreviewUrl
            }

             function choosetImgBtnClick(){
                // Получаем выбранный размер
                var sizeId = $("#imgSizeList :selected").val();
                // Нужно ли производить ресайз картинки
                if ( sizeId == 'noResize'){
                    // Получаем URL файла
                    var fileUrl  = fileManager.selectItem.file;
                    var previewUrl  = fileManager.selectItem.preview;
                    // Возвращаемся к ckEdit
                    fileManager.returnInEditor(fileManager.funcNameCallBack, fileUrl, previewUrl);
                }else{
                    // Получаем имя картинки
                    var name = fileManager.selectItem.name;
                    // Отправляем запрос на ресайз изобразения

                    HAjax.makePreviewUrl({data: {name: name, sizeid: sizeId}, query: file.userQuery});
                }
                // func. choosetImgBtnClick
            }
            
            function btnDeleteClick(){
                if (!confirm('Уверены что хотите удалить?')){
                    return false;
                }
                fileManager.itemSelectList['contid'] = file.contid;
                HAjax.fileRm({data: fileManager.itemSelectList, methodType: 'POST', query: file.userQuery});
                //console.log(fileManager.itemSelectList);
                return false;
                // func. btnDeleteClick
            }

            function cbFileRm(pData){
                if ( pData['error']){
                    alert(pData['error']['msg']);
                    return;
                }
                
                for( var num in pData['idlist'] ){
                    var fileId = pData['idlist'][num];
                    $('#'+fileId).remove();
                } // ofr
                // func. cbFileRm
            }

            $(document).ready(function(){
                fileManager.funcNameCallBack = file.funcNameCallBack;
                
                if ( !file.isSizeListShow ){
                    $('#selectSizeDiv').hide();
                }
                
                $('#choosetImgBtn').click(choosetImgBtnClick);
                $('#btnDelete').click(btnDeleteClick);
                
                HAjax.create({
                    makePreviewUrl: cbMakePreviewUrl,
                    fileRm: cbFileRm
                });
                
                switch (file.filterType) {
                    case 'img':
                        SWFUploadSettings.file_types = '*.jpg;*.gif;*.png;*.jpeg';
                        SWFUploadSettings.file_types_description = "Image files";
                        break;
                    case 'flash':
                        SWFUploadSettings.file_types = '*.swf';
                        SWFUploadSettings.file_types_description = "Flash files";
                        break;
                } // switch

                SWFUploadSettings.file_post_name = 'files';
                SWFUploadSettings.upload_url = utils.url({method: 'uploadFile', query: file.userQuery});
                SWFUploadSettings.upload_url += '&siteName=' + file.siteName;
                //$('#formTest').attr('action', SWFUploadSettings.upload_url);
                SWFUploadSettings.file_dialog_complete_handler = cbFileDialogComplete;
		        SWFUploadSettings.button_window_mode = 'opaque';
		        SWFUploadSettings.debug = false;

                fileUpload.onUploadSuccess = cbUploadSuccess;
                swfu = new SWFUpload(SWFUploadSettings);
                
                $('#folder').folderProp({
                    imgPath: '/res/plugin/fileManager/img/'
                });

                for(var i = 0; i < file.fileList.length; i++){
                    $('#'+file.folderContenierId).addFile(makeFileData(file.fileList[i]));
                } // for(file.fileList)
                // window.ready
            }); 
        </script>
    </body>
</html>
