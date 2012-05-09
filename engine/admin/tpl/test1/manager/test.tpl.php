<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Flame CMS</title>

        <link type="text/css" rel="stylesheet" href="<?= $this->res('css/main.css')?>" />
        
        <link type="text/css" rel="stylesheet" href="/res/plugin/jquery/css/jquery-ui-1.8.14.css"/>
        <script type="text/javascript" src="/res/plugin/jquery/jquery-1.5.1.min.js"></script>
        <script type="text/javascript" src="/res/plugin/jquery/jquery.json-2.2.min.js"></script>
        <script type="text/javascript" src="/res/plugin/jquery/jquery-ui-1.8.14.min.js"></script>
        
        
        <script type="text/javascript" src="/res/plugin/classes/tables.js"></script>

        <? core\classes\html\element::printCSS(); ?>
        
        
        <link href="res/icons/icon128x128.png" rel="icon"/>
	<link href="res/icons/icon16x16.png" rel="shortcut icon" />
        
		
        <script type="text/javascript">
            //subnav top menu
            $(document).ready(function(){
			   
                $("#top_menu_content li a").hover(function() { //When trigger is clicked...
		
                    //Following events are applied to the subnav itself (moving subnav up and down)
                    $(this).parent().find("ul.subnav").slideDown('fast').show(); //Drop down the subnav on click

                    $(this).parent().hover(function() {
                    }, function(){
                        $(this).parent().find("ul.subnav").slideUp('slow'); //When the mouse hovers out of the subnav, move it back up
                    });

                    //Following events are applied to the trigger (Hover events for the trigger)
                }).hover(function() {
                    $(this).addClass("subhover");//On hover over, add class "subhover"
                }, function(){	//On Hover Out
                    $(this).removeClass("subhover");//On hover out, remove class "subhover"
                });

            });
            $(function() {
                $("#tabs").tabs({
                    cookie: {
                        // store cookie for a day, without, it would be a session cookie
                        expires: 1
                    }
                });
            });

        </script><!-- end javascripts -->
        
        
        
        
        
        <!-- DXHTML COMPONENT -->
        <link href="res/plugin/dhtmlxTree/codebase/dhtmlxtree.css" rel="stylesheet" type="text/css"/>
        <link href="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/dhtmlxgrid.css" rel="stylesheet" type="text/css"/>
        <link href="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css" rel="stylesheet" type="text/css"/>

        <script src="res/plugin/dhtmlxTree/codebase/dhtmlxcommon.js"></script>


        <script src="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>
        <script src="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
        <!--<script src="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js"></script>-->
        <script src="res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_tree.js"></script>

        <script src="res/plugin/dhtmlxTree/codebase/dhtmlxtree.js"></script>
        <script src="res/plugin/dhtmlxTree/codebase/ext/dhtmlxtree_json.js"></script>
        <!-- END DXHTML COMPONENT -->
        
        <style>
        .containerTableStyle { overflow : auto; position:relative; top:0; font-size : 12px;
            background-color:#f5f5f5;border :1px solid Silver;
        }
</style>


    </head>
    <body>

    <body>
        <!-- start top menu -->
        <div id="top_menu">
            <div id="top_menu_content">

                <!-- start logo -->
                <img src="<?= $this->res('images/logo.png')?>" width="160" height="42" alt="adminz" class="float_left margin_10_0" /><!-- end logo -->

                <!-- start menu -->
                <ul><? 
                    core\classes\html\element::printMenu(self::get('mainmenu'));
                ?></ul>
                <!-- end menu -->

                <div class="float_right margin_0_20">
                    <a href="dashboard.html"><img src="<?= $this->res('images/settings.png')?>" width="24" height="24" alt="settings" /></a><br />

                    <a href="index.html"><img src="<?= $this->res('images/logout.png')?>" width="24" height="24" alt="logout" /></a></div>
                <div id="top_menu_info">
                    <img src="<?= $this->res('images/avatar.png')?>" width="32" height="32" alt="avatar" /><br />
			Hello, Stefan</div>
            </div>
            <div id="top_menu_button" class="down_arrow corners_bottom">&nbsp;</div>
        </div><!-- end top menu -->

        <!-- start container -->
        <div class="container">
            
            
            
            
            
            
            
            
            
            
            
        <div id="gridbox" style="width: 600px; height: 270px; background-color:white;"></div>
        <div id="treeboxbox_tree" style="width:300px;height:200px"></div>
        <script>

            tree = new dhtmlXTreeObject("treeboxbox_tree", "100%", "100%", 0);
            tree.setImagePath("res/plugin/dhtmlxTree/codebase/imgs/csh_vista/");
            tree.setSkin("dhx_skyblue");
            tree.loadJSONObject(<?= self::get('compTree') ?>);
            mygrid = new dhtmlXGridObject('gridbox');
            mygrid.setImagePath("res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/imgs/");



            mygrid.setSubTree(tree, 3);
            
            mygrid.setHeader(",Название,Сист. назв,Компонент");
            //mygrid.attachHeader("#text_filter,#select_filter,#numeric_filter");
            mygrid.setInitWidths("32,*,150,150");
            mygrid.setColAlign("left,left,right,right");
            mygrid.setColTypes("img,ed,ed,stree");
            
            mygrid.init();
            mygrid.setSkin("dhx_skyblue");
            mygrid.loadXML("res/plugin/dhtmlxGrid/dhtmlxGrid/codebase/test.xml");


        </script>

        
        
        
        
        
                <div class="clear"></div>

                <!-- begin footer -->
                <div id="footer">
                    <!-- begin separator -->
                    <hr /><!-- end separator -->
                    <div class="float_left">ООО "Фумо Фумо" &copy; 2010-<?= date('Y') ?>. Все права защищены.</div>
                <div class="float_right">Разработано в <a href="http://fumofumo.ru/" target="_blank">ООО "Фумо Фумо"</a></div>
                <div class="clear"></div>
            </div><!-- end footer -->

        </div><!-- end container -->

        <!-- begin fixed left panel -->

        <div class="fixed_left_panel corners_right">
            <div class="hidden_left_div">
                <ul class="list_left">
                    <li><a href="javascript:();">5 new orders</a></li>
                    <li><a href="javascript:();">2 new comments</a></li>
                    <li><a href="javascript:();">1 system error</a></li>
                    <li><a href="javascript:();">1 new message</a></li>

                    <li><a href="javascript:();">8 new subscribers</a></li>
                    <li><a href="javascript:();">4 new articles</a></li>
                    <li><a href="javascript:();">2 new topics</a></li>
                    <li><a href="javascript:();">6 new posts</a></li>
                </ul></div>
            <div class="show_left_div right_arrow"></div>
            <div class="clear"></div></div><!-- end fixed left panel -->

        <!-- begin fixed right panel -->
        <div class="fixed_right_panel fixed_right_panel_hide corners_left">
            <div class="hidden_right_div">
                <div class="date float_left">19 / July / 2010</div>
                <div class="time float_left">18:06</div>
                <div class="clear"></div>
                <div class="all_appointments"><a href="javascript:();">View all appointments</a></div>

                <div class="today_appointments">No appointments today</div>
                <div id="appointments"></div>
            </div>
            <div class="show_right_div left_arrow"></div>
            <div class="clear"></div>
        </div><!-- end fixed right panel -->

        <!-- start pretty photo image viewer -->
        <script type="text/javascript" charset="utf-8">
            $(document).ready(function(){
                //$(".imgtoolbox a[rel^='prettyPhoto']").prettyPhoto({theme:'facebook'});
            });
        </script><!-- end pretty photoo image viewer -->


    </body>
</html>
