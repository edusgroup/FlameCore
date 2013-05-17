<?php

namespace core\comp\spl\oiList\help;

// Conf
use site\conf\DIR;
use site\conf\SITE;

// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\userUtils;
use core\classes\site\dir as sitePath;

trait blog{

	private static $_paginationList;
	private static $_paginationUrlTpl;
	private static $_paginationUrlParam = [];
	private static $_paginationPageNum;

    protected static function getPaginationList($pPageNum, $pCount){
        $maxCount = 8;

        $firstNum = $pPageNum - $maxCount / 2;
        $firstNum  = $firstNum < 1 ? 1 : $firstNum;
        $lastNum = $pPageNum + $maxCount / 2;
        $lastNum = $lastNum > $pCount ? $pCount : $lastNum;

        // Корректировка значений, так начальные и последнии позиции особенные
        $korect = $pPageNum - $firstNum + $lastNum - $pPageNum;
        $korect = $maxCount - $korect - 1;
        $firstNum -= $pPageNum > $pCount - $maxCount / 2 ? $korect : 0;
        $firstNum  = $firstNum < 1 ? 1 : $firstNum;
        $lastNum += $pPageNum < $maxCount / 2 ? $korect : 0;
        $lastNum = $lastNum > $pCount ? $pCount : $lastNum;

        $prev = $firstNum != 1;
        $next = $lastNum != $pCount;

        return ['count' => $pCount,
                'firstNum' => $firstNum,
                'lastNum' => $lastNum,
                'prev' => $prev,
                'next' => $next];
        // func. getPaginationList
    }

	public static function paginationDataAction($pName){
		$comp = dbus::$comp[$pName];
		//$nsPath = $comp['nsPath'];
		//$tplFile = DIR::TPL . 'comp/' . $nsPath;
        $tplPath = sitePath::getSiteCompTplPath($comp['isTplOut'], $comp['nsPath']);
		$tpl = userUtils::getCompTpl($comp);
        
        (new render($tplPath, ''))
                ->setVar('paginationList', self::$_paginationList)
                ->setVar('paginationUrlTpl', self::$_paginationUrlTpl)
                ->setVar('pagionationUrlParam', self::$_paginationUrlParam)
                ->setVar('pageNum', self::$_paginationPageNum)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
	}
	
	/**
     * @static
     * Установка параметров SEO
     */
    public static function setListSeo($pName, $pParam){
        $comp = dbus::$comp[$pName];
        $pageNavTplUrl = $comp['urlTpl']['pageNav'];

        // Если есть varName, то мы находимся на какой либо странице списка
        if ( isset($comp['varName']) && isset(dbus::$vars[$comp['varName']])){
            $pageNum = dbus::$vars[$comp['varName']];
            $fileCount = $pageNum['fileCount'];
            $pageNum = (int)$pageNum['num'];
        }else{
            $pageNum = 1;
            $fileCount = 0;
        }

        // Если страница не первая, то показываем теги
        if ($pageNum > 1 && isset($pParam['linkNextTitle']) ){
			$title = sprintf($pParam['linkNextTitle'], $pageNum-1);
			$href = sprintf($pageNavTplUrl, $pageNum-1);
            echo '<link rel="prev" title="'.$title.'" href="'.$href.'" />'.PHP_EOL;
        }

        // Если страница не самая последняя показывает теги
        if (( $pageNum != $fileCount || !$fileCount ) && isset($pParam['linkNextTitle'])){
			$title = sprintf($pParam['linkNextTitle'], $pageNum+1);
			$href = sprintf($pageNavTplUrl, $pageNum+1);
            echo '<link rel="next" title="'.$title.'" href="'.$href.'" />'.PHP_EOL;
        }
        // func. setListSeo
    }

    public static function setListCategorySeo($pName, $pParam){
        $comp = dbus::$comp[$pName];

        //var_dump($pParam);

        // Если есть data, значит нам указали директорию
        if (!isset($comp['varName'])) {
            return;
        }
        $pageNavTplUrl = $comp['urlTpl']['pageNav'];
        $varName = $comp['varName'];

        list($pageNum, $categoryId, $catName, $fileCount, $catCap) = self::getDataCategory($varName);

        // Если страница не первая, то показываем теги
        if ($pageNum > 1 && $pParam['linkNextTitle'] ){
			$title = sprintf($pParam['linkNextTitle'], $catCap, $pageNum-1);
			$href = sprintf($pageNavTplUrl, $catName, $pageNum-1);
            echo '<link rel="prev" title="'.$title.'" href="'.$href.'" />'.PHP_EOL;
        } // if

        // Если страница не самая последняя показывает теги
        if (( $pageNum != $fileCount || !$fileCount ) && $pParam['linkNextTitle'] ){
			$title = sprintf($pParam['linkNextTitle'], $catCap, $pageNum+1);
			$href = sprintf($pageNavTplUrl, $catName, $pageNum+1);
            echo '<link rel="next" title="'.$title.'" href="'.$href.'" />'.PHP_EOL;
        } // if
        // func. setListCategorySeo
    }
	
	public static function getDataCategory($varName){
        // Если есть num, то это ID каталога и мы на страницы с номером страницы
        if ( isset(dbus::$vars[$varName]['num'])){
            // Имя предыдушей переменной, по ней мы получим имя категории
            $prevVarName = dbus::$vars[$varName]['prevVarName'];
            return [
                dbus::$vars[$varName]['num'],
                dbus::$vars[$prevVarName]['id'],
                dbus::$vars[$prevVarName]['seoname'],
                dbus::$vars[$varName]['fileCount'],
                dbus::$vars[$prevVarName]['caption']
            ];
        }else{
            return [
                1,
                dbus::$vars[$varName]['id'],
                dbus::$vars[$varName]['seoname'],
                0,
                dbus::$vars[$varName]['caption']
            ];
        } // if
    }

    // func. blog
}