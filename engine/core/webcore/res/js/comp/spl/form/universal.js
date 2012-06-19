var univeralForm = (function(){

   function submitSuccess(pData){
       var fSubmitSuccess = cFormList[pData['formId']].submitSuccess;
       fSubmitSuccess && fSubmitSuccess(pData['formId']);
       // func. submitSuccess
   }

   function submit(pEvent){
       var formId = pEvent.target.id;

       var fSubmitClick = cFormList[formId].submitClick;
       fSubmitClick && fSubmitClick(formId);

       var data = $(pEvent.target).serialize();
       data += '&$form[formId]='+formId;
       $.ajax({
           url: '/func/comp/spl/form/',
           type: 'POST',
           data: data,
           cache: false
       }).done(submitSuccess);
       return false;
       // function submit;
   }

   function init(){
       if ( !cFormList ){
           return;
       } // if
       for( var formId in cFormList ){
           $('#'+formId).submit(submit);
       } // for

   // func. init
   }
   return {
        init: init
   }
})();
univeralForm.init();