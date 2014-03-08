<rss version="2.0" xmlns:ya="http://blogs.yandex.ru/yarss/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" encoding="UTF-8">
    <channel>
        <title><?=self::get('title')?></title>
        <link>http://<?=self::get('host')?>/</link>
        <description><?=self::get('descr')?></description>
        <language>ru</language>
        <!--<image>
            <url>{IMG.jpg}</url>
            <width>100</width>
            <height>100</height>
        </image>-->
        <?
        $list = self::get('list');
        foreach( $list as $item){?>
            <item>
                <guid isPermaLink='true'><?=self::get('host').$item['url']?></guid>
                <category><?=$item['category']?></category>
                <author><?=self::get('host').$item['url']?>aboutme/</author>
                <pubDate><?=$item['date_add']?></pubDate>
                <link><?=self::get('host').$item['url']?></link>
                <description><![CDATA[<?=$item['descr']?> [...]]]></description>
                <title><?=$item['caption']?></title>
                <comments><?=self::get('host').$item['url']?>#comments</comments>
            </item>
            <?
        } // while
        ?>
    </channel>
</rss>