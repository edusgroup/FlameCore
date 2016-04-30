<?

$handleArt = self::get('handleArt');
$item = $handleArt->fetch_object();

//var_dump($item);

$host=self::get('host');

echo '<div id="sitemapbox">';
do{
   $url = sprintf($item->urlTpl, $item->seoName, $item->seoUrl);
   echo '<div class="sitemap"><a href="', $host.$url, '" title="', $item->caption, '">', $item->caption, '</a></div>';
}while($item = $handleArt->fetch_object());

echo '</div>';