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
                </a>
            </div>

			<div class="dt">Youtube ссылка</div>
            <div class="dd">
                <?=self::text('name="videoUrl"', self::get('videoUrl'));?>
            </div>
			
			<div class="dt">Заголовок</div>
            <div class="dd">
                <?=self::text('name="caption"', self::get('caption'));?>
            </div>
			
			<div class="dt">Текст</div>
            <div class="dd">
                <textarea name="textDesc" style="width: 400px; height: 200px"><?=self::get('textDesc')?></textarea>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    var reviewData = {
        contid: <?= self::get('contId') ?>,
		objItemId: <?= self::get('objItemId') ?>,
		prevImgUrl: ''
    };

    var contrName = reviewData.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);

    var reviewMvc = (function () {
        var options = {};
        var tree = {};

        // Клик по кноке Сохранить
        function saveBtnClick() {
            var sel = tree.compcont.getAllCheckedBranches();
            var propData = $('#' + options.propBox).serialize();

            HAjax.saveData({
                data:'sel=' + sel + '&'+propData,
                methodType:'POST'
            });
            return false;
            // func. saveBtnClick
        }
		
		function fileManagerCallBack(pFuncNum, pUrl){

			if ( pFuncNum == '25'){
				reviewData.prevImgUrl = pUrl;
				$('#'+options.preImgUrl).show().attr('href', pUrl);
			}/*else{
				CKEDITOR.tools.callFunction(pFuncNum, pUrl);
			}*/
			// func. fileManagerCallBack
		}

        // callback сохранения данных
        function saveDataSuccess(pData) {
            if (pData['error']) {
                alert(pData['error']['msg']);
                return;
            }

            alert('Данные успешно сохранены');
            // func. saveDataSuccess
        }
		
		function prevImgBtnClick(){
			var urlWindow = utils.url({
				method: 'fileManager', 
				query: {CKEditorFuncNum: '25', type: 'img', id: reviewData.objItemId}
			});
			window.open( urlWindow, 'Выберите файл', 
				'width=800,height=600,scrollbars=yes,resizable=yes,'
				+'location=no,status=yes,menubar=yes');
			return false;
			// func. prevImgBtnClick
		}

        function init(pOptions) {
            options = pOptions;

            // Кнопка Сохранить
            $('#'+options.saveBtn).click(saveBtnClick);
            HAjax.create({
                saveData:saveDataSuccess
            });
			
			$('#'+options.prevImgBtn).click(prevImgBtnClick);
			$('#'+options.backBtn).attr('href', utils.url({}));

            // func. init
        }

        return {
            init:init,
			fileManagerCallBack: fileManagerCallBack
        }
        // func. reviewMvc
    })();
	
	function fileManagerCallBack(pFuncNum, pUrl){
		reviewMvc.fileManagerCallBack(pFuncNum, pUrl);
		// func fileManagerCallBack
	}

    $(document).ready(function () {
        reviewMvc.init({
            backBtn: 'backBtn',
            saveBtn: 'saveBtn',
			prevImgBtn: 'prevImgBtn',
			preImgUrl: 'preImgUrl'
        });
    }); // $(document).ready

</script>