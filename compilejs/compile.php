<?

//include DIR::CORE.'site/function/autoload.php';
//include DIR::CORE.'core/function/errorHandler.php';
if ( count($argv) != 3 ){
    die('User compile.php src-file.js compile-file.js');
}

$code = file_get_contents($argv[1]);

// 	SIMPLE_OPTIMIZATIONS
// ADVANCED_OPTIMIZATIONS
$post = 'output_format=json&output_info=compiled_code&output_info=warnings&output_info=errors&output_info=statistics&compilation_level=SIMPLE_OPTIMIZATIONS&warning_level=verbose&output_file_name=default.js&js_code='.urlencode($code);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://closure-compiler.appspot.com/compile');
curl_setopt($ch, CURLOPT_HEADER, 0);

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

$return = curl_exec($ch);
$data = json_decode($return);
curl_close($ch);

//var_dump($data);


//$code = file_get_contents('http://closure-compiler.appspot.com'.$data->outputFilePath);

file_put_contents($argv[2], $data->compiledCode);