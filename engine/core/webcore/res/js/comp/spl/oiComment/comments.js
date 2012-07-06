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
				if ( dbus.oiComment.param.noComment ){
					$commentsBox.html(html+'</li>');
				}else{
					$('#'+commentsBox+' .level-0:first').parent().append(html+'</li>');
				}
            }else{
                // Комментарии уже есть, нужно найти куда прикрипеть текущий
                var id = 'comment-'+commentId;

                var $parent = $('#'+id);
                var $children = $parent.find('ul.children:first');
                // Есть ли у блока уже комментарии: $children.length == 1 комментарии есть, $children.length = 0 их нет
                if ( $children.length > 0 ){
                    // Есть комментарии, добавляем под ними
					$children.append(html+'</li>');
                }else{
                    // Нету, добавляем новые
					$('#'+id).append('<ul class="children">'+html+'</li></ul>');
                } // if*/

            } // if
			// Возвращаем обратно Блок с полями комментария
			$('#'+respondBox).appendTo($('#'+commentsBox));
			
            var respond = $('#'+respondBox);
			//$("#"+commentsBox+" .reply > a").click(replyBtnClick);
            respond.find('input[name="author"]').val('');
            respond.find("[name=parentId]").val(0);
            commentId = null;
            respond.find('textarea[name="comment"]').val('');
            //document.location.href = '#comment-'+commentId;
        });
        
        return false;
    // func. saveCommnents
    }
    
    function setParam(pOptions){
        options = $.extend(options, pOptions);
    }
    
    function replyBtnClick(pEvent){
        var id = $(this).attr('rel');
        var respond = $('#'+respondBox);
        $('#cancelBtn').show();
			
        respond.find("[name=parentId]").val(id);
        respond.appendTo($("#comment-"+id));
		commentId = id;			
		document.location.href = '#'+respondBox;
        return false;
        // func. replyBtnClick
    }
    
    function cancelBtnClick(){
        $('#cancelBtn').hide();
        var respond = $('#'+respondBox);
        respond.find("[name=parentId]").val(0);
        respond.appendTo($('#'+commentsBox));
        commentId = null;
        return false;
        // func. cancelBtnClick
    }

    function init(){
        // Бок с полями комментария
        var respond = $('#'+respondBox);
        // Сохранение комментария
        respond.find('form:first').submit(saveCommnents);//.find('input[placeholder]').placeholder();
        $("#"+commentsBox+" .reply > a").click(replyBtnClick);
        $("#"+commentsBox+" .reply > a").live('click', replyBtnClick);
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