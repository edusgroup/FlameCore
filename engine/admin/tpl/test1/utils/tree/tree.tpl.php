<link   href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script type="text/javascript" src="/res/plugin/fancybox/source/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="/res/plugin/fancybox/source/jquery.fancybox.css" media="screen" />

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<!--<script src="res/plugin/classes/html.js" type="text/javascript"></script>-->
<style>
    div.bothPanel{float: left; margin-right: 10px;}
    br.clearBoth { clear:both;}
    div.buttonPanel{height: 30px}
    div.treePanel{height:218px;background-color:#f5f5f5;border :1px solid Silver; overflow:auto; width: 200px;}
    #secondPanel {display: none; width: 400px}
    div .items .dt{font-weight: bold}
    div .items .dd{ padding-left: 25px}

    div.submitDiv{margin-top: 20px}
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
                        <a href="/?$t=manager&$c=wareframe" id="" title="WF">
                            <img src="<?= self::res('images/wf_32.png') ?>" alt="WF" /><span>Wareframe</span>
                        </a>
                    </li>
                    <li>
                        <a href="/?$t=manager&$c=complist" id="" title="Component List">
                            <img src="<?= self::res('images/refresh_32.png') ?>" alt="Component List" /><span>Component List</span>
                        </a>
                    </li>
                </ul>
            </div>


            <div class="content" id="mainpanel">
                @TODO CREATE SHOW MSGBOX<br/>


                <div class="bothPanel">
                    <div class="treePanel" id="utilsTreeBox"></div>
                </div>

                <div class="bothPanel" id="secondPanel">
                    test
                </div>

                <br class="clearBoth"/>

            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->

<script type="text/javascript">
    var contrName = 'tree';
    var callType = 'utils';
    utils.setType(callType);
    utils.setContr(contrName);
    HAjax.setContr(contrName);
    HAjax.setType(callType);
   
    var treeData = {
        tree:{
            utils: <?=self::get('utilsTree')?>
        }
    }
    
    var treeMvc = (function(){
        // Все dhtml tree в этом буффере
        var tree = {};
        
        function utilsTreeDbClick(pItemId, pTree){
            var url = utils.url({contr: pItemId});
            utils.go(url);
            // func. utilsTreeClick
        }
        
        function init(){
            var utilsTree = {
                tree:   { id:'utilsTreeBox', json: treeData.tree.utils },
                dbClick: utilsTreeDbClick
            }
            dhtmlxInit.init({'utils': utilsTree});
            tree.utils  = dhtmlxInit.tree['utils'];
            // func. init
        }
        
        return {
            init: init
        }  
    })();
    
    $(document).ready(function(){
        treeMvc.init();         
    }); // $(document).ready

</script>