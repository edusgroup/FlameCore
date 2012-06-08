<?php
namespace admin\library\mvc\comp\spl\objItem\category;

// Conf
use \DIR;

$categoryName = 'article';
include DIR::CORE.'admin/library/mvc/comp/spl/objItem/category/'.$categoryName.'/category.php';
unset($categoryName);