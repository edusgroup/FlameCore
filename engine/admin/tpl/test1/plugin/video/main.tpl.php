
<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>
    <!-- Basic Page Needs
   ================================================== -->
    <meta charset="utf-8">
    <title>Explorer</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Mobile Specific Metas
   ================================================== -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- CSS
   ================================================== -->
    <link rel="stylesheet" href="http://theme.codecampus.ru/plugin/grid960/css/base/base.css">
    <link rel="stylesheet" href="http://theme.codecampus.ru/plugin/grid960/css/base/skeleton.css">
    <link rel="stylesheet" href="http://theme.codecampus.ru/plugin/grid960/css//base/layout.css">

    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Favicons
     ================================================== -->
    <link rel="shortcut icon" href="/res/img/favicon.ico">
    <link rel="apple-touch-icon" href="/res/img/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/res/img/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/res/img/apple-touch-icon-114x114.png">

    <script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js'></script>
    <script type="text/javascript" src="/res/plugin/jquery/jquery.cookie.js"></script>


    <script src="http://files.codecampus.ru/res/js/min/mvc-api.js" type="text/javascript"></script>



    <!--<script src="http://theme.codecampus.ru/plugin/SWFUpload_v2.2.0.1/swfupload.js" type="text/javascript"></script>
    <script src="/res/js/min/mvc-files.js" type="text/javascript"></script>


    <link rel="stylesheet" type="text/css" href="/res/css/main.css" />-->

    <style>
        #mainContainer{
            margin-top: 40px;
        }

        #videoBox input{
            display: inline;
        }

        #videoBox .item{
            clear: both;
            margin-bottom: 10px;
        }

        #videoBox .item input{
            padding: 3px 4px;
            margin: 0 8px 0 0;
        }

        #videoBox .item img{
            margin-right: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container" id="mainContainer">

        <input type="button" value="Выбрать" id="selectBtn"/>
        <input type="button" value="Сохранить" id="saveBtn"/>
        <ul id="videoBox"></ul>
    </div>
    <div class="clearfix"></div>

    <script>

        var videoData = {
            objItemId: <?=self::get('id')?>,
            list:{},
            videoList: <?=self::get('videoList', '{}')?>
        }

        var videoMvc = (function(){
            var isSingleChoose = false;
            var currentId = null;
            var newIdNum = 0;

            // Флага на закрытие окна
            var isCloseWindow = false;

            function isAddNewItem(pDomObj){
                var $parent = jQuery(pDomObj).parents('.item:first');
                var isNew = $parent.attr('data-new');
                if ( isNew == 'true' ){
                    $parent.removeAttr('data-new');
                    addNewLine('n'+newIdNum++, true);
                }
                // func. isAddNewItem
            }

            function urlTextKeyPress(pEvent){
                isAddNewItem(pEvent.target);
                var id = jQuery(pEvent.target).parent().attr('id');
                if (videoData.list[id]['type'] != 'new'){
                    videoData.list[id]['type'] = 'edit';
                }
                // func. urlCBChange
            }


            function addNewLine(pId, isNew){
                isNew = isNew == undefined ? '' : 'data-new="true"';

                var selectObj = isSingleChoose ? '<input type="radio" name="id"/>' : '<input type="checkbox" name="id"/>';

                jQuery('#videoBox').append('<li class="item" '+isNew+' id="'+pId+'">' + selectObj +
                    '<input data-type="img" type="button" value="Изображение"/>' +
                    '<input type="text" name="url" onkeypress="videoMvc.urlTextKeyPress(event)"/>' +
                    '<img data-type="rm" src="/res/theme/test1/site/images/del_16.png"/>' +
                    '</li>');

                videoData.list[pId] = {id:''};
                videoData.list[pId]['type'] = isNew ? 'new' : 'none';
                // func. addNewLine
            }

            function videoBoxClick(pEvent){
                var type = jQuery(pEvent.target).attr('data-type');
                currentId = jQuery(pEvent.target).parent().attr('id');
                var dataNew = jQuery(pEvent.target).parent().attr('data-new');

                if ( type == 'img'){
                    mvcFileApi.setCallback(function(pEvent){
                        if ( pEvent.data.length == 0 ){
                            alert('Вы ни чего не выбрали');
                            return;
                        }
                        isAddNewItem(jQuery('#' + currentId + ' *:first'), true);
                        videoData.list[currentId].id = pEvent.data.id

                        if (videoData.list[currentId]['type'] != 'new'){
                            videoData.list[currentId]['type'] = 'edit';
                        }
                    }); // mvcFileApi.setCallback

                    var selectData = videoData.list[currentId].id ? [videoData.list[currentId].id] : [];
                    mvcFileApi.showWindow('p'+videoData.objItemId, 'video', 'people', selectData, {single:1});
                }else
                if ( type == 'rm' && dataNew != 'true' ){
                    if ( currentId[0] == 'n' ){
                        delete videoData.list[currentId];
                    }else{
                        videoData.list[currentId]['type'] = 'rm';
                    }
                    jQuery('#' + currentId).remove();
                } // if

                // func. videoBox
            }

            function saveDataSuccess(data) {
                var data = JSON.parse(data);
                if ( data['error']){
                    alert(data['error']);
                    return;
                }

                for( var id in data ){
                    var newId = 'i'+data[id];
                    jQuery('#'+id).attr('id', newId);
                    videoData.list[newId] = videoData.list[id]
                    delete videoData.list[id];
                }

                for( var id in videoData.list ){
                    if (videoData.list[id].type != 'rm' ){
                        continue;
                    }
                    delete videoData.list[id];
                } // for

                if ( isCloseWindow ){
                    returnSelectData();
                }

                // func. saveDataSuccess
            }

            function saveBtnClick(){
                for(var id in videoData.list ){
                    if ( videoData.list[id]['type'] == 'rm'){
                        continue;
                    }
                    videoData.list[id]['txt'] = jQuery('#'+id + ' input[name="url"]:first').val();
                } // for

                var saveData = {};
                var isSave = false;
                for( var id in videoData.list ){
                    var obj = videoData.list[id];
                    if ( obj.type != 'none' && (obj.id || obj.txt)){
                        saveData[id] = obj;
                        isSave = true;
                    }
                }

                if ( !isSave && !isCloseWindow ){
                    return;
                }

                jQuery.post(document.location + '&$m=saveData', {data:saveData, id:videoData.objItemId}).done(saveDataSuccess);
                return false;
                // func. saveBtnClick
            }

            function returnSelectData(){
                if ( isSingleChoose ){
                    var obj = jQuery('#videoBox input[type="radio"]:checked');
                    if ( obj.length == 0 ){
                        videoMvc.cbResult(null);
                        window.close();
                        return;
                    } // if
                    var id = jQuery(obj).parent().attr('id');
                    if ( id[0] == 'n'){
                        alert('Нельзя выбрать пустой элемент');
                        return;
                    }
                    videoMvc.cbResult(id.substr(1));
                    window.close();
                    return;
                }

                var obj = jQuery('#videoBox input[type="checkbox"]:checked');
                var result = [];
                obj.each(function(i, elem){
                    var id = jQuery(elem).parent().attr('id');
                    if ( id[0] == 'n'){
                        return;
                    }
                    result.push(id.substr(1));
                });
                videoMvc.cbResult(result);
                window.close();
                // func. returnSelectData
            }

            function selectBtnClick(){
                isCloseWindow = true;
                saveBtnClick();
                return false;
                // func. selectBtnClick
            }

            function setInitData(pList){
                jQuery(document).ready(function(){
                    for( var i in pList ){
                        jQuery('#i'+pList[i]+' input[name="id"]').attr('checked', 'checked');
                    }
                });
                // func. setInitData
            }

            function init(){
                isSingleChoose = document.location.search.search("single=1") != -1;

                for( var i in videoData.videoList ){
                    var obj = videoData.videoList[i];
                    var id = 'i'+obj.id;
                    addNewLine(id);
                    jQuery('#'+id + ' input[name="url"]:first').val(obj.txt);
                    videoData.list[id].id = obj.imgId;
                } // for

                addNewLine('n'+newIdNum++, true);
                jQuery('#videoBox').click(videoBoxClick);
                jQuery('#saveBtn').click(saveBtnClick);
                jQuery('#selectBtn').click(selectBtnClick);

                // func. init
            }
            return{
                init: init,
                urlTextKeyPress: urlTextKeyPress,
                setInitData: setInitData
            }
        })();

        jQuery(document).ready(function(){
            videoMvc.init();
        });

    </script>
</body>
</html>