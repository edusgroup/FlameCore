<?
	$arr = explode('?', $_SERVER['REQUEST_URI']);
	list($url,$query) = isset($arr[1])?$arr:array($arr[0], '');
	$query = $query ? '?'.$query : '';
	//print 'q='.$query;
	#print 'd='.$url.'/'.$query;
	#exit;
	header("HTTP/1.1 301 Moved Permanently");
    header('Location: http://'.$_SERVER['HTTP_HOST'].$url.'/'.$query);
