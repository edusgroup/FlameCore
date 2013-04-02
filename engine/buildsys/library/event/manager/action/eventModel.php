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
use core\classes\admin\dirFunc;
use core\classes\comp as compCore;

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
use ORM\block\blockLink as blockLinkOrm;
use ORM\blockItem\order as blockItemOrderOrm;

// Model
use admin\library\mvc\manager\varible\model as varibleModel;

// Init
use admin\library\init\comp as compInit;

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

        // ========  Есть ли редирект, то выставляем его и выходим изобработки ==========
        if ($propData['isRedir']) {
            // Создаём код на редирект
            return '<?php header(\'Location: ' . $propData['redirect'] . '\', true, 301); ?>';
        }
        // if ===========================================================================

        // Получаем WareframeId выставленный для actionId,
        // т.е то что мы выберали при настройке action
        $wfId = $propData['wf_id'];
        // Если шаблон не был задан, выходим из обработки текущего event-a
        // т.е. при настройке мы не выбрали wf, значит отображать нечего
        if ($wfId == -1) {
            return;
        }

        // ====================== Работа с переменными==========================
        $varList = varibleModel::getVarList($pRouteTree, $pAcId);
        // Теперь в $varList храняться переменные, которые были заданы в URL
        // Буффер для доступных переменных
        $varListRender = [];
        $isUsecompContTree = false;
        $varIdtoName = [];
        // Если есть переменные, то нужно их обработать
        if ($varList) {
            $isVar = self::_initVarible($pAcId, $varList, $urlTreePropVar, new varComp(), new varTree(), $varListRender, $varIdtoName, $isUsecompContTree);
			if (!$isVar){
				return;
			}			
        } // if

        $buildTpl = DIR::CORE . 'buildsys/tpl/';
        $render = new render($buildTpl, '');
        $render->setMainTpl('index.tpl.php')->setContentType(null);

        $render->setVar('siteConf', SITE_DIR_CONF::SITE_CORE);
        $render->setVar('varList', $varListRender);
        $render->setVar('isUsecompContTree', $isUsecompContTree);
        $render->setVar('controller', $propData['controller']);

        // Начинем создовать код
        $codeBuffer = '<?php $time = microtime(true);'.PHP_EOL;

        // Если есть кастомный контроллер, мы его должны за инклюдить и вызывать его методы
        if ( $propData['controller'] ){
            $controllerBody = 'include(\''.SITE_DIR_CONF::SITE_CORE . 'core/logic/'.$propData['controller'].'\');'.PHP_EOL;
            $controllerBody .= '$bodyCustom = new bodyCustom(); $bodyCustom->onCreate();'.PHP_EOL;
            $render->setVar('controllerBody', $controllerBody, false);
        } // if

        ob_start();
        $render->render();
        $codeBuffer .= ob_get_clean();
		
		// получаем значение выставленных переменных для шаблонов сайта
        $tplVarList = (new tplvarOrm())->sql('select * from (
              select name, value
              from ' . tplvarOrm::TABLE . '
              where acid=' . $pAcId . ' or acid = 0
              order by acid desc
              ) t group by t.name')->fetchAll();

        $tplVarList = arrays::dbQueryToAssoc($tplVarList);
		$codeBuffer .= 'dbus::$tplVarible = '.var_export($tplVarList, true).';'.PHP_EOL;

        $biGroupRelationOrm = new biGroupRelationOrm();

        // ========================== Wareframe ================================
        // Далее идёт построение шаблона для сайта

        // Вытаскиваем все блоки которые заданы в WF( глобальные и локальные для actionId )
        $wfAllBlock = self::_getAllWFItem(new blockfile(), $wfId, $pAcId);
        if (!$wfAllBlock) {
            return "WF[$wfId] is empty";
        }

        $blockFileList = [];
        // Бегаем по блокам WF, строим удобный для нас массив
        // Строим нечто такое: $blockFileList[blockId] = [file="", id=""]
        foreach ($wfAllBlock as $biItem) {
            $blockId = $biItem['block'] . ':' . $biItem['file_id'];
            $blockFileList[$blockId] = [
                'file' => $biItem['file'],
                'id' => $biItem['id']];
        } // foreach

        $blockItemList = self::_getAllBlockItem($wfId, $pAcId);
        /*
        После метода self::_getAllBlockItem, у нас в $blockItemList содержится все блоки и линки на блоки, которые были
        когда сохранены в wareframe и action-wf
        */

        // Создаём доп буфферы
        $buffBlockToClass = [];
        $blockItemInitList = [];
        $sysnameNum = 0;

        // Бегаем по настройкам блоков, которые получили из метода self::_getAllBlockItem
        foreach ($blockItemList as $biItem) {
            // Если компонент был удалён, то пишем ошибку и берём следующий компонент
            if (!$biItem['compId']) {
                echo "ERROR(" . __METHOD__ . "):" . PHP_EOL;
                echo "\tBlockId({$biItem['id']}) AcId($pAcId) SysName({$biItem['sysname']})" . PHP_EOL;
                echo "\tComponent [{$biItem['compId']}] not found." . PHP_EOL;
                continue;
            } // if

            // Если контент был удалён, то пишем ошибку и берём следующий компонент
            /*if (!$biItem['statId'] && !$biItem['varId']) {
                //var_dump($biItem);
                echo "\tERROR(" . __METHOD__ . "):" . PHP_EOL;
                echo "\tBlockId({$biItem['id']}) AcId($pAcId) SysName({$biItem['sysname']})" . PHP_EOL;
                echo "\tNot set contId in blockItem.";
                continue;
            } // if*/
            // Если табличные данные контента были удалёны, то пишем ошибку и берём следующий компонент
            if (!$biItem['tableId'] && $biItem['onlyFolder'] && !$biItem['varId']) {
                print "ERROR(" . __METHOD__ . "):" . PHP_EOL . "\tTableId not found. BlockId: [{$biItem['id']}]. AcId: $pAcId" . PHP_EOL;
                continue;
            } // if
            // Если табличные данные контента были удалёны, то пишем ошибку и берём следующий компонент
            if (!$biItem['methodName']) {
                print "ERROR(" . __METHOD__ . "):" . PHP_EOL . "\tMethodName not found. BlockId: [{$biItem['id']}]. AcId: $pAcId" . PHP_EOL;
                continue;
            } // if

            $blockId = $biItem['block_id'];
            if (!$biItem['ns'] || !$biItem['classFile']) {
                echo PHP_EOL . "\t" . 'Notice: blockItem ID: [' . $biItem['id'] . '] not have prop' . PHP_EOL;
                continue;
            }
            ++$sysnameNum;
            $sysname = $biItem['sysname'] ? : 'sys_' . $sysnameNum;

            // Имя класс-файла, выбранного в blockItem. Пример: /objItem.php
            $className = comp::fullNameClassSite($biItem['classFile'], $biItem['ns']);
            $methodName = $biItem['methodName'];
            $callParam = '(\'' . $sysname . '\');';
            $nsPath = filesystem::nsToPath($biItem['ns']);

            $tplFileData = comp::getFileType($biItem['tplFile']);;
            $tplFile = $tplFileData['file'];
            $isTplFileOut = $tplFileData['isOut'];
            $urlTplList = (new urlTplListOrm())->selectAll('name, acId', 'blockItemId=' . $biItem['id']);

            $codeTmp = ['tpl' => $tplFile, 'isTplOut' =>$isTplFileOut, 'compId'=>$biItem['compId'], 'nsPath'=> $nsPath];


            // Если ограничения по авторизации пользователя
            if ($biItem['userReg']) {
                $groupList = $biGroupRelationOrm->selectList('groupId', 'groupId', 'biId=' . $biItem['id']);
                if ($groupList) {
                    $codeTmp['userGroup'] = implode(',', $groupList);
                }
                // Есть ли доступы для авторизованных пользователей
                if ($biItem['tplAccess']) {
                    $codeTmp['tplAccess'] = $biItem['tplAccess'];
                } // if $item[userReg]
            } // if

            // Переменная в настройках blockItem должна быть задана в настройках
            if ($biItem['varId']) {
                $varName = $varIdtoName[$biItem['varId']];
                $codeTmp['varName'] = $varName;
            } // if

            $codeTmp['contId'] = $biItem['statId'];

            if ($biItem['onlyFolder']) {
                if ($biItem['varTableId']) {
                    $varName = $varIdtoName[$biItem['varTableId']];
                    //$codeTmp .= "\t// Component has onlyFolder, varTableName - name vars of category" . PHP_EOL;
                    $codeTmp['varTableName'] = $varName;
                } else {
                    $codeTmp['tableId'] = $biItem['tableId'];
                } // if
            } // if onlyFolder

            if ($urlTplList) {
                foreach ($urlTplList as $urlTpl) {
                    // Строим шаблон для URL
                    $urlArr = $pRouteTree->getActionUrlById((int)$urlTpl['acId']);
                    $urlArr = array_map(function($pItem) {
                        return $pItem['propType'] == 1 ? '%s' : $pItem['name'];
                    }, $urlArr);
                    $urlArr = array_reverse($urlArr);
                    $url = '/'.implode('/', $urlArr).'/';
                    $codeTmp['urlTpl'][$urlTpl['name']] = $url;
                } // foreach
            } // if ( $urlTplList )

            // ================= Создание блока для кастомных настроек ===================
            if ($biItem['custContId'] || $biItem['statId']) {
                self::_getCodeBlSettCust($codeTmp, $biItem, $pAcId);
            } // if ($item['custContId'] || $item['statId'])
            // ---------------------------------------------------------------------------

            $codeTmp = var_export($codeTmp, true).';';

            $blockItemInitList[$sysname][] = $codeTmp;

            // Теперь необходимо проверить существование класса, вполне возможно что класс
            // удалили, а он используется в системе
            try{
                new $className();
                $buffBlockToClass[$blockId][] = [
                    'class' => $className,
                    'method' => $methodName,
                    'callParam' => $callParam
                ];
            }catch(\Exception $ex){
                // если мы тту, то файл был удалён и использовался в системе
                // нужно сообщить об этом
                echo "\tError: ".__METHOD__.PHP_EOL;
                echo "\tClass $className was remove. acId[$pAcId]".PHP_EOL;
            } // catch

        } // foreach


        // Инициализация(объявление в коде) компонентов
        $codeBuffer .= self::_getCodeCompInit($blockItemInitList);

        // Создание кода class::init()
        $codeBuffer .= self::_getInitClassCode($buffBlockToClass);
        $codeBuffer .= "\n\n?>";

        // Включаем шаблонизатор, что бы получить код страницы
        $tplSitePath = dirFunc::getSiteTplPath();
        $resSiteUrl = dirFunc::getSiteResUrl();
        $tplBlockCreator = new tplBlockCreator(
            $tplSitePath,
            $resSiteUrl,
            $blockFileList[':']['id']);

        // Данные блока Head
        $headData = self::getHeadData($pAcId);
        // Если есть контроллер, мы должны добавить вызов метода onAfterHead
        if ( $propData['controller']){
            $headData .= '<? $bodyCustom->onAfterHead(); ?>';
			$tplBlockCreator->setBodyBeginHtml('<? $bodyCustom->onAfterBody(); ?>');
        } // if

        $tplBlockCreator->setHeadData($headData);
        unset($headData);

        //$tplBlockCreator->setVaribleList($tplVarList);

        $scriptStaticData = self::getScriptStaticData();
        $scriptDynData = self::getScriptDynData();
        $tplBlockCreator->setScriptData($scriptStaticData, $scriptDynData);
        unset($scriptOnlineData, $scriptOfflineData);
		
		$blockLinkData = (new blockLinkOrm())->selectAll('blockId, linkBlockId', 'wfId=' . $wfId);
        $blockLinkData = arrays::dbQueryToAssoc($blockLinkData, 'blockId', 'linkBlockId');
		

        // Устанавливаем название файлов, которые формируют шаблон
        $tplBlockCreator->setBlockFileList($blockFileList);
        // Устанавливаем классы, которые формируют компоненты
        $tplBlockCreator->setBlockItemList($buffBlockToClass);
        // Устанавливаем ссылки, которые ссылаются на другие блоки, что бы взять компоненты от туда
        $tplBlockCreator->setBlockLinkList($blockLinkData);

        $tplBlockCreator->start($blockFileList[':']['file']);

        // Создаём php файл по шаблону
        $codeBuffer .= $tplBlockCreator->getCodeBuffer();
        $codeBuffer .= "<? echo '<!-- '.(microtime(true) - \$time).' -->'; ?>";
        if ( $propData['controller'] ){
            $codeBuffer .= '<?$bodyCustom->onDestroy();?>';
        } // if

        //exit;

        return $codeBuffer;
        // func. createFileTpl
    }

    private static function _initVarible($pAcId, $varList, $urlTreePropVar, $varComp, $varTree, &$varListRender, &$varIdtoName, &$isUsecompContTree){
        $varListCount = count($varList);
        // Бегаем по переменным
        for ($j = $varListCount - 1; $j >= 0; $j--) {
            $acItem = $varList[$j];
            // Получаем настроки переменных
            $acProp = $urlTreePropVar->selectFirst('*', 'acId=' . $acItem['id']);
            // Если ничего не найдено, выходим из обработки
            if ($acProp['varType'] == varibleModel::VARR_TYPE_NONE) {
                echo "\tError(" . __METHOD__ . "): Not set varible properties in action. AcId: {$acItem['id']}" . PHP_EOL;
                echo "\tSee: URL '?\$t=manager&\$c=action' and set put varible type in Varbile settings" . PHP_EOL;
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
                        echo "\nERROR(" . __METHOD__ . ")";
						echo "\tVarible not set in AcId: {$acItem['id']}" . PHP_EOL;
                        return;
                    }
                    $classFileData = comp::getFileType($varCompData['classFile']);
                    // Получаем методы класа
                    $nsClassMethod = comp::fullNameVarClass($classFileData, $varCompData['ns']);

                    $nsClassMethod .= '::' . $varCompData['methodName'];

                    // Если не установлен контент для компонента, возможно это может быть плохо
                    // всё зависит от логики компонента, вполне возможно может установить любое значение
                    // что бы этого NOTICE не было
                    if (!$varCompData['contId']) {
                        echo "NOTICE(" . __METHOD__ . ")" . PHP_EOL;
                        echo "\tIn varible not set contId AcId: {$acItem['id']}" . PHP_EOL;
						return;
                    } // if

                    $varListRender[$name]['comp'] = $nsClassMethod;
                    $varListRender[$name]['contId'] = $varCompData['contId'];
                    $varListRender[$name]['compId'] = $varCompData['compId'];
                } // if varType == comp
        } // foreach
		return true;
        // func. _initVarible
    }

    private static function _getCodeBlSettCust(&$codeTmp, $item, $pAcId){
        $custContId = (int)($item['custContId'] ? : $item['statId']);
        if ($custContId) {
            // Получаем настройки ветки
            $objProp = compCore::findCompPropBytContId($custContId);

            // Имя класса который задали в настройках
            $classFile = $objProp['classFile']?: '/base/'.$objProp['classname'].'.php';

            $classNameAdmin = comp::fullNameClassAdmin($classFile, $objProp['ns']);
            $compAdmObj = new $classNameAdmin('', '');

            if (method_exists($compAdmObj, 'getBlockItemParam')) {
                $compAdmObj->getBlockItemParam($codeTmp, $item['id'], $pAcId);
            } // if method_exists
        } // if $custContId
        // func. _getCodeBlSettCust
    }

    private static function _getCodeCompInit($blockItemInitList){
        $codeBuffer = '';
        // =====================================================================
        // ============ Инициализация компонентов =============================
        foreach ($blockItemInitList as $name => $obj) {
            $itemCount = count($obj);
            for ($i = 0; $i < $itemCount; $i++) {
                $codeBuffer .= 'dbus::$comp[\'' . $name . '\'] = ' . $obj[$i];
            } // for
        } // foreach.
        return $codeBuffer;
    }

    private static function _getAllWFItem($blockfile, $wfId, $pAcId){
        return $blockfile->select('id, file, file_id, wf_id as wfId, block')
            ->where('wf_id=' . $wfId . ' AND ( action_id = 0 or action_id = ' . $pAcId . ')')
            ->order('file_id, id')
            ->comment(__METHOD__)
            ->fetchAll();
        // func. __getAllWFItem
    }

    private static function _getAllBlockItem($wfId, $pAcId){
        // После того как массив блоков получили, нам вытащить настроки blockItem для всех блоков
        // В настройках храняться компоненты и их параметры
        $selectField = 'bi.id, c.ns, bi.sysname, bi.block_id, bi.compId, c.onlyFolder' .
            ',bis.tplFile, bis.classFile, bis.methodName' .
            ',bi.userReg, bi.tplAccess, bis.custContId' .
            ',bis.statId, bis.tableId, bis.varId, bis.varTableId, bi.position';

        // Получаем все залинкованные блоки для $pAcId
        $linkMainList = (new blockLinkOrm())->selectList('linkMainId', 'linkMainId', ['acId' => $pAcId]);
        $linkMainList[] = $pAcId;

        // Сортировка данных для следующего запроса
        $orderPrefix = '';
        $position = (new blockItemOrderOrm())->selectList('position', 'position', ['acId' => $linkMainList]);
        $position = implode(',', $position);
        if ($position) {
            $orderPrefix = 'field(t.id, ' . $position . '),';
        }

        /*
         Запрос состоит из двух частей.
         1 часть до union - делает выборку всех компонентов, по всем блокам в
         указаной wareframeId + кастомные компоненты в блоках по action Id
         2 часть. После union - делает выборку всех компонентов также, но уже в разрезе link
         выципляет все wf-link и также ac-link
         Сортировка глобальная по всему запросу. Если при редактировании были установки позиция,
         то они сохранятся и будут доступны через blockItemOrderOrm, для этого сверху была подготовка позиций, если
         сохранения и растоновки не было, то идёт простая сортировка по position
        */
        $sql = 'SELECT * FROM (
    (SELECT ' . $selectField . ' FROM ' . blockItem::TABLE . ' bi
        LEFT OUTER JOIN ' . blockItemSettings::TABLE . ' bis  ON bis.blockItemId = bi.id
        JOIN ' . componentTree::TABLE . ' c  ON bi.compId = c.id
        WHERE bi.wf_id = ' . $wfId . ' AND (bi.acId = 0 OR bi.acId = ' . $pAcId . '))
    UNION
        (SELECT ' . $selectField . ' FROM ' . blockLinkOrm::TABLE . ' bl
        LEFT OUTER JOIN ' . urlTreePropVar::TABLE . ' utp ON utp.acId = bl.linkMainId
        JOIN ' . blockItem::TABLE . ' bi ON ( bi.acId = bl.linkMainId OR bl.acId = 0 OR bi.acId = 0  ) AND bi.block_id = bl.linkBlockId
        LEFT OUTER JOIN ' . blockItemSettings::TABLE . ' bis ON bis.blockItemId = bi.id
        JOIN ' . componentTree::TABLE . ' c ON bi.compId = c.id
        WHERE bl.acId = '.$pAcId.' or bl.acId = 0)) t
            ORDER BY ' . $orderPrefix . ' t.position, t.id';

        //die($sql);
		
		/* 
		#Вспомогательные/объяснительные запросы
		# Выводит все компоненты, которые были созданы на уровне root-wareframe, а не action
		SELECT * FROM pr_blockitem bi where bi.acId = 0;

		# Выводит все линки на компоненты, которые были указаны для actionId = 0 и root-wareframe
		select * from pr_block_link bl WHERE  bl.acId = 3 or bl.acId = 0;
		*/

        return (new blockItem())->sql($sql)->comment(__METHOD__)->fetchAll();
        // func. _getAllBlockItem
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
            function _importJs(src, func){
				var scriptElem = document.createElement('script');
                scriptElem.setAttribute('src',src);
                scriptElem.setAttribute('type','text/javascript');
				scriptElem.onload = function() {
				  if (!this.executed) {
					this.executed = true;
					func();
				  }
				};
				scriptElem.onreadystatechange = function() {
				  var self = this;
				  if (this.readyState == "complete" || this.readyState == "loaded") {
					setTimeout(function() { self.onload() }, 0);
				  }
				};
                document.getElementsByTagName('head')[0].appendChild(scriptElem);
				// func. _importJs
            }
			
			function _importCss(src){
				var scriptElem = document.createElement('link');
                scriptElem.setAttribute('href',src);
                scriptElem.setAttribute('rel','stylesheet');
				document.getElementsByTagName('head')[0].appendChild(scriptElem);
				// func. _importCss
			}

            setTimeout(function(){
                <?php
            $dbusHeadCount = count(dbus::$head['jsDyn']);
            for( $i = 0; $i < $dbusHeadCount; $i++ ){
                 echo '_importJs("'.dbus::$head['jsDyn'][$i].'");';
            } // if
?>			for( var i in importResList["js"] ){
				_importJs(importResList["js"][i].src, importResList["js"][i].func);
			}
			for( var i in importResList["css"] ){
				_importCss(importResList["css"][i]);
			}
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
                $classObj = new $item[$i]['class']();
                if (method_exists($classObj, 'init')) {
                    $codeBuffer .= $item[$i]['class'] . '::init' . $item[$i]['callParam'] . ";\n";
                } // if
            } // for
        } // foreach
        // Если ни какого кода нет, то не делает try..catch
        if ($codeBuffer) {
            $codeBuffer = 'try{' . $codeBuffer;
            $codeBuffer .= "}catch(Exception \$ex){
    header('Status: 502 Internal Server Error');
    exit;
}";
        }
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
            's.seoData, bi.sysname name, s.linkNextUrl, bi.id,'
                . 's.linkNextTitle, c.ns, c.sysname, s.method, bis.classFile', 's')
            ->joinLeftOuter(blockItem::TABLE . ' bi', 'bi.id = s.blItemId')
            ->joinLeftOuter(componentTree::TABLE . ' c', 'c.id=bi.compId')
            ->joinLeftOuter(blockItemSettings::TABLE . ' bis', 'bis.blockItemId=bi.id')
            ->where('s.acId=' . $pAcId)
            ->comment(__METHOD__)
            ->fetchFirst();
        if (!$seoData) {
            return '<!-- ' . __METHOD__ . '() | No DATA -->';
        }

        $seoList = unserialize($seoData['seoData']);

        // Добавляем теги описания, ключевых слов и заголовка страницы
        $headData = "<?";

        if ( $seoList['descr'] ){
            $pageDescr = self::parseTag($seoList['descr']);
            $headData .= "echo '<meta name=\"description\" content=\"".$pageDescr."\" /><meta property=\"og:description\" content=\"".$pageDescr."\"/>';";
        }

        if ( $seoList['keywords'] ){
            $pageKeywords = self::parseTag($seoList['keywords']);
            $headData .= "echo '<meta name=\"keywords\" content=\"".$pageKeywords."\" />';";
        }

        if ( $seoList['title'] ){
            $pageTitle = self::parseTag($seoList['title']);
            $headData .= "echo '<title>".$pageTitle."</title><meta property=\"og:title\" content=\"".$pageTitle."\"/>';";
        }

        if ( $seoList['imgUrl'] ){
            $imgUrl = self::parseTag($seoList['imgUrl']);
            $headData .= "echo '<meta property=\"og:image\" content=\"". $imgUrl."\"/><link rel=\"image_src\" href=\"". $imgUrl."\" />';";
        }

        if ( $seoList['videoUrl'] ){
            $videoUrl = self::parseTag($seoList['videoUrl']);
            $headData .= "echo '<meta property=\"og:video\" content=\"". $videoUrl."\"/>';";
        }

        if ($seoData['sysname']) {
            $headData .= comp::fullNameClassSite($seoData['classFile'], $seoData['ns']);
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