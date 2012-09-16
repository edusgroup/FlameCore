<?php

namespace core\comp\spl\oiList\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;
// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\userUtils;
use core\classes\site\dir as sitePath;

/**
 * Description of main
 *
 * @author Козленко В.Л.
 */
class blog {
    
    public static $urlTplList = [
        'pageNav' => null,
        'category' => null
    ];
	
	private static $_paginationList;
	private static $_paginationUrlTpl;
	private static $_paginationUrlParam = [];
	private static $_paginationPageNum;

    /**
     * Вывод всего списка ( без категорий )
     * @param type $pName 
     */
    public static function renderByOneAction($pName) {
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];

        // Если есть pageNum, то это ID каталога и мы на страницы с номером страницы
        if ( isset($comp['varName'])){
            $varName = $comp['varName'];
            // Номер страницы
            $pageNum = dbus::$vars[$varName]['num'];
            $prop = dbus::$vars[$varName];
        }else{
            $pageNum = 1;
            // Если data нет, это загружаем настроки
            $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/prop.txt';
            $data = @file_get_contents($file);
            if ( !$data){
                return;
            }
            $prop = \unserialize($data);
        } // if

        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/' . $pageNum . '.txt';
        $data = @file_get_contents($file);
        $oiList = null;
        if (!$data) {
            return;
        }
        $oiListData = \unserialize($data);
        //var_dump($data);
        if ($oiListData) {
            $tpl = userUtils::getCompTpl($comp);
            self::$_paginationList = self::getPaginationList($pageNum, $prop['fileCount']);

            // Шаблон ссылки для пагинации
            self::$_paginationUrlTpl = isset($comp['urlTpl']['pageNav'])?$comp['urlTpl']['pageNav']:'';
			self::$_paginationPageNum = $pageNum;
            $categoryUrlTpl = isset($comp['urlTpl']['category'])?$comp['urlTpl']['category']:'';

            $tplPath = sitePath::getSiteCompTplPath($comp['isTplOut'], $comp['nsPath']);
            (new render($tplPath, ''))
                ->setVar('oiListData', $oiListData)
                ->setVar('paginationList', self::$_paginationList)
                ->setVar('pagionationUrlParam', [])
                ->setVar('paginationUrlTpl', self::$_paginationUrlTpl)
                ->setVar('categoryUrlTpl', $categoryUrlTpl)
                ->setVar('pageNum', $pageNum)
                ->setVar('objItemDir', DIR::APP_DATA)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
        } // if ($oiListData)
        // func. fileArtList
    }

    /**
     * Вывод списка статей по категориям
     * @param type $pName
     * @return type 
     */
    public static function renderByCategoryAction($pName) {
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];

        // Если есть data, значит нам указали директорию
        if (!isset($comp['varName'])) {
            echo 'Error(renderByCategory): Not category varible';
            return;
        }
        
        $varName = $comp['varName'];

        list($pageNum, $categoryId, $catName, $fileCount, $catCap) = self::getDataCategory($varName);

        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/' . $categoryId . '/prop.txt';
        $data = @file_get_contents($file);
        if ($data) {
            $prop = \unserialize($data);
            unset($data);
        }else{
            echo 'No found settings file data';
            return;
        } // if
        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/' . $categoryId . '/' . $pageNum . '.txt';
        $data = file_get_contents($file);
        $oiListData = null;
        if ($data) {
            $oiListData = \unserialize($data);
            unset($data);
        }
        if ($oiListData) {
            $tpl = userUtils::getCompTpl($comp);

            self::$_paginationList = self::getPaginationList($pageNum, $prop['fileCount']);
			
            // Шаблон ссылки для пагинации
            self::$_paginationUrlTpl = isset($comp['urlTpl']['pageNav'])?$comp['urlTpl']['pageNav']:'';
			self::$_paginationUrlParam = [$catName];
			self::$_paginationPageNum = $pageNum;
			
            $categoryUrlTpl = isset($comp['urlTpl']['category'])?$comp['urlTpl']['category']:'';
            $nsPath = $comp['nsPath'];
            $tplFile = DIR::TPL . 'comp/' . $nsPath;
            (new render($tplFile, ''))
                ->setVar('oiListData', $oiListData)
                ->setVar('paginationList', self::$_paginationList)
                ->setVar('paginationUrlTpl', self::$_paginationUrlTpl)
                ->setVar('categoryUrlTpl', $categoryUrlTpl)
                ->setVar('pagionationUrlParam', self::$_paginationUrlParam)
                ->setVar('pageNum', $pageNum)
                ->setVar('objItemDir', DIR::APP_DATA)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
        }else{
			echo '<span style="color: red">Error: <br/>'.$file.'<br/> empty or not found</a>';
		}// if
        // func. renderByCategory
    }
	
	public static function paginationDataAction($pName){
		$comp = dbus::$comp[$pName];
		$nsPath = $comp['nsPath'];
		$tplFile = DIR::TPL . 'comp/' . $nsPath;
		$tpl = userUtils::getCompTpl($comp);
		(new render($tplFile, ''))
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

    private static function getDataCategory($varName){
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

    private static function getPaginationList($pPageNum, $pCount){
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

        return ['firstNum' => $firstNum,
                'lastNum' => $lastNum,
                'prev' => $prev,
                'next' => $next];
        // func. getPaginationList
    }

    // class blog
}