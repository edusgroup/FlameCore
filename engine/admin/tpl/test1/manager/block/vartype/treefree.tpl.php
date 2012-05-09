<script type="text/javascript">
    var varType = {}
    varType.saveData = function(pData){
        if (pData['error']){
            alert(pData['error']['msg']);
            return;
        }
        alert('Данные успешно сохранены');
    }
    
    $(document).ready(function(){

        HAjax.create({
            saveData: varType.saveData
        });
        
        varible.saveDataClick = function(){
            var data = $('#contentForm').serialize();
            HAjax.saveData({data: data, methodType: 'POST'});
            // func. varible.saveDataClick
        }
    });

</script>