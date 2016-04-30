<?
use core\classes\word;

include('../engine/core/classes/word.php');

if ( !isset($argv[1])){
    die('use '.$argv[0].' filename.txt');
}

$filename = $argv[1];
$fr = fopen($filename, 'r');

while($line = fgets($fr)){
    $line = trim($line);

    echo word::wordToUrl($line).PHP_EOL;
} // while

fclose($fr);