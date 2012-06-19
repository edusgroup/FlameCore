var oiComment = (function(){
    var formCompId = 'oiCommentForm';
    // Блок с полями формы
    var respondBox = 'respond';
    var commentsBox = 'comments';
    // ID комментария, на который отвечаем
    var commentId = null;
    var options = {
        contId: -1,
        comentId: -1
    };

    function saveCommnents(){
        var data = $('#'+respondBox + ' form:first').serialize();
        var url = '/webcore/func/comp/spl/oiComment/';
        url += '?blockItemId=' + options.blockItemId;
        url += '&objItemId=' + options.objItemId;
        $.ajax({
            url: url,
            type: "POST",
            data: data,
            cache: false
        }).done(function( html ) {
            if ( this.dataTypes[1] == 'json' ){
                var json = eval(html);
                alert(json['msg']);
                return;
            }
            // Если commentId не задан, то это просто добавление комментария
            if ( !commentId ){
                var $commentsBox = $('#'+commentsBox);
                var $ul = $commentsBox.find('ul.level1:first');
                // Есть ли уже комментарии
                if ( $ul.length > 0 ){
                    $ul.append('<li></li>').find('li:last').html(html);
                }else{
                    $commentsBox.append('<ul class=""level1"><li></li></u>').find('li:last').html(html);
                } // if
            }else{
                // Комментарии уже есть, нужно найти куда прикрипеть текущий
                var id = 'comment-'+commentId;

                var $parent = $('#'+id).parent();
                var $children = $parent.find('ul.children:first');
                // Есть ли у блока уже комментарии
                if ( $children.length > 0 ){
                    // Есть комментарии, добавляем под ними
                    $children.append('<li></li>').find('li:last').html(html);
                }else{
                    // Нету, добавляем новые
                    $parent.append('<ul class="children"><li></li></ul>').find('ul:last li:first').html(html);
                } // if
                $('#'+respondBox).appendTo($('#comments'));
            } // if
            var respond = $('#'+respondBox);
            respond.find('input[name="author"]').val('');
            respond.find("[name=parentId]").val(0);
            commentId = null;
            $('#comment').val('');
            //document.location.href = '#comment-'+commentId;
        });
        
        return false;
    // func. saveCommnents
    }
    
    function setParam(pOptions){
        options = $.extend(options, pOptions);
    }
    
    function replyBtnClick(){
        var id = $(this).attr('rel');
        var respond = $('#'+respondBox);
        $('#cancelBtn').show();
			
        respond.find("[name=parentId]").val(id);
        respond.appendTo($("#comment-"+id));
	commentId = id;			
        return false;
        // func. replyBtnClick
    }
    
    function cancelBtnClick(){
        $('#cancelBtn').hide();
        var respond = $('#'+respondBox);
        respond.find("[name=parentId]").val(0);
        respond.appendTo($('#comments'));
        commentId = null;
        return false;
        // func. cancelBtnClick
    }

    function init(){
        // Бок с полями комментария
        var respond = $('#'+respondBox);
        // Сохранение комментария
        respond.find('form:first').submit(saveCommnents).find('input[placeholder]').placeholder();

        $("#comments p.reply > a").click(replyBtnClick);
        $('#cancelBtn').click(cancelBtnClick);
        respond.find('input[name=parentId]').val(0);
        //$('#cancelBtn').hide();
        // func. init
    }

    return {
        setParam: setParam,
        init: init
    };
})();

$(function($) {
    oiComment.init();
    if ( dbus.oiComment ){
        oiComment.setParam(dbus.oiComment.param);
    }
});