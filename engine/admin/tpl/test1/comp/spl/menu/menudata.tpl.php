<div class="dt">URL:</div>
<div class="dd"><?= self::text('name="link"', self::get('link')) ?></div>

<div class="dt">Class:</div>
<div class="dd"><?= self::text('name="class"', self::get('class')) ?></div>

<div class="dt">Attr:</div>
<div class="dd">
    <?= self::checkbox('name="nofollow" value="1"', self::get('nofollow')); ?>
    nofollow
</div>

<script type="text/javascript">
    
    $(document).ready(function(){

    });
    
    
</script>