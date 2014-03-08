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
class blog{
    use \core\comp\spl\oiList\help\blog;
    
    public static $urlTplList = [
        'pageNav' => null,
        'category' => null
    ];

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

            $tplPath = sitePath::getSiteCompTplPath($comp['isTplOut'], $comp['nsPath']);
            (new render($tplPath, ''))
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

    // class blog
}