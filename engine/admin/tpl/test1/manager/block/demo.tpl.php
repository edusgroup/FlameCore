<link   href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
<script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>

<script src="res/plugin/classes/utils.js" type="text/javascript"></script>
<script src="res/plugin/classes/html.js" type="text/javascript"></script>

<style type="text/css">
    .bold {font-weight: bold}
    .vmiddle{vertical-align: middle; height: 40px}
    .vmiddle img{vertical-align: middle}
    .treeBlock{vertical-align:top; width:200px; height:218px;background-color:#f5f5f5;border :1px solid Silver;; overflow:auto;}
    img.img_button{cursor: pointer}
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
                        <a href="#back" id="back" title="Назад">
                            <img src="<?= self::res('images/back_32.png') ?>" alt="Назад" /><span>Назад</span>
                        </a>
                    </li>
                    <li>
                        <a href="#save" id="saveTpl" title="Сохранить">
                            <img src="<?= self::res('images/save_32.png') ?>" alt="Сохранить" /><span>Сохранить</span>
                        </a>
                    </li>
                </ul>
            </div>


            <div class="content">
                
            </div><!-- end panel right content -->
        </div><!-- end panel right content -->
    </div><!-- end panel right panel -->
</div><!-- end panel right column -->



<script type="text/javascript">
    
    var ini = {
        buff: {},
        get: function(pName){
            return this.buff[pName] ? this.buff[pName] : null;
        },
        set: function(pName, pValue){
            return this.buff[pName] = pValue;
        }
    }

    var controllerName = 'compitem/';



    $(document).ready(function(){
       
    });
    
    
</script>