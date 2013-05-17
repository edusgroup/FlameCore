<link   href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<!--<script src="res/plugin/classes/html.js" type="text/javascript"></script>-->
<style>
    .editor{ width: 600px; height: 300px}
    div .dt{font-weight: bold}
    div .dd{ padding-left: 25px}
</style>

<!-- start panel right column -->
<div class="column" >
    <!-- start panel right panel -->
    <div class="panel corners">
        <!-- start panel right title -->
        <div class="title corners_top">
            <div class="title_element">

                <a style="margin-left: 10px" href="" title="В начало">
                    <img src="<? self::res('images/home_16x16.png') ?>" alt="В начало" width="16" height="16" alt="В начало"/>
                    В начало /
                </a>
                <span id="history">{Hisotry}</span>
            </div>
        </div><!-- end title -->
        <!-- start panel right content -->
        <div class="content" id="mainpanel">

            <div class="boxmenu corners">
                <ul class="menu-items">

                    <li>
                        <a href="#back" id="backBtn" title="Назад">
                            <img src="<?= self::res('images/back_32.png') ?>" alt="Назад" /><span>Назад</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=wareframe" id="" title="WF">
                            <img src="<?= self::res('images/wf_32.png') ?>" alt="WF" /><span>Wareframe</span>
                        </a>
                    </li>

                    <li>
                        <a href="/?$t=manager&$c=complist" id="" title="Component List">
                            <img src="<?= self::res('images/refresh_32.png') ?>" alt="Component List" /><span>Component List</span>
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
                    <?
                    $engineList = self::get('engineList');
                    foreach ($engineList as $item) {
                        $id = $item['id'];
                        echo '<div class="dt">' . $item['userAgent'] . '</div> ';
                        echo '<div class="dd">';
                        echo '<div>CrawlDelay</div>';
                        echo '<div>' . self::text('name="crawlDelay[' . $id . ']"', $item['crawlDelay']) . '</div>';
                        echo '<div>CleanParam</div>';
                        echo '<div>' . self::text('name="cleanParam[' . $id . ']"', $item['cleanParam']) . '</div>';
                        echo '</div>';
                    } // foreach
                    ?>

                    <div>Данные файла robots.txt в ручном режиме</div>
                    <div>
                        <textarea class="editor" name="robotsText"><?= self::get('robotsText') ?></textarea>
                    </div>
                </form>
            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<script type="text/javascript">
    var contrName = 'robots';
    var callType = 'utils';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
   
    var robotsData = {
        
    }
    
    var robotsMvc = (function(){
        var options = {};
        
        // Клик по кноке Сохранить
        function saveBtnClick(pEvent){
            //var data = $(options.editor).val();
            var data = $(options.mainForm).serialize();
            HAjax.saveData({data: data, methodType: 'POST'});
            // func. saveBtnClick
        }
        
        // callback сохранения данных
        function saveDataSuccess(pData){
            if ( pData['error'] ){
                alert(pData['error']['msg']);
                return;
            }
            alert('Данные успешно сохранены');
        }
        
        function init(pOptions){
            options = pOptions;
            
            // Кнопка Назад
            $(options.backBtn).attr('href', utils.url({
                contr:'tree'
            }));
            // Кнопка Сохранить
            $(options.saveBtn).click(saveBtnClick);
            
            HAjax.create({
                saveData: saveDataSuccess
            });
            // func. init
        }
        
        return {
            init: init
        }  
    })();
    
    $(document).ready(function(){
        robotsMvc.init({
            backBtn: '#backBtn',
            saveBtn: '#saveBtn',
            mainForm: '#mainForm'
            //editor: 'textarea[name="editor"]'
        });         
    }); // $(document).ready

</script>