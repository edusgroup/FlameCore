<!DOCTYPE html>
<html>
<head>
    <title>Выберите контент</title>
    <style>
        #content img{
            cursor: pointer;
        }
    </style>
    <script type="text/javascript" src="/res/plugin/jquery/jquery-1.7.2.min.js"></script>
</head>
<body>
    <table id="content">
        <thead>
            <th>Название</th>
            <th></th>
        </thead>
        <tbody>
        <?
            $list = self::get('list');
            foreach($list['body'] as $obj){
                ?>
                <tr>
                    <td><?=$obj['caption']?></td>
                    <td><img src="<?=self::res('images/add_16.png')?>" alt="Выбрать" title="Выбрать" href="#<?=$obj['id']?>" caption="<?=$obj['caption']?>"/></td>
                </tr>
                <?
            }
        ?></tbody>
    </table>
<script type="text/javascript">
    var content = (function(){

        function contentClick(pEvent){
             var $obj = $(pEvent.target);
             var id = $obj.attr('href').substr(1);
             var caption = $obj.attr('caption');
             parentCallback(id, caption, window.callBackUsedData);
             close();
             // func. contentClick
        }

        function init(){
            $('#content').click(contentClick);
            // func. init
       }

       return{
           init: init
       }
    })();
    $(document).ready(function(){
        content.init();

    });
</script>
</body>
</html>