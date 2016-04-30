<?
if ( count($argv) < 4 ){
    die('E: Use '.$_SERVER['SCRIPT_FILENAME'].' filename oldname newname'.PHP_EOL);
}

$filename = $argv[1];
if ( !is_file($filename)){
    die('E: file '.$filename.' not found'.PHP_EOL);
}

$data = file_get_contents($filename);
$data = preg_replace('/'.preg_quote($argv[2]).'/i', $argv[3], $data);

file_put_contents($filename, $data);


