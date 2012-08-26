<script src="res/plugin/classes/utils.js" type="text/javascript"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<link rel="stylesheet" href="http://theme.codecampus.ru/plugin/jqui/custom/css/smoothness/jquery-ui-1.8.22.custom.css">
<script type="text/javascript" src="http://theme.codecampus.ru/plugin/jqui/custom/js/jquery-ui-1.8.22.custom.min.js"></script>

<style>
    div .dt{font-weight: bold}
    div .dd{ padding-left: 25px}
    div.hidden{display: none}
    #cloakingBox{width: 800px}
</style>

<style>
    span.word:hover {
        cursor: pointer;
    }

    span.word:hover {
        color: red;
    }

    span.word.selected{
        font-weight: bold;
        color: red;
    }

    span.sentence{
    }

    span.sentence:hover{
        text-decoration: underline;
        /*background-position: right center;
          background-image: url(/res/images/info.png);
          background-repeat: no-repeat;
          padding-right: 18px;
          margin-right: 18px;*/
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
            <div>
                <input type="button" value="Clear Sel" id="clearSelectedBtn"/>
                <input type="button" value="Set Rule" id="setRuleBtn"/>
            </div>
            <div id="htmlDataBox" style=""><?=self::get('engartText')?></div>


        </div>
    </div>
</div>

<div id="ruleDlg" style="display: none">
    <div id="tabs">
        <ul>
            <li><a href="#tabs-type">Типы Слов</a></li>
            <li><a href="#tabs-settings">Осн. настр.</a></li>
            <li><a href="#tabs-dynamic">Расширен. настр</a></li>
        </ul>
        <div id="tabs-type"></div>
        <div id="tabs-settings">
            <span></span>
            <table>
                <tr>
                    <td>Автопоиск прав.</td>
                    <td><input type="checkbox" name="autorule"/></td>
                </tr>
                <tr>
                    <td>Часть речи</td>
                    <td>
                        <select id="classWord">
                            <option value="none">{Выбрать}</option>
                            <option value="noun">Существительное</option>
                            <option value="verb">Глагол</option>
                            <option value="phrasalverb">Фраз. глагол</option>
                            <option value="particle">Частица</option>
                            <option value="idiom">Идиома</option>
                            <option value="infinitiv">Инфинитив</option>
                            <option value="conjunction">Союз</option>
                            <option value="pronoun">Местоимение</option>
                            <option value="adjective">Прилогательное</option>
                            <option value="preposition">Предлог</option>
                            <option value="adverb">Наречие</option>
                            <option value="gerund">Герундий</option>
                            <option value="participle">Причастие</option>
                            <option value="determine">Определитель</option>
                            <option value="interjection">Междометье</option>
                            <option value="numeral">Чистительное</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Транскрип.</td>
                    <td><input type="text" name="transcr"/></td>
                </tr>
                <tr>
                    <td>Перевод.</td>
                    <td><input type="text" name="translate"/></td>
                </tr>
                <tr>
                    <td>Звучание.</td>
                    <td>// Сделать<input type="hidden" name="soundFile" value="0"/></td>
                </tr>
            </table>
            <table style="width: 100%">
                <tr>
                    <td colspan="2">Комментарий.</td>
                </tr>
                <tr>
                    <td><textarea name="commnet" style="width: 100%"></textarea></td>
                </tr>
            </table>

        </div>
        <div id="tabs-dynamic"></div>
    </div>
</div>

<script type="text/javascript">
    var engartData = {
        contid: <?= self::get('contId') ?>,
        itemObjId: <?= self::get('objItemId') ?>
    };

    var contrName = engartData.contid;
    var callType = 'comp';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);

    var engartMvc = (function(){
        var options = {};

        var selectedBuff = [];

        function clearSelectedBtnClick(){
            selectedBuff = [];
            $(options.htmlDataBox+' span.selected').removeClass('selected');
            // func. clearSelectedBtnClick
        }

        function mouseMoveBox(pEvent){
            //console.log(pEvent);
            //console.log(pEvent.target.className);
            /*var isSentence = $(pEvent.target.parentElement).hasClass('sentence');
               if ( isSentence ){

               }*/
            if ( $(pEvent.target).hasClass('word') ){

            }
            // func. mouseMoveBox
        }

        function mouseClickBox(pEvent){
            if ( $(pEvent.target).hasClass('word') ){
                wordClick(pEvent.target);
            }else
            if ( $(pEvent.target).hasClass('sentence') ){
                console.log('sentence');
            }
            // func. mouseClickBox
        }

        function wordClick(pObj){
            var num = $(pObj).attr('num');
            // Есть ли в буффере
            var index = $.inArray(num, selectedBuff );
            if ( index != -1 ){
                $(pObj).removeClass('selected');
                selectedBuff.splice( index, 1 );
            }else{
                $(pObj).addClass('selected');
                selectedBuff.push(num);
            }
            // func. wordClick
        }

        function setRuleBtnClick(){
            var htmlTmpBuff = '<table><tr><td>Основ</td><td>Название</td></tr>';
            for( var i in selectedBuff ){
                var num = selectedBuff[i];
                var name = $(options.htmlDataBox + ' span.word[num='+num+']:first').html();
                htmlTmpBuff += '<tr><td><input type="checkbox" name="w'+num+'"/></td><td>'+name+'</td></tr></p>';
            }
            htmlTmpBuff += '</table>';
            $(options.tabsType).html(htmlTmpBuff);

            $.fancybox.open([{
                href: '#ruleDlg',
                width: 500,
                height: 300,
                autoSize: false
            }]);
            // func. setRuleBtnClick
        }

        function init(pOptions){
            options = pOptions;

            $(options.htmlDataBox).mousemove(mouseMoveBox).click(mouseClickBox);
            $(options.clearSelectedBtn).click(clearSelectedBtnClick);
            $(options.setRuleBtn).click(setRuleBtnClick);
            $(options.tabsBox).tabs();

            // func. init
        }

        return {
            init: init
        }
    })();

    $(document).ready(function(){
        engartMvc.init({
            htmlDataBox: '#htmlDataBox',
            setRuleBtn: '#setRuleBtn',
            clearSelectedBtn: '#clearSelectedBtn',
            tabsType: '#tabs-type',
            tabsBox: '#tabs'
        });
    }); // $(document).ready

</script>