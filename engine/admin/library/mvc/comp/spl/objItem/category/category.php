<?php
namespace admin\library\mvc\comp\spl\objItem\category;

// Conf
use \DIR;
// Engine
use core\classes\comp;

global $gObjProp;
$name = 'default.php';
if ( $gObjProp['classType'] !== comp::DEFAULT_VALUE ){
    $name = $gObjProp['classType'] == 'user' ? $gObjProp['classUserFile'] :$gObjProp['classExtFile'] ;
}
$categoryName = $gObjProp['category'];
include DIR::CORE.'admin/library/mvc/comp/spl/objItem/category/'.$categoryName.'/'.$name;
unset($categoryName);