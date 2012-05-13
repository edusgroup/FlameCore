<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<script type="text/javascript" src="res/plugin/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="res/plugin/ckeditor/config.js"></script>

<script src="res/plugin/fileManager/fileManager.js" type="text/javascript"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<style>
    div .dt{font-weight: bold}
    div .dd{ padding-left: 25px}
    div.hidden{display: none}
    #cloakingBox{width: 800px}
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

            <div class="dt">Начальное изображение</div>
            <div class="dd">
                <a href="#" id="prevImgBtn">
                    <img src="<?= self::res('images/folder_16.png') ?>" alt="Выбрать" />
                    Выбрать
                </a>
                <a id="preImgUrl" href="#" style="display: none" target="_blank">
                    <img src="<?= self::res('images/file_16.png') ?>" alt="Посмотреть" />
                    Посмотреть
                </a><br />
            </div>

            <div>
                <textarea id="article"><?= self::get('text') ?></textarea>
            </div>
        </div>

    </div>
</div>
<div class="hidden" id="seoBox">
    <div class="dt">Keywords</div>
    <div class="dd"><textarea name="keywords"></textarea></div>

    <div class="dt">Описание</div>
    <div class="dd"><textarea rows="5" cols="30" name="descr"></textarea></div>
</div>
<div class="hidden" id="cloakingBox">
    <div class="boxmenu corners">
        <ul class="menu-items">
            <li>
                <a href="#load" id="getMainArticleBtn" title="Load">
                    <img src="<?= self::res('images/refresh_32.png') ?>" alt="Load" /><br /><span>Load</span>
                </a>
            </li>
        </ul>
    </div>
    <textarea id="cloakingText"><?=self::get('cloakingText')?></textarea>
</div>
<div id="descrBox" class="hidden">
    <b>Мини опинсание статьи</b>
    <textarea name="miniDescrText" style="width: 400px; height: 200px"><?=self::get('miniDescrText')?></textarea></div>
<script type="text/javascript">
    
    var article = {
        contid: <?= self::get('contId') ?>,
        articleId: <?= self::get('articleId') ?>,
        articleData: <?= self::get('articleData') ?>,
        editor: null,
        prevImgUrl: '',
        cloakingEditor: null
    }
    
    var contrName = article.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    
    article.saveBtnClick = function(){
        var data = {
            'article': article.editor.getData(),
            'contid': article.contid,
            'id': article.articleId,
            'prevImgUrl': article.prevImgUrl,
            'seoKeywords': article.articleData['seoKeywords'],
            'seoDescr': article.articleData['seoDescr'],
            'cloakingText': $('#cloakingText').val(),
            'miniDescrText': $('#descrBox textarea[name="miniDescrText"]').val()
        }
        HAjax.saveData({data: data, methodType: 'POST'});
        return false;
    }
    
    article.saveData = function(pData){
        if ( pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        alert('Данные успешно сохранены');
    }

    article.seoBtnBeforeClose = function(){
        var $seoBox = $('#seoBox');
        article.articleData['seoKeywords'] = $seoBox.find('textarea[name="keywords"]').val();
        article.articleData['seoDescr'] = $seoBox.find('textarea[name="descr"]').val();
        // func. article.setSeoClick
    }

    article.cloakingBtnBeforeShow = function(){
        article.cloakingEditor = CKEDITOR.replace( 'cloakingText', {toolbar: 'Article'} );
        (new CKEDITOR.focusManager( article.cloakingEditor )).focus();
        // func. article.cloakingBtnbeforeClose
    }

    article.cloakingBtnAfterClose = function(){
        article.cloakingEditor.destroy();
        delete article.cloakingEditor.content;
        // func. article.cloakingBtnbeforeClose
    }

    article.getMainArticleBtnClick = function(){
        article.cloakingEditor.setData(article.editor.getData());
        // func. getMainArticleBtnClick
    }
    
    article.prevImgBtnClick = function(){
        var urlWindow = utils.url({
            method: 'fileManager', 
            query: {CKEditorFuncNum: '25', type: 'img', id: article.articleId}
        });
        window.open( urlWindow, 'Выберите файл', 
             'width=800,height=600,scrollbars=yes,resizable=yes,'
            +'location=no,status=yes,menubar=yes');
    }
    
    fileManagerCallBack = function(pFuncNum, pUrl){
        if ( pFuncNum == '25'){
            article.prevImgUrl = pUrl;
            $('#preImgUrl').show().attr('href', pUrl);
        }else{
            CKEDITOR.tools.callFunction(pFuncNum, pUrl);
        }
    }

    $(document).ready(function(){
        // Кнопка Назад
        $('#backBtn').attr('href', utils.url({}));
        $('#saveBtn').click(article.saveBtnClick);
        
        $('#prevImgBtn').click(article.prevImgBtnClick);
        $('#seoBtn').fancybox({
            beforeClose: article.seoBtnBeforeClose
        });
        $('#cloakingBtn').fancybox({
            beforeShow: article.cloakingBtnBeforeShow,
            afterClose: article.cloakingBtnAfterClose
        });
        $('#descrBtn').fancybox({
            openEffect	: 'elastic',
            closeEffect	: 'elastic',
            maxWidth: 450
        });

        $('#getMainArticleBtn').click(article.getMainArticleBtnClick);

        
        HAjax.create({
            saveData: article.saveData
        });
        
        // Инициализация менеджера файлов
        var id = article.articleId;
        //fileManager.initCkEditor(urlParam);
        
        if ( article.articleData['prevImgUrl']){
            fileManagerCallBack('25', article.articleData['prevImgUrl']);
        }
        var $seoBox = $('#seoBox');
        $seoBox.find('textarea[name="keywords"]').val(article.articleData['seoKeywords']);
        $seoBox.find('textarea[name="descr"]').val(article.articleData['seoDescr']);

        CKEDITOR.config.filebrowserBrowseUrl = utils.url({method: 'fileManager', query: {type: 'file', id: id}});
        CKEDITOR.config.filebrowserImageBrowseUrl = utils.url({method: 'fileManager', query: {type: 'img', id: id}});
        CKEDITOR.config.filebrowserFlashBrowseUrl = utils.url({method: 'fileManager', query: {type: 'flash', id: id}});
        CKEDITOR.config.filebrowserUploadUrl = 'res/plugin/kcfinder/upload.php?type=files';
        CKEDITOR.config.filebrowserImageUploadUrl = 'res/plugin/kcfinder/upload.php?type=images';
        CKEDITOR.config.filebrowserFlashUploadUrl = 'res/plugin/kcfinder/upload.php?type=flash';
        
        article.editor = CKEDITOR.replace( 'article',{toolbar: 'Article'} );

    });
</script>