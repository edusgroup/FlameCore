<?php

namespace core\classes\webserver;

// Engine
use core\classes\render;
use core\classes\filesystem;
use core\classes\arrays;
// ORM
use ORM\tree\routeTree;
use ORM\event\eventBuffer;
// Conf
use \DIR;
use \site\conf\SITE as SITE_CONF;

/**
 * Description of nginx
 *
 * @author Козленко В.Л.
 */
class nginx {
    
    const REGEX_VARIBLE = '([^/]+)';

    public static function createConf($pRouteTree) {
        $scriptFileBuffer = [];
        $varList = [];

        // Получаем все переменне
        $varIdList = $pRouteTree->selectAll(
            'id',
            'propType = 1 and isDel=0', 'brunchNum DESC, varCount'
        );

        $varIdListCount = count($varIdList);
        for ($i = 0; $i < $varIdListCount; $i++) {
            // Получаем URL для переменной, вдруг там еще есть переменные
            $pathUrl = $pRouteTree->getActionUrlById((int) $varIdList[$i]['id']);

            // Regexp для location в конфиге
            $regexp = '';
            // Название скрипта, который будет запускать
            $scriptFile = '';
            // rewrite строка в конфиге
            $queryString = '';
            $varCount = 1;
            $pathUrlCount = count($pathUrl);
            for ($j = $pathUrlCount - 1; $j >= 0; $j--) {
                $name = $pathUrl[$j]['name'];
                $propType = (int) $pathUrl[$j]['propType'];
                $reg = $propType == 1 ? self::REGEX_VARIBLE : $pathUrl[$j]['name'];
                $regexp .= '/' . $reg;
                $scriptFile .= '/' . $name;
                $varName = $propType == 1 ? $name . '=$' . ($varCount++) . '&' : '';
                $queryString .= $varName;
            } // for $j
            
            // Смотрим, вдруг есть в папке с переменой, статичные блоки
            $statFolderList = $pRouteTree->selectAll(
                'name',
                'propType = 0 and isDel=0 and tree_id='.$pathUrl[0]['treeId']
            );
            // Если есть, то их нужно добавить перед конфигом nginx
            if ( $statFolderList ){
                foreach( $statFolderList as $item ){
                     $count = strlen($regexp) - strlen(self::REGEX_VARIBLE);
                     $stRegexp = substr($regexp, 0, $count).$item['name'].'/';
                     
                     $count = strlen($queryString) - strlen($varName);
                     $stQueryString =  substr($queryString, 0, $count);
                     
                     $count = strlen($scriptFile) - strlen($name);
                     $stScriptFile = substr($scriptFile, 0, $count).$item['name'].'/';
                     $varList[] = [
                        'regexp' => $stRegexp,
                        'scriptFile' => $stScriptFile,
                        'queryString' => $stQueryString
                    ];
                } // foreach
            } // if
            
            $regexp .= '/';
            $scriptFile .= '/';
            $needle = str_replace('/', '\/', $scriptFile);
            if (!arrays::pregSearch($needle, $scriptFileBuffer)) {
                $regexp .= '(.*/)';
                $scriptFile .= '$' . $varCount;
            } // if

            $scriptFileBuffer[] = $scriptFile;

            $varList[] = [
                'regexp' => $regexp,
                'scriptFile' => $scriptFile,
                'queryString' => $queryString
            ];
        } // for $i

        // Пересоздаём nginx конфиг
        $buildTpl = DIR::CORE . 'buildsys/tpl/';
        $render = new render($buildTpl, '');
        $render->setMainTpl('site_nginx.conf.php')
                ->setContentType(null);
        $render->setVar('vars', $varList);

        $webCoreScript = DIR::getCoreScript();
        $webCoreScript = trim($webCoreScript, '/');

        $render->setVar('siteName', SITE_CONF::NAME);
        $render->setVar('nginxLog', DIR::getSiteNginxLog());
        $render->setVar('siteRoot', DIR::getSiteRoot());
        $render->setVar('coreScript', $webCoreScript );

        ob_start();
        $render->render();
        $nginxConfData = ob_get_clean();
        filesystem::saveFile(DIR::NGINX_CONF, SITE_CONF::NAME . '.conf', $nginxConfData);
        // func. createConf
    }
// class nginx
}