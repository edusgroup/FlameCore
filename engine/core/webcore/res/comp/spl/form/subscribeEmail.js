var subscribeForm = (function(pOption){

	function subscribeBtnClick(){
		var $subscribeFormUnderArticle = jQuery(pOption.mainForm);

		var $obj = $subscribeFormUnderArticle.find('input[name="name"]');
		var name = $obj.val().trim();
		if ( name.length == 0 ){
			jQuery(pOption.eventBox).html('Неверно введено имя').addClass('error').show();
			$obj.addClass('inputError');
			return false;
		}
		$obj.removeClass('inputError');

		var $obj = $subscribeFormUnderArticle.find('input[name="email"]');
		var email = $obj.val().trim();
		if ( !(/^[^@]+@([\w-_]+\.)+\w+$/g).test(email) ){
			jQuery('#eventBox').html('Неверно введёт Email').addClass('error').show();
			$obj.addClass('inputError');
			return false;
		}
		$obj.removeClass('inputError');


		jQuery(pOption.eventBox).hide();
		jQuery.ajax({
			url: "/webcore/func/comp/spl/form/?form[action]=subscribe",
			type: 'POST',
			data: 'name=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email)
		}).done(cbSubscribeForm);
		return false;
		// func. subscribeBtnClick
	}

	function cbSubscribeForm(pData){
		if ( !pData || !pData['status'] == undefined){
			return;
		}

		if ( pData['status'] != 0 ){
			jQuery('#eventBox').html(pData['msg']).addClass('error').show();
			return;
		}

		jQuery('#eventBox').html('Спасибо за подписку! Ожидайте рассылку в конце недели.').removeClass('error').addClass('success').show();
		jQuery(pOption.mainForm).hide();
		// func. cbSubscribeForm
	}

	function init(){
		if ( jQuery.cookie('subscribeEmail') == null ){
			jQuery(pOption.mainForm).show();
			jQuery('#subscribeBtn').click(subscribeBtnClick);
		}
		// func. init
	}

	init();
});