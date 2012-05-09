<div class="left">
    <div class="dt">Примеры</div>
    <div class="dd">{vars|article|caption}</div>
    <div class="dd">{comp|article|data|seoDescr}</div>
    <div class="dd">{comp|article|data|seoKeywords}</div>
    <div class="dt">Title</div>
    <div class="dd">
        <?=self::textarea('name="title"', self::get('title'))?>
    </div>

    <div class="dt">Description</div>
    <div class="dd"><?=self::textarea('name="descr"', self::get('descr'))?></div>

    <div class="dt">Keywords</div>
    <div class="dd"><?=self::text('name="keywords"', self::get('keywords'))?></div>

    <div class="dt">Компонент</div>
    <div class="dd"><?=self::selectIdName(self::get('complist'), 'name="blCompId"')?></div>
    <div class="dd">Метод</div>
    <div class="dd"><?=self::select(self::get('methods'), 'name="method"')?></div>

    <div class="dt">Link: Next&Prev</div>
    <div class="">Title</div>
    <div class="dd"> <?=self::text('name="linkNextTitle"', self::get('linkNextTitle'))?> Прим: title %s</div>
    <div class="">Url</div>
    <div class="dd"><?=self::text('name="linkNextUrl"', self::get('linkNextUrl'))?> Прим /blog/%s/%s</div>

</div>
<div class="left">
    <?
    $list = self::get('complist')['list'];
    array_shift($list);
    foreach ($list as $item) {
        echo '<p>' . $item['sysname'] . ' | ' . $item['name'] . '</p>';
    }
    ?>
</div>
<script>
    $('#paramBox select[name=blCompId]').change(seoMvc.loadClassMethod);
</script>