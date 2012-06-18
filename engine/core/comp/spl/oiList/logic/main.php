<?php

namespace core\comp\spl\oiList\logic;

// Conf
use site\conf\DIR;
use site\conf\SITE;
// Engine
use core\classes\dbus;
use core\classes\render;
use core\classes\userUtils;

/**
 * Description of main
 *
 * @author Козленко В.Л.
 */
class main {
    
    public static $urlTplList = array(
        'pageNav' => null,
        'category' => null
    );

    /**
     * Вывод всего списка ( без категорий )
     * @param type $pName 
     */
    public static function renderByOneAction($pName) {
        $comp = dbus::$comp[$pName];
        $compId = $comp['compId'];
        $contId = $comp['contId'];

        // Если есть num, то это ID каталога и мы на страницы с номером страницы
        if ( isset($comp['varName'])){
            $varName = $comp['varName'];
            // Номер страницы
            $num = dbus::$vars[$varName]['num'];
            $prop = dbus::$vars[$varName];
        }else{
            $num = 1;
            // Если data нет, это загружаем настроки
            $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/prop.txt';
            $data = @file_get_contents($file);
            if ( !$data){
                return;
            }
            $prop = \unserialize($data);
        } // if

        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/' . $num . '.txt';

        $data = @file_get_contents($file);
        $oiList = null;
        if (!$data) {
            return;
        }
        $oiListData = \unserialize($data);
        //var_dump($data);
        if ($oiListData) {
            $tpl = userUtils::getCompTpl($comp);
            $paginationList = self::getPaginationList($num, $prop['fileCount']);

            // Шаблон ссылки для пагинации
            $paginationUrlTpl = isset($comp['urlTpl']['pageNav'])?$comp['urlTpl']['pageNav']:'';
            $categoryUrlTpl = isset($comp['urlTpl']['category'])?$comp['urlTpl']['category']:'';

            $nsPath = $comp['nsPath'];
            $tplFile = DIR::SITE_CORE . 'tpl/' . SITE::THEME_NAME . '/comp/' . $nsPath;
            (new render($tplFile, ''))
                ->setVar('oiListData', $oiListData)
                ->setVar('paginationList', $paginationList)
                //->setVar('pagionationUrlParam', $paginationUrlTpl)
                ->setVar('pagionationUrlParam', [])
                ->setVar('paginationUrlTpl', $paginationUrlTpl)
                ->setVar('categoryUrlTpl', $categoryUrlTpl)
                ->setVar('pageNum', $num)
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
        }else{
            echo 'No found settings file data';
            return;
        } // if

        $file = DIR::APP_DATA . 'comp/' . $compId . '/' . $contId . '/' . $categoryId . '/' . $pageNum . '.txt';
        $data = file_get_contents($file);
        $oiList = null;
        if ($data) {
            $oiList = \unserialize($data);
        }
        if ($oiList) {
            $tpl = userUtils::getCompTpl($comp);

            $paginationList = self::getPaginationList($pageNum, $prop['fileCount']);

            // Шаблон ссылки для пагинации
            $paginationUrlTpl = isset($comp['urlTpl']['pageNav'])?$comp['urlTpl']['pageNav']:'';
            $categoryUrlTpl = isset($comp['urlTpl']['category'])?$comp['urlTpl']['category']:'';
            $nsPath = $comp['nsPath'];
            $tplFile = DIR::SITE_CORE . 'tpl/' . SITE::THEME_NAME . '/comp/' . $nsPath;
            (new render($tplFile, ''))
                ->setVar('oiList', $oiList)
                ->setVar('paginationList', $paginationList)
                ->setVar('paginationUrlTpl', $paginationUrlTpl)
                ->setVar('categoryUrlTpl', $categoryUrlTpl)
                ->setVar('pagionationUrlParam', [$catName])
                ->setVar('pageNum', $pageNum)
                ->setVar('objItemDir', DIR::APP_DATA)
                ->setMainTpl($tpl)
                ->setContentType(null)
                ->render();
        } // if
        // func. renderByCategory
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
            echo '<link rel="prev" title="'.sprintf($pParam['linkNextTitle'], $pageNum-1).'"'
                .' href="'.sprintf($pageNavTplUrl, $pageNum-1).'" />'.PHP_EOL;
        }

        // Если страница не самая последняя показывает теги
        if (( $pageNum != $fileCount || !$fileCount ) && isset($pParam['linkNextTitle'])){
            echo '<link rel="next" title="'.sprintf($pParam['linkNextTitle'], $pageNum+1).'"'
                .' href="'.sprintf($pageNavTplUrl, $pageNum+1).'" />'.PHP_EOL;
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
            echo '<link rel="prev" title="'.sprintf($pParam['linkNextTitle'], $catCap, $pageNum-1).'"'
                .' href="'.sprintf($pageNavTplUrl, $catName, $pageNum-1).'" />'.PHP_EOL;
        } // if

        // Если страница не самая последняя показывает теги
        if (( $pageNum != $fileCount || !$fileCount ) && $pParam['linkNextTitle'] ){
            echo '<link rel="next" title="'.sprintf($pParam['linkNextTitle'], $catCap, $pageNum+1).'"'
                .' href="'.sprintf($pageNavTplUrl, $catName, $pageNum+1).'" />'.PHP_EOL;
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

    // class. main
}