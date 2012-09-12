<div class="dt">URL:</div>
<div class="dd"><?= self::text('name="link"', self::get('link')) ?></div>

<div class="dt">Class:</div>
<div class="dd"><?= self::text('name="class"', self::get('class')) ?></div>

<div class="dt">Attr:</div>
<div class="dd">
    <?= self::checkbox('name="nofollow" value="1"', self::get('nofollow')); ?>
    nofollow
</div>

<div class="dt">Sort Value:</div>
<div class="dd"><?= self::text('name="sortValue"', self::get('sortValue', 0)) ?></div>