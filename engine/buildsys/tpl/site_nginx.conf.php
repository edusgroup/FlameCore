server {
  listen 80;
  server_name www.<?=self::get('siteName')?>;
  rewrite ^/(.*) http://<?=self::get('siteName')?>/$1 permanent;
}
server {
	listen 80;
    server_name  <?=self::get('siteName')?>;

    access_log  <?=self::get('nginxLog')?>access.log  main;

	set $wwwDir <?=self::get('siteRoot')?>;
	set $coreScriptDir <?=self::get('coreScript')?>;

    root $wwwDir;
    #For debug only
    #autoindex on;
		
	location ^~ /res/ {
        access_log off;
        expires 24h;
        #Кешируем только на клиентах (ибо сжатое)
        add_header Cache-Control private,max-age=86400;
        add_header Cache-Control private,max-age=86400;
        location ~* \.(jpg|jpeg|gif|png|swf|tiff|swf|flv)$ {
            gzip off;
        }

        location ~ /res/core/ {
            root $coreScriptDir;
        }
	}

    location ^~ /robots.txt {
        gzip off;
	}

    location ^~ /sitemap.xml {
    }

    #gzip on;
    #gzip_comp_level 8;

     error_page  404 @error404;
     location @error404 {
         include fastcgi_params;
         #fastcgi_pass   127.0.0.1:9000;
		 fastcgi_pass php-fpm;
         fastcgi_param  SCRIPT_FILENAME  $wwwDir/404.php;
     } # location @error404

    error_page 500 501 502 503 @error500;
        location @error500 {
        include fastcgi_params;
        #fastcgi_pass   127.0.0.1:9000;
		fastcgi_pass php-fpm;
        fastcgi_param  SCRIPT_FILENAME  $wwwDir/500.php;
    } # location @error500

     location ~ /\.ht {
         deny  all;
     }

     location / {
         include fastcgi_params;
         #fastcgi_pass   127.0.0.1:9000;
		 fastcgi_pass php-fpm;
         fastcgi_param  DOCUMENT_ROOT    $wwwDir;
         fastcgi_param  SCRIPT_FILENAME  $wwwDir/$fastcgi_script_name;
         fastcgi_param  PATH_TRANSLATED  $wwwDir/$fastcgi_script_name;
	 } # location /

    #location /nginx_status {
        #stub_status on;
        #access_log   off;
        #allow SOME.IP.ADD.RESS;
        #deny all;
    #}

	location ~ [^/]$ {
         #fastcgi_pass   127.0.0.1:9000;
		 fastcgi_pass php-fpm;
         fastcgi_param  SCRIPT_FILENAME  $coreScriptDir/script/redirect.php;
         fastcgi_param  QUERY_STRING	    $1?$query_string;
         include fastcgi_params;
	} # location ~ [^/]$

    location ~ /func/ {
        include fastcgi_params;
        #fastcgi_pass   127.0.0.1:9000;
		fastcgi_pass php-fpm;
        fastcgi_param  DOCUMENT_ROOT    $wwwDir;
        fastcgi_param  SCRIPT_FILENAME  $wwwDir/$fastcgi_script_name;
        fastcgi_param  PATH_TRANSLATED  $wwwDir/$fastcgi_script_name;
	} # location ~ /func/
		
<?
        $vars = self::get('vars');
        for( $i = 0; $i < count($vars); $i++ ){
            echo '            #'.$vars[$i]['scriptFile'].'
            location ~ ^'.$vars[$i]['regexp'].'?$ {
                include fastcgi_params;
                #fastcgi_pass   127.0.0.1:9000;
				fastcgi_pass php-fpm;
                fastcgi_param  SCRIPT_FILENAME  $wwwDir'.$vars[$i]['scriptFile'].'index.php;
                fastcgi_param  QUERY_STRING	'.$vars[$i]['queryString'].'$query_string;
            } # ===============================
            
';
        }
        ?>

}