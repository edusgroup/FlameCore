<?php

namespace buildsys\library\event\manager\action;

// Conf
use \DIR;

// Conf Site
use \site\conf\SITE as SITE_CONF;
use \site\conf\DIR as SITE_DIR_CONF;

// Engine
use core\classes\filesystem;
use core\classes\builder\tplBlockCreator;
use core\classes\render;
use core\classes\comp;
use core\classes\arrays;

// ORM
use ORM\urlTreePropVar;
use ORM\blockfile;
use ORM\blockItem;
use ORM\blockItemSettings;
use ORM\varTree;
use ORM\varComp;
use ORM\utils\seo as seoOrm;
use ORM\urlTplList as urlTplListOrm;
use ORM\tree\routeTree;
use ORM\tree\componentTree;
use ORM\blockItem\relation as biGroupRelationOrm;
use ORM\tplvar as tplvarOrm;

// Model
use admin\library\mvc\manager\varible\model as varibleModel;

/**
 * Description of eventModel
 *
 * @author Козленко В.Л.
 */
class eventModel {

    // TODO: разобрать эту процедуру на несколько маленьких
    public static function createFileTpl($pFolder, $pAcId, $pPropType, $pRouteTree) {
        // Настройки папки
        $urlTreePropVar = new urlTreePropVar();
        $propData = $urlTreePropVar->selectFirst('*', 'acId=' . $pAcId);

        // Если action не доступен, удаляем его и выходим из обработки текущего event-a
        if (!$propData['enable']) {
            filesystem::unlink($pFolder . 'index.php');
            return;
        }

        // ===================== Есть ли редирект ==============================
        if ($propData['isRedir']) {
            // Создаём код на редирект
            return '<?php header(\'Location: ' . $propData['redirect'] . '\', true, 301); ?>';
        }
        // if

        $wfId = $propData['wf_id'];
        // Если шаблон не был задан, выходим из обработки текущего event-a
        if ($wfId == -1) {
            return;
        }

        // ====================== Работа с переменными==========================
        // Смотрим есть ли в URL перменные
        // TODO: по хорошему лучше сделать процедурой в БД, что бы меньше фильровать
        // тут
        $pathUrl = $pRouteTree->getActionUrlById($pAcId);
        $varList = [];

        foreach ($pathUrl as $item) {
            if ($item['propType'] == 1) {
                $varList[] = $item;
            } // if
        } // foreach
        unset($item);
        // Буффер для доступных переменных
        $varListRender = [];
        $varTree = new varTree();
        $varComp = new varComp();
        $isUsecompContTree = false;
        $varIdtoName = [];
        // Если есть переменные, то нужно их обработать
        if ($varList) {

            $varListCount = count($varList);
            // Бегаем по переменным
            for ($j = $varListCount - 1; $j >= 0; $j--) {
                $acItem = $varList[$j];
                // Получаем настроки переменных
                $acProp = $urlTreePropVar->selectFirst('*', 'acId=' . $acItem['id']);
                // Если ничего не найдено, выходим из обработки
                if ($acProp['varType'] == varibleModel::VARR_TYPE_NONE) {
                    echo "\tError(".__METHOD__."): Not set varible properties in action. AcId: {$acItem['id']}" . PHP_EOL;
                    echo "\tSee: URL '?\$t=manager&\$c=action' and set put varible type in Varbile settings".PHP_EOL;
                    exit;
                }

                // Название переменной
                $name = $acItem['name'];
                $varIdtoName[$acProp['acId']] = $name;

                // Буффер для переменных, для рендера
                $varListRender[$name] = [];
                // Тип переменной: tree или comp
                $varListRender[$name]['type'] = $acProp['varType'];
                // Если переменная имеет тип дерево
                if ($acProp['varType'] == 'tree') {
                    // Меняем флаг, если флаг стоит, то в шаблоне будет создана переменная
                    // $compContTree
                    $isUsecompContTree = true;
                    // Получаем ID родительской ветки
                    $treeId = -1;
                    // У первого элемента дерева это стат число, у других это пред значение 
                    // дерева, по этом ставим -1
                    if ($j == $varListCount - 1) {
                        $treeId = $varTree->get('treeIdStat', 'action_id=' . $acItem['id']);
                    }
                    $varListRender[$name]['treeId'] = $treeId;
                } else
                    // Если тип переменной компонент
                    if ($acProp['varType'] == 'comp') {
                        // Получаем настройки переременной
                        $varCompData = $varComp->select('vc.*, c.ns', 'vc')
                            ->join(componentTree::TABLE . ' c', 'vc.compId=c.id')
                            ->comment(__METHOD__)
                            ->where('acId=' . $pAcId)
                            ->fetchFirst();
                        // Переменная может быть удалена или что то с ней случится,
                        // если её нет, берём следующую переменную
                        if (!$varCompData) {
                            echo "ERROR(" . __METHOD__ . ")" . PHP_EOL . "\tVarible not set in AcId: {$acItem['id']}" . PHP_EOL;
                            exit;
                        }
                        // Название класса
                        $classFile = $varCompData['className'];

                        $className = filesystem::getName($classFile);
                        // Добавляем в буффер namespace classname и название метода
                        $nsClassMethod = comp::getFullCompClassName(
                            $varCompData['classType'],
                            $varCompData['ns'],
                            'vars\\'.$acProp['storageType'],
                            $className);
                        $nsClassMethod .= '::' . $varCompData['methodName'];

                        if ( !$varCompData['contId'] ){
                            echo "NOTICE(" . __METHOD__ . ")" . PHP_EOL . "\tIn varible not set contId AcId: {$acItem['id']}" . PHP_EOL;
                        }

                        $varListRender[$name]['comp'] = $nsClassMethod;
                        $varListRender[$name]['contId'] = $varCompData['contId'];
                        $varListRender[$name]['compId'] = $varCompData['compId'];
                    } // if varType == comp
            } // foreach
        } // if

        $codeBuffer = '<?php ';
        $codeBuffer .= ' $time = microtime(true);';

        $buildTpl = DIR::CORE . 'buildsys/tpl/';
        $render = new render($buildTpl, '');
        $render->setMainTpl('index.tpl.php')
            ->setContentType(null);

        $render->setVar('siteConf', SITE_DIR_CONF::SITE_CORE );
        $render->setVar('varList', $varListRender);
        $render->setVar('isUsecompContTree', $isUsecompContTree);

        ob_start();
        $render->render();
        $codeBuffer .= ob_get_clean();

        $biGroupRelationOrm = new biGroupRelationOrm();

        // ========================== Wareframe ================================
        /**
         * Далее идёт построение шаблона для сайта
         */
        $blockfile = new blockfile();
        $blockItem = new blockItem();
        // Вытаскиваем все блоки WF
        $wfArr = $blockfile->select('id, file, file_id, wf_id as wfId, block')
            ->where('wf_id=' . $wfId . ' AND ( action_id is null or action_id = ' . $pAcId . ')')
            ->order('file_id, id')
            ->comment(__METHOD__)
            ->fetchAll();
        if ( !$wfArr){
            return "WF[$wfId] is empty";
        }

        $blockFileList = [];
        // Бегаем по блокам WF, строим удобный для нас массив
        // $blockFileList[blockId] = [file="", id=""]
        foreach ($wfArr as $item) {
            $blockId = $item['block'] . ':' . $item['file_id'];
            $blockFileList[$blockId] = [
                'file' => $item['file'],
                'id' => $item['id']
            ];
        } // foreach
        unset($item);

        // После того как массив блоков получили, нам вытащить настроки blockItem для всех блоков
        // В настройках храняться компоненты и их параметры
        $blockItemArr = $blockItem
            ->select(
            'bi.id, c.ns, bi.sysname, bi.block_id, bi.compId, c.onlyFolder' .
                ',bis.tplFile, bis.classFile, bis.methodName' .
                ',bi.userReg, bi.tplAccess, bis.custContId, bis.classType' .
                ',bis.statId, bis.tableId, bis.varId, bis.varTableId', 'bi')
            ->joinLeftOuter(blockItemSettings::TABLE . ' bis', 'bis.blockItemId=bi.id')
            ->join(componentTree::TABLE . ' c', 'bi.compId=c.id')
            ->where('bi.wf_id=' . $wfId . ' AND ( bi.acId is null or bi.acId = ' . $pAcId . ')')
            ->order('bi.position')
            ->comment(__METHOD__)
            ->fetchAll();

        $blockItemList = [];
        $blockItemInitList = [];
        $sysnameNum = 0;

        // Бегаем по настройкам блоков
        foreach ($blockItemArr as $item) {
            //print $item['classType']."\n";
            // Если компонент был удалён, то пишем ошибку и берём следующий компонент
            if (!$item['compId']) {
                echo "ERROR(" . __METHOD__ . "):" . PHP_EOL;
                echo "\tBlockId({$item['id']}) AcId($pAcId) SysName({$item['sysname']})" . PHP_EOL;
                echo "\tComponent [{$item['compId']}] not found." . PHP_EOL;
                continue;
            } // if

            // Если контент был удалён, то пишем ошибку и берём следующий компонент
            if (!$item['statId'] && !$item['varId']) {
                echo "ERROR(" . __METHOD__ . "):" . PHP_EOL;
                echo "\tBlockId({$item['id']}) AcId($pAcId) SysName({$item['sysname']})" . PHP_EOL;
                echo "\tNot set contId in blockItem.";
                continue;
            } // if
            // Если табличные данные контента были удалёны, то пишем ошибку и берём следующий компонент
            if (!$item['tableId'] && $item['onlyFolder'] && !$item['varId']) {
                print "ERROR(" . __METHOD__ . "):" . PHP_EOL . "\tTableId not found. BlockId: [{$item['id']}]. AcId: $pAcId" . PHP_EOL;
                continue;
            } // if
            // Если табличные данные контента были удалёны, то пишем ошибку и берём следующий компонент
            if (!$item['methodName']) {
                print "ERROR(" . __METHOD__ . "):" . PHP_EOL . "\tMethodName not found. BlockId: [{$item['id']}]. AcId: $pAcId" . PHP_EOL;
                continue;
            } // if

            $blockId = $item['block_id'];
            if (!$item['ns'] || !$item['classFile']) {
                echo PHP_EOL . "\t" . 'Notice: blockItem ID: [' . $item['id'] . '] not have prop' . PHP_EOL . PHP_EOL;
                continue;
            }
            ++$sysnameNum;
            $sysname = $item['sysname'] ? : 'sys_' . $sysnameNum;

            // Имя класс-файла, выбранного в blockItem. Пример: /objItem.php
            $className = $item['classFile'];
            // Убераем символ "/" в начале и ".php", т.е. получаем чистое имя файла
            $className = substr($className, 1, strlen($className) - 5);
            // Заменяем все "/" на "\", т.е. делаем namespace+classname
            $className = str_replace('/', '\\', $className);
            // Добавляем пристовку namespace компонентов, ns name компонента + названия класса
            //$className = '\core\comp\\' . $item['ns'] . 'logic\\' . $className;
            $className = comp::getFullCompClassName($item['classType'], $item['ns'], 'logic', $className);
            $methodName = $item['methodName'];
            $callParam = '(\'' . $sysname . '\');';

            $nsPath = filesystem::nsToPath($item['ns']);
            $tplFile = substr($item['tplFile'], 1);

            $urlTplListOrm = new urlTplListOrm();
            $urlTplList = $urlTplListOrm->selectAll('name, acId', 'blockItemId=' . $item['id']);

            $codeTmp = "array(\n" .
                "\t'tpl' => '$tplFile'," . PHP_EOL .
                "\t'compId' => '{$item['compId']}'," . PHP_EOL .
                "\t'nsPath' => '$nsPath'," . PHP_EOL;

            // Если ограничения по авторизации пользователя
            if ($item['userReg']) {
                $groupList = $biGroupRelationOrm->selectList('groupId', 'groupId', 'biId=' . $item['id']);
                if ($groupList) {
                    $codeTmp .= "\t'userGroup' => [" . implode(',', $groupList) . "]," . PHP_EOL;
                }
                // Есть ли доступы для авторизованных пользователей
                if ($item['tplAccess']) {
                    $codeTmp .= "\t'tplAccess' => '{$item['tplAccess']}'," . PHP_EOL;
                } // if $item[userReg]
            } // if


            if ($item['varId']) {
                $varName = $varIdtoName[$item['varId']];
                $codeTmp .= "\t'varName' => '$varName'," . PHP_EOL;
            } // if

            $codeTmp .= "\t'contId' => '{$item['statId']}'," . PHP_EOL;

            if ($item['onlyFolder']) {
                if ($item['varTableId']) {
                    $varName = $varIdtoName[$item['varTableId']];
                    $codeTmp .= "\t// Component has onlyFolder, varTableName - name vars of category" . PHP_EOL;
                    $codeTmp .= "\t'varTableName' => '$varName'," . PHP_EOL;
                } else {
                    $codeTmp .= "\t'tableId' => '{$item['tableId']}'," . PHP_EOL;
                } // if
            } // if onlyFolder

            if ($urlTplList) {
                $codeTmp .= "\t'urlTpl' => [" . PHP_EOL;
                foreach ($urlTplList as $urlTpl) {
                    // Строим шаблон для URL
                    $urlArr = $pRouteTree->getActionUrlById((int)$urlTpl['acId']);
                    $urlArr = array_map(function($pItem) {
                        return $pItem['propType'] == 1 ? '%s' : $pItem['name'];
                    }, $urlArr);
                    $urlArr = array_reverse($urlArr);
                    $url = implode('/', $urlArr);
                    $codeTmp .= "\t\t'{$urlTpl['name']}'=>'/$url/'," . PHP_EOL;
                } // foreach
                $codeTmp .= "\t]" . PHP_EOL;
            } // if ( $urlTplList )

            // ================= Создание блока для кастомных настроек ===================
            if ($item['custContId'] || $item['statId']) {
                $custContId = (int)($item['custContId'] ? : $item['statId']);
                if ($custContId) {
                    // Создаём объекта класса компонента, который стоит в blockItem
                    global $gObjProp;
                    $gObjProp = comp::getCompContProp($custContId);
                    $contrObj = comp::getCompObject($gObjProp);

                    if (method_exists($contrObj, 'getBlockItemParam')) {
                        $codeTmp .= $contrObj->getBlockItemParam($item['id'], $pAcId);
                    } // if method_exists
                } // if $custContId
            } // if ($item['custContId'] || $item['statId'])
            // ---------------------------------------------------------------------------

            $codeTmp .= ");\n";

            $blockItemInitList[$sysname][] = $codeTmp;

            $blockItemList[$blockId][] = [
                'class' => $className,
                'method' => $methodName,
                'callParam' => $callParam
            ];
        } // foreach


        // =====================================================================
        // ============ Инициализация компонентов =============================
        foreach ($blockItemInitList as $name => $obj) {
            $itemCount = count($obj);
            for ($i = 0; $i < $itemCount; $i++) {
                $codeBuffer .= 'dbus::$comp[\'' . $name . '\'] = ' . $obj[$i];
            } // for
        } // foreach. Бегаем по blockItem у WF

        $codeBuffer .= self::_getInitClassCode($blockItemList);
        $codeBuffer .= "\n\n?>";

        // получаем значение выставленных переменных для шаблонов сайта
        $tplVarList = (new tplvarOrm())->sql('select * from (
              select name, value
              from '.tplvarOrm::TABLE.'
              where acid='.$pAcId.' or acid is null
              order by acid desc
              ) t
              group by t.name')->fetchAll();

        $tplVarList = arrays::dbQueryToAssoc($tplVarList);

        // TODO: Вписать обработку controller
        // Включаем шаблонизатор, что бы получить код страницы
        $tplSitePath = DIR::getSiteTplPath();
        $resSiteUrl = DIR::getSiteResUrl();
        $tplBlockCreator = new tplBlockCreator(
            $tplSitePath,
            $resSiteUrl,
            $blockFileList[':']['id']);

        // Данные блока Head

        $headData = self::getHeadData($pAcId);
        $tplBlockCreator->setHeadData($headData);
        unset($headData);

        $tplBlockCreator->setVaribleList($tplVarList);

        $scriptStaticData = self::getScriptStaticData();
        $scriptDynData = self::getScriptDynData();
        $tplBlockCreator->setScriptData($scriptStaticData, $scriptDynData);
        unset($scriptOnlineData, $scriptOfflineData);

        $tplBlockCreator->setBlockFileList($blockFileList);
        $tplBlockCreator->setBlockItemList($blockItemList);
        //$tplBlockCreator->setBlockItemInitList($blockItemInitList);

        $tplBlockCreator->start($blockFileList[':']['file']);

        $codeBuffer .= $tplBlockCreator->getCodeBuffer();
        // Создаём php файл

        $codeBuffer .= "<? echo '<!-- '.(microtime(true) - \$time).' -->'; ?>";

        return $codeBuffer;
        // func. createFileTpl
    }

