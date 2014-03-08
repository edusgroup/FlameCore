<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<!--<script type="text/javascript" src="res/plugin/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="res/plugin/ckeditor/config.js"></script>-->

<script type="text/javascript" src="http://theme.codecampus.ru/plugin/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="http://theme.codecampus.ru/plugin/ckeditor/config.js"></script>

<script src="res/plugin/fileManager/fileManager.js" type="text/javascript"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<style>
    div .dt{font-weight: bold}
    div .dd{ padding-left: 25px}
    .hidden{display: none}
    #cloakingBox{width: 800px}
    .leftChild>div{
        float: left;
        margin-right: 10px;
        /*border: 1px solid black;*/
        padding: 5px;
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
                <li>
                    <a href="#seoBox" id="seoBtn" title="SEO">
                        <img src="<?= self::res('images/seo_32.png') ?>" alt="SEO" /><br /><span>SEO</span>
                    </a>
                </li>

                <li>
                    <a href="#cloakingBox" id="cloakingBtn" title="Клоакинг">
                        <img src="<?= self::res('images/seo_32.png') ?>" alt="Клоакинг" /><br /><span>Клоакинг</span>
                    </a>
                </li>
                <li>
                    <a href="#descrBox" id="descrBtn" title="Мини опинсание статьи">
                        <img src="<?= self::res('images/seo_32.png') ?>" alt="Описание" /><br /><span>Описание</span>
                    </a>
                </li>
            </ul>
        </div>


        <div class="content">
            <div class="leftChild">
                <div>
                    <div class="dt">Начальное изображение</div>
                    <div class="dd">
                        <a href="#" id="prevImgBtn">
                            <img src="<?= self::res('images/folder_16.png') ?>" alt="Выбрать" />
                            Выбрать
                        </a>
                        <a id="preImgUrl" href="#" style="display: none" target="_blank">
                            <img src="<?= self::res('images/file_16.png') ?>" alt="Посмотреть" />
                            Посмотреть
                        </a>
                    </div>
                </div>
                <div>
                    <div class="dt">Разделять описание</div>
                    <div class="dd">
                        <input type="checkbox" value="1" id="divArticle"/>
                    </div>
                </div>

                <div>
                    <div class="dt">Приват. доступ</div>
                    <div class="dd">
                        <input type="checkbox" value="1" id="isPrivate"/>
                    </div>
                </div>
            </div>
            <div class="clear"></div>

            <div>
                <textarea id="articleTxtId"><?= self::get('text') ?></textarea>
            </div>
        </div>

    </div>
</div>

<form class="hidden" id="seoBox">
    <div class="dt">Keywords</div>
    <div class="dd"><textarea name="keywords"></textarea></div>

    <div class="dt">Description</div>
    <div class="dd"><textarea rows="5" cols="30" name="descr"></textarea></div>

    <div class="dt">Img Url</div>
    <div class="dd"><input type="text" name="imgUrl"/></div>

    <div class="dt">Video Url</div>
    <div class="dd"><input type="text" name="videoUrl"/></div>
</form>

<div class="hidden" id="cloakingBox">
    <div class="boxmenu corners">
        <ul class="menu-items">
            <li>
                <a href="#load" id="getMainobjItemBtn" title="Load">
                    <img src="<?= self::res('images/refresh_32.png') ?>" alt="Load" /><br /><span>Load</span>
                </a>
            </li>
        </ul>
    </div>
    <textarea id="cloakingText"><?=self::get('cloakingText')?></textarea>
</div>
<div id="descrBox" class="hidden">
    <b>Мини опинсание статьи</b>
    <textarea name="miniDescrText" style="width: 400px; height: 200px"><?=self::get('miniDescrText')?></textarea>
</div>
<script type="text/javascript">
    
    var objItem = {
        contid: <?= self::get('contId') ?>,
        objItemId: <?= self::get('objItemId') ?>,
        objItemData: <?= self::get('objItemData') ?>,
        editor: null,
        prevImgUrl: '',
        cloakingEditor: null,
        seo: <?= self::get('seoData') ? self::get('seoData') : '{}' ?>
    }

    var contrName = objItem.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    
    objItem.saveBtnClick = function(){
        var data = {
            'objItem': objItem.editor.getData(),
            'contid': objItem.contid,
            'id': objItem.objItemId,
            'prevImgUrl': objItem.prevImgUrl,
            'cloakingText': jQuery('#cloakingText').val(),
            'miniDescrText': jQuery('#descrBox textarea[name="miniDescrText"]').val(),
			'divArticle': jQuery('#divArticle').attr("checked")=="checked" ? 1 : 0,
            seo: objItem.seo,
            isPrivate: jQuery('#isPrivate').attr("checked")=="checked" ? 1 : 0
        }
        HAjax.saveData({data: data, methodType: 'POST'});
        return false;
    }
    
    objItem.saveData = function(pData){
        if ( pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        alert('Данные успешно сохранены');
    }

    objItem.seoBtnBeforeClose = function(){
        var serialArr = jQuery('#seoBox').serializeArray();
        for( var i in serialArr ){
            objItem.seo[serialArr[i].name] = serialArr[i].value;
        }
        // func. objItem.setSeoClick
    }

    objItem.cloakingBtnBeforeShow = function(){
        objItem.cloakingEditor = CKEDITOR.replace( 'cloakingText', {toolbar: 'Article'} );
        (new CKEDITOR.focusManager( objItem.cloakingEditor )).focus();
        // func. objItem.cloakingBtnbeforeClose
    }

    objItem.cloakingBtnAfterClose = function(){
        objItem.cloakingEditor.destroy();
        delete objItem.cloakingEditor.content;
        // func. objItem.cloakingBtnbeforeClose
    }

    objItem.getMainobjItemBtnClick = function(){
        objItem.cloakingEditor.setData(objItem.editor.getData());
        // func. getMainobjItemBtnClick
    }
	
	function getContentCallBack(pFuncNum , pUrl){
		if ( pFuncNum == '25'){
            objItem.prevImgUrl = pUrl;
            jQuery('#preImgUrl').show().attr('href', pUrl);
        }else{
            CKEDITOR.tools.callFunction(pFuncNum, pUrl);
        }
		// func. getContentCallBack
	}
    
    objItem.prevImgBtnClick = function(){
        var urlWindow = utils.url({
            method: 'fileManager', 
            query: {CKEditorFuncNum: '25', type: 'img', id: objItem.objItemId}
        });
        var win = window.open( urlWindow, 'Выберите файл', 
             'width=800,height=600,scrollbars=yes,resizable=yes,'
            +'location=no,status=yes,menubar=yes');
		win.onload = function() {
            win.funcNameCallBack = getContentCallBack;
            win.callBackUsedData = {};
       };
		// func. prevImgBtnClick
    }
    
    fileManagerCallBack = function(pFuncNum, pUrl){
        getContentCallBack(pFuncNum, pUrl);
    }

    jQuery(document).ready(function(){
        // Кнопка Назад
        jQuery('#backBtn').attr('href', utils.url({}));
        jQuery('#saveBtn').click(objItem.saveBtnClick);
        
        jQuery('#prevImgBtn').click(objItem.prevImgBtnClick);
        jQuery('#seoBtn').fancybox({
            beforeClose: objItem.seoBtnBeforeClose
        });
        jQuery('#cloakingBtn').fancybox({
            beforeShow: objItem.cloakingBtnBeforeShow,
            afterClose: objItem.cloakingBtnAfterClose
        });
        jQuery('#descrBtn').fancybox({
            openEffect	: 'elastic',
            closeEffect	: 'elastic',
            maxWidth: 450
        });

        jQuery('#getMainobjItemBtn').click(objItem.getMainobjItemBtnClick);

        
        HAjax.create({
            saveData: objItem.saveData
        });
        
        // Инициализация менеджера файлов
        var id = objItem.objItemId;
        //fileManager.initCkEditor(urlParam);
        
        if ( objItem.objItemData['prevImgUrl']){
            getContentCallBack('25', objItem.objItemData['prevImgUrl']);
        }

        unserializeForm('#seoBox', objItem.seo);
		
		if ( objItem.objItemData['divArticle'] == '1' ){
            jQuery('#divArticle').attr("checked", 'checked');
        }

        if ( objItem.objItemData['isPrivate'] == '1' ){
            jQuery('#isPrivate').attr("checked", 'checked');
        }


        CKEDITOR.config.filebrowserBrowseUrl = utils.url({method: 'fileManager', query: {type: 'file', id: id}});

        CKEDITOR.config.filebrowserImageBrowseUrl = utils.url({method: 'fileManager', query: {type: 'img', id: id}});
        //CKEDITOR.config.filebrowserImageBrowseUrl = 'http://files.codecampus.ru/?group=p'+id+'&subgroup=article&profile=article&single=1';//utils.url({method: 'fileManager', query: {type: 'img', id: id}});

        CKEDITOR.config.filebrowserFlashBrowseUrl = utils.url({method: 'fileManager', query: {type: 'flash', id: id}});
        CKEDITOR.config.filebrowserUploadUrl = 'res/plugin/kcfinder/upload.php?type=files';
        CKEDITOR.config.filebrowserImageUploadUrl = 'res/plugin/kcfinder/upload.php?type=images';
        CKEDITOR.config.filebrowserFlashUploadUrl = 'res/plugin/kcfinder/upload.php?type=flash';
        
        objItem.editor = CKEDITOR.replace( 'articleTxtId', {
			toolbar: 'Article',
			height: '550px'
		} );

    });
</script>