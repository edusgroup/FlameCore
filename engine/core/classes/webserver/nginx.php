<?php

namespace core\classes\webserver;

// Engine
use core\classes\render;
use core\classes\filesystem;
use core\classes\arrays;
use core\classes\admin\dirFunc;

// ORM
use ORM\tree\routeTree;
use ORM\event\eventBuffer;

// Conf
use \DIR;
use \SITE;
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

        // Получаем ID всех переменных т.е. все actionId которые выделены как переменная
        $varIdList = $pRouteTree
            ->select('id')
            ->where('propType = 1 and isDel=0')
            ->order('brunchNum DESC, varCount')
            ->toList('id');

        //var_dump($varIdList);

        $varIdListCount = count($varIdList);
        for ($i = 0; $i < $varIdListCount; $i++) {
            // Regexp для location в конфиге
            $regexp = '';
            // Название скрипта, который будет запускать
            $scriptFile = '';
            // rewrite строка в конфиге
            $queryString = '';
            $varCount = 1;

            // Получаем URL для переменной, вдруг там еще есть переменные
            $actionId = (int)$varIdList[$i];
            $pathUrl = $pRouteTree->getActionUrlById($actionId);
            $pathUrlCount = count($pathUrl);
            for ($j = $pathUrlCount - 1; $j >= 0; $j--) {
                $name = $pathUrl[$j]['name'];
                $propType = (int)$pathUrl[$j]['propType'];
                $reg = $propType == 1 ? self::REGEX_VARIBLE : $pathUrl[$j]['name'];
                $regexp .= '/' . $reg;
                $scriptFile .= '/' . $name;
                $varName = $propType == 1 ? $name . '=$' . ($varCount++) . '&' : '';
                $queryString .= $varName;
            } // for $j

            //echo $i,' ', $queryString.PHP_EOL;

            // Смотрим, вдруг есть в папке с переменой, статичные блоки
            $statFolderList = $pRouteTree->selectList(
                'name', 'name', 'propType = 0 and isDel=0 and tree_id=' . $pathUrl[0]['treeId']
            );

            /* // Debug info
               foreach( $pathUrl as $item){
                   echo $item['name'].'/';
               }
               echo PHP_EOL;
               echo 'F:'.(implode(',', $statFolderList));
               echo PHP_EOL.PHP_EOL;
               */

            // Если папки на одном уровне с переменными папками, то их нужно добавить перед конфигом nginx
            if ($statFolderList) {
                foreach ($statFolderList as $name) {
                    // Получаем статический Regexp
                    // /lessons/([^/]+) -> /lessons/page/
                    $count = strlen($regexp) - strlen(self::REGEX_VARIBLE);
                    $stRegexp = substr($regexp, 0, $count) . $name . '/';

                    $count = strlen($queryString) - strlen($varName);
                    $stQueryString = substr($queryString, 0, $count);

                    // Получаем адрес скрипта
                    // /lessons/category -> /lessons/page/
                    $count = strrpos($scriptFile, '/') + 1;
                    $stScriptFile = substr($scriptFile, 0, $count) . $name . '/';

                    //echo "$scriptFile\t$stScriptFile\t$count\n";
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


        $webCoreScript = dirFunc::getCoreScript();
        $webCoreScript = rtrim($webCoreScript, '/');

        $siteName = SITE_CONF::NAME;
        // Если это машина разработчика, то нужно изменить адреса с боевого
        // на локальный. т.е. site.ru -> site.lo
        if (SITE::IS_DEVELOPER) {
            $siteName = preg_replace('/(com|ru|org)$/', 'lo', $siteName);
        } // if

        $render->setVar('siteName', $siteName);
        $render->setVar('fastcgiPass', SITE::FASTCGI_PASS);
        $render->setVar('nginxLog', dirFunc::getSiteNginxLog());
        $render->setVar('siteRoot', dirFunc::getSiteRoot());
        $render->setVar('coreScript', $webCoreScript);

        $loadDir = dirFunc::getSiteDataPath('utils/nginx/');

        $textData = filesystem::loadFileContent($loadDir . 'data.txt');
        $render->setVar('textData', $textData);

        $textData = filesystem::loadFileContent($loadDir . 'servData.txt');
        $render->setVar('servData', $textData);

        ob_start();
        $render->render();
        $nginxConfData = ob_get_clean();
        filesystem::saveFile(DIR::NGINX_CONF, $siteName . '.conf', $nginxConfData);
		echo "Nginx conf saved".PHP_EOL;
        // func. createConf
    }
    // class nginx
}