    private static function getScriptStaticData() {
        return <<<'CODE_STRING'
<?php
            $dbusHeadCount = count(dbus::$head['jsStatic']);
            for( $i = 0; $i < $dbusHeadCount; $i++ ){
                 echo '<script src="'.dbus::$head['jsStatic'][$i].'"></script>';
            } // if
?>
CODE_STRING;
    }

    private static function getScriptDynData() {
        return <<<'CODE_STRING'
        <script>
            function $import(src){
                var scriptElem = document.createElement('script');
                scriptElem.setAttribute('src',src);
                scriptElem.setAttribute('type','text/javascript');
                document.getElementsByTagName('head')[0].appendChild(scriptElem);
            }

            setTimeout(function(){
                //$import('/res/js/template.js');
                <?php
            $dbusHeadCount = count(dbus::$head['jsDyn']);
            for( $i = 0; $i < $dbusHeadCount; $i++ ){
                 echo '$import("'.dbus::$head['jsDyn'][$i].'");';
            } // if
?>
        }, 700);</script>

CODE_STRING;
    }

    /**
     * Возвращает блок инициализаций компонентов
     * Только в случае, если у компонента есть функцию init, добавляет в файла при вызове
     * @static
     * @param $blockItemList
     * @return string
     */
    private static function _getInitClassCode($blockItemList) {
        $codeBuffer = '';
        // Бегаем по компонентам, смотрим если у них методы init, если да, то ставим
        // на вызов в файле
        foreach ($blockItemList as $item) {
            $itemCount = count($item);
            for ($i = 0; $i < $itemCount; $i++) {
                if (method_exists(new $item[$i]['class'](), 'init')) {
                    $codeBuffer .= $item[$i]['class'] . '::init' . $item[$i]['callParam'] . ";\n";
                } // if
            } // ofr
        } // foreach
        // Если ни какого кода нет, то не делает try..catch
        if ( $codeBuffer ){
        $codeBuffer = 'try{'.$codeBuffer;
        $codeBuffer .= "}catch(Exception \$ex){
    header('Status: 502 Internal Server Error');
    exit;
}";}
        return $codeBuffer;
        // func. _getInitClassCode
    }

    /**
     * Возвращает данные для блока Head
     * @static
     * @return string
     */
    public static function getHeadData($pAcId) {
        // Получаем даныне по сео. Настраиваются в Utils->SEO
        $seoData = (new seoOrm())
            ->select(
            's.title, s.descr, s.keywords, bi.sysname name, s.linkNextUrl, '
                . 's.linkNextTitle, c.ns, c.sysname, s.method, bis.classFile, bis.classType', 's')
            ->joinLeftOuter(blockItem::TABLE . ' bi', 'bi.id = s.blItemId')
            ->joinLeftOuter(componentTree::TABLE . ' c', 'c.id=bi.compId')
            ->joinLeftOuter(blockItemSettings::TABLE . ' bis', 'bis.blockItemId=bi.id')
            ->where('s.acId=' . $pAcId)
            ->comment(__METHOD__)
            ->fetchFirst();
        if ( !$seoData ){
            return '<!-- '.__METHOD__.'() | No DATA -->';
        }
        // Заголовок
        $title = $seoData['title'];
        // Ключевые слова
        $keywords = $seoData['keywords'];
        // Описание
        $descr = $seoData['descr'];
        // Парсим данные на наличие переменных
        $title = self::parseTag($title);
        $keywords = self::parseTag($keywords);
        $descr = self::parseTag($descr);

        // Добавляем теги описания, ключевых слов и заголовка страницы
        $headData = "<?
        echo '<title>$title</title>'.PHP_EOL;
        echo '<meta name=\"description\" content=\"$descr\" />'.PHP_EOL;
        echo '<meta name=\"keywords\" content=\"$keywords\" />'.PHP_EOL;
        ";

        if ($seoData['sysname']) {
            $classFile = substr($seoData['classFile'], 1, strlen($seoData['classFile']) - 5);
            $classFile = str_replace('/', '\\', $classFile);
            $headData .= comp::getFullCompClassName($seoData['classType'], $seoData['ns'], 'logic', $classFile);
            $headData .= "::{$seoData['method']}('{$seoData['name']}', [" .
                "'linkNextTitle'=>'{$seoData['linkNextTitle']}'," .
                "'linkNextUrl'=>'{$seoData['linkNextUrl']}'" .
                "]);" . PHP_EOL;
        } // if

        $headData .= '?>';
        return $headData;
        // func. getHeadData
    }

    public static function parseTag($pWord) {
        preg_match_all('/{([\w|]+)}/', $pWord, $data);
        if (!isset($data[1])) {
            return $pWord;
        } // if
        foreach ($data[1] as $item) {
            $code = explode('|', $item);
            $varName = array_shift($code);
            $code = implode("']['", $code);
            $code = "'.dbus::$" . $varName . "['" . $code . "'].'";
            $pWord = str_replace('{' . $item . '}', $code, $pWord);
        } // foreach
        return $pWord;
        // func. parseTag
    }

    public static function getActionPath($pAcId, $pRouteTree) {
        $pathUrl = $pRouteTree->getTreeUrlById(routeTree::TABLE, $pAcId);
        $path = '';
        $pathUrlCount = count($pathUrl);
        for ($j = 0; $j < $pathUrlCount; $j++) {
            $path = $pathUrl[$j]['name'] . '/' . $path;
        } // for $j
        // ===============
        return $path;
        // func. getActionPath
    }

    // class eventModel
}