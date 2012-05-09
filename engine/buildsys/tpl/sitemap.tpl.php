<?echo '<?xml version="1.0" encoding="UTF-8"?>'?>
<?echo '<?xml-stylesheet type="text/xsl" href="/res/xml-sitemap.xsl"?>'?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?$handleArt = self::get('handleArt');
    $item = $handleArt->fetch_object();?>

    <url>
        <loc><?=$host=self::get('host')?>/</loc>
        <lastmod><?=$item?$item->date_add:''?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <?do{
    $url = sprintf($item->urlTpl, $item->seoName, $item->seoUrl);
    ?>
    <url>
        <loc><?=$host.$url?></loc>
        <lastmod><?=$item->date_add?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <?}while($item = $handleArt->fetch_object())?>
</urlset>