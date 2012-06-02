<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<script src="res/plugin/jquery/jquery.cookie.js" type="text/javascript"></script>
<div class="column">
    <div class="panel corners">
        <div class="content" id="mainpanel">
            <div></div>Сайт: <?=self::select(self::get('siteList'), 'name="siteList"')?></div>
            <div><input type="button" value="Выбрать" id="chooseBtn" /></div>

        </div>
    </div>
</div>


<script type="text/javascript">


    var siteMvc = (function () {
        var options = {};

        function siteListChange(pObj){
            var elemId = '#'+options.mainPanel+' select[name="'+options.siteName + '"]';
            var val = $(elemId).val();
            $.cookie('siteName', val);
            utils.go(utils.url({type: 'manager', contr: 'action'}))
            // func. siteListChange
        }

        function init(pOptions) {
            options = pOptions;
            $('#'+options.chooseBtn).click(siteListChange);
            // func. init
        }

        return {
            init:init
        }
        // class siteMvc
    })();

    $(document).ready(function () {

        siteMvc.init({
            mainPanel:'mainpanel',
            siteName: 'siteList',
            chooseBtn: 'chooseBtn'
        });
    }); // $(document).ready

</script>