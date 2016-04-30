<?
use core\classes\request;
use core\classes\dbus;
use core\classes\DB\DB as DBCore;
use core\classes\password;
use core\classes\filesystem;
use core\classes\render;

// ORM
use ORM\tree\compContTree;
use ORM\users as usersOrm;

// Conf
use site\conf\DIR;

use core\classes\validation\word;

if(!defined('STDIN')){
    die('Run only console'.PHP_EOL);
}

include 'lib/swift/swift_required.php';
include 'lib/swift-plugin.php';
//include 'lib/class.phpmailer.php';

// Config DIR
include 'conf/DIR.php';

//include
include DIR::CORE . 'site/function/autoload.php';

include DIR::CORE . 'core/function/errorHandler.php';

try{
    $mongoHandle = new MongoClient("mongodb://localhost");
}catch(MongoConnectionException $ex){
    echo 'ERROR[5]: '.$ex->getMessage().PHP_EOL;
    exit;
}

/*
email_base.clients
   tplDir - Директория с шаблонами
   logDir - Директория для логов
   unsubTplIndex - шаблон для отписки
      ( если null, то будет взят common/index.html из unsubscribe.marketingforyou.ru/tpl/form )
   unsubTplDo - что показывать, если клиент отписался
      ( если null,  то будет взят common/do.html из unsubscribe.marketingforyou.ru/tpl/form )
   userCount - количество клиентов

 */

/*
 * <attach src="res/data.jpg"/>
 * Для атача
 *
 */

/*
 * Переменные
 * unsuburl - URL для описки
 * abName - Имя абонента
 */

/**
 * Формат
 * db-mail.php -с Показать список клиентов и выйти
 * db-mail.php -n={clientName} -t=user Показать список клиентов и выйти
 * db-mail.php -r Запуск рассылки
 * db-mail.php -r -n={clientName} Запуск рассылки по конкретному клиенту
 */

$shortopts  = '';
$shortopts .= 'l::';  // Показать только список текущих действий
$shortopts .= 'r::';  // Запуск текищих действий
$shortopts .= 'n::';  // Запуск текищих действий
$shortopts .= 't::';  // Запуск текищих действий
$shortopts .= 'c::';  // Список клиентов

$cmdOption = getopt($shortopts);

$onlyShowClient = isset($cmdOption['c']);
if ( $onlyShowClient ){
	$clients = $mongoHandle->email_base->clients->find([], ['_id'=>0]);
	echo 'Client: '.PHP_EOL;
	foreach($clients as $client){
		echo $client['name'].PHP_EOL;
	}
	exit;
}

if ( !isset($cmdOption['n']) || !isset($cmdOption['t'])){
    die('Use -n=client-name -t=table'.PHP_EOL);
}

$table = $cmdOption['t'];

$isFakeTest = true && !isset($cmdOption['r']);
$clientQuery = ['name'=>$cmdOption['n']];


if ( !$isFakeTest ){
    //$transport = Swift_SmtpTransport::newInstance('127.0.0.1', 25);
    $transport = Swift_SendmailTransport::newInstance('/usr/sbin/exim -bs');
    $mailer = Swift_Mailer::newInstance($transport);
} // if ( !$isFakeTest )


$client = $mongoHandle->email_base->clients->findOne($clientQuery, ['_id'=>0]);
if (!$client){
    die('Client not found');
}


if ( !$isFakeTest ){
    $render = new render('', '');
}

$dbHandle = $mongoHandle->selectDB($client['dbName']);

echo 'Name: '.$client['name'].'['.$client['num'].'] '.$client['dbName'].PHP_EOL;
//echo 'Tpl: '.$client['tplDir'].PHP_EOL;
//echo 'Log: '.$client['logDir'].PHP_EOL;

$subscribeId = 'default';

// TODO Добавить первым рассылку по jobs
$subscribe = $dbHandle->subscribes->findOne(['enable'=>0, '_id'=>$subscribeId], []);

if ( !$subscribe){
	die('Error: subscribes "'.$subscribeId.'" not found'.PHP_EOL);
}

$isExist = $dbHandle->system->namespaces->findOne(['name'=>$client['dbName'].'.'.$table]);
if ( !$isExist ){
	die('Error: Table "'.$table.'" not found in subscribe "'.$subscribeId.'"'.PHP_EOL);
}

$tableNum = array_search($table, $subscribe['table']);
if ( $tableNum === FALSE ){
	die('Error: Number of table "'.$table.'" not found in subscribe.table "'.$subscribeId.'"'.PHP_EOL);
}

/*if ( $emailSender->checkSubscribe($subscribe)){
	continue;
}*/

echo "\tName: ".$subscribe['name'].PHP_EOL;
echo "\tEnable: ".$subscribe['enable'].PHP_EOL;
echo "\tFromName: ".$client['fromName'].' '.$client['fromMail'].PHP_EOL;
echo "\tTable: ".$table.PHP_EOL;
echo "\tTpl: ".$subscribe['tplFile'].PHP_EOL;
echo "\tImgDir: ".$client['imgDir'].PHP_EOL;

$userCount = $dbHandle->{$table}->find(['_id' => ['$ne'=>'uniqnum']])->count();
echo "\tUserCount: ".$userCount.PHP_EOL;

// Есть ли файл шаблона
$tplFile = $client['tplDir'].$subscribe['tplFile'];
if ( !is_readable($tplFile) || filesize($tplFile) < 100 ){
	die('ERROR[4]: File '.$tplFile.' not found'.PHP_EOL);
}

// Папка с изображениями
if ( isset($client['imgDir']) && $client['imgDir'] && !is_dir($client['imgDir'])){
	die( 'ERROR[3]: Img dir '.$client['imgDir'].' not found'.PHP_EOL);
}

// Переходим в папку с изображениями
chdir($client['imgDir']);

// Если это не фейковый запуск
if ( !$isFakeTest ){
	// Добавляем в таблицу логов запись о запуске
	$sLogData = ['sid' => $subscribe['_id'], 'time'=>(int)microtime(true), 'table'=>$table];
	$ret = $dbHandle->subscribeLogs->findAndModify(['_id' => 'uniqnum'], ['$inc'=>['seq'=>1]], ['seq'=>1, '_id'=>0], ['new'=>true,'upsert'=>true]);
	$sLogData['num'] = $ret['seq'];
	$sLogId = $dbHandle->subscribeLogs->insert($sLogData);

	// Убераем доступность рассылки
	$dbHandle->subscribes->update(['_id'=>$subscribe['_id']], ['$set'=>['enable'=>0]]);

	$logdir = $client['logDir'].$sLogData['_id'];
	// Создаём папку для логов
	filesystem::mkdir($logdir);
	//chmod($logdir, 0777);  // восьмеричное, верный способ

	// Файл для логов
	$flogw = fopen($logdir.'/send.txt', 'w');

} // if ( !$isFakeTest )


// Вытаскиваем все валидных и не проверенных пользователей
try{
	// DEBUG
	// , 'num'=>['$gte' => 84 ]
	$users = $dbHandle->{$table}->find(['_id'=>['$ne'=>'uniqnum']], []);
}catch(Exception $ex){
	die('ERROR[10]: '.$ex->getMessage().PHP_EOL);
}

//foreach ($users as $user){
$sendUserCount = $users->count(true);
echo "\tRealCount = $sendUserCount".PHP_EOL;

if ( $isFakeTest ){
	exit;
}

$is421error = false;
for( $i = 0; $i < $sendUserCount; $i++ ){
	if ( !$is421error ){
		$user = $users->getNext();
	}else{
		$is421error = false;
	}

	/*if ( $emailSender->checkUserEmail($user)){
		continue;
	}*/
	$subscribe['vars']['userName'] = isset($user['userName'])?$user['userName']:'';
	$subscribe['vars']['userId'] = isset($user['num'])?$user['num']:0;
	$subscribe['vars']['userEmail'] = $user['email'];

	$render->clear();
	$render->setTplPath($client['tplDir']);
	$render->setContentType('');
	$render->setMainTpl($subscribe['tplFile']);

	$listUnsubscribe = $client['List-Unsubscribe'];

	if ( isset($subscribe['vars'])){
		foreach($subscribe['vars'] as $varName => $varValue){
			//$subscribe['subject'] = str_replace('%'.$varName, $varValue, $subscribe['subject']);
			$listUnsubscribe = str_replace('%'.$varName, $varValue, $listUnsubscribe);
			$render->setVar($varName, $varValue);
		} // foreach($subscribe['vars'] as $varname => $value)
	} // if ( isset($subscribe['vars']))
	
	$listUnsubscribe = str_replace('%userId', $user['num'], $listUnsubscribe);
	$listUnsubscribe = str_replace('%userEmail', $user['email'], $listUnsubscribe);

	$uid = $client['num'].'-'.$sLogData['num'].'-'.$user['num'].'-'.$tableNum;
	$render->setVar('uid', $uid);
	//$render->setVar('abName', $abName);
	$render->setVar('unsuburl', 'http://unsubscribe.marketingforyou.ru/?uid='.$uid);
	$render->setVar('statimg', '<img src="http://unsubscribe.marketingforyou.ru/stat/?uid='.$uid.'" width="1" height="1"/>', false);

	ob_start();
	$render->render();
	$htmlCode = ob_get_clean();

	try {
		preg_match('/<subject[^>]+value=["\']([^\'""]+)["\']/m', $htmlCode, $resData);
		if ( isset($resData[1])){
			$htmlCode = preg_replace('/<subject[^>]+>/', '', $htmlCode);
			$message = Swift_Message::newInstance( $resData[1]);
		}else{
			die('ERROR[99]: Subject in '.$client['tplDir'].$subscribe['tplFile'].' not found'.PHP_EOL);
		}

		$headers = $message->getHeaders();
		$headers->addParameterizedHeader('Precedence', 'bulk');
		$headers->addParameterizedHeader('List-Unsubscribe', '<'.implode('>,<', $listUnsubscribe).'>');
		$headers->addParameterizedHeader('List-Id', $uid.' <MarketingForYou>');

		// Добавляем Inline изображения к письму
		preg_match_all('/<img[^>]+src=["\']+([^\'""]+)["\']/m', $htmlCode, $resData, PREG_SET_ORDER);
		foreach( $resData as $resItem ){
			if ( isset($resItem[1])){
				$imgFilename = $resItem[1][0] == '/' ? $resItem[1] : $client['imgDir'].$resItem[1];

				if ( !is_file($imgFilename)){
                    echo 'Error: '.$imgFilename.' not found'.PHP_EOL;
					continue;
				}
				$cid = $message->embed(Swift_Image::fromPath($imgFilename));
				$htmlCode = str_replace($resItem[1], $cid, $htmlCode);
			} // if ( isset($resData[0]))
		} // foreach( $resData as $resItem )

        //exit;

		// Добавляем Attach к письму
		preg_match_all('/<attach[^>]+src=["\']+([^\'""]+)["\']/m', $htmlCode, $resData, PREG_SET_ORDER);
		foreach( $resData as $resItem ){
			if ( isset($resItem[1])){
				if ( !is_file($resItem[1])){
					continue;
				}
				$htmlCode = preg_replace('/<attach[^>]+>/', '', $htmlCode);
				$message->attach(Swift_Attachment::fromPath($resItem[1]));
			} // if ( isset($resData[0]))
		}

		$message->setFrom([$client['fromMail']=>$client['fromName']])
			->setTo($user['email'])
			->setBody($htmlCode, 'text/html');
			//->setReturnPath('www.dft@mail.ru'); // Onbounce

		$msgId = $message->generateId();
		$message->setId($uid.'-'.$msgId);

		$getEximIdPlugin = new getEximIdPlugin();
		$mailer->registerPlugin($getEximIdPlugin);

		// Pass a variable name to the send() method
		$msgResponse = $mailer->send($message, $failures);
		if (!$msgResponse){
			echo "Failures:";
			print_r($failures);
		}

		$emailId = $getEximIdPlugin->getId();
		fwrite($flogw, $user['_id'].'|'.$emailId.'|'.$user['email'].'|'.$client['num'].PHP_EOL);

	} catch (Exception $e) {
		sleep(1);
		if ( $e->getCode() == 421 ){
			echo "\t\tLimit sender connection".PHP_EOL;
			$is421error = true;
			--$i;
			$transport = Swift_SendmailTransport::newInstance('/usr/sbin/exim -bs');
			$mailer = Swift_Mailer::newInstance($transport);
			continue;
		}

		echo 'ERROR[2]: '.$e->getCode().' '.$e->getMessage().PHP_EOL;
	} // catch (Exception $e)
} // for ($i)


if ( !$isFakeTest ){
	fclose($flogw);
}

// TODO Переместить в архивные рассылки
// TODO Поместить отписвшихся людей в лог в БД с пометкой в какой рассылке отказались

echo "\t---------------------------".PHP_EOL;



$mongoHandle->close();

/*if ( !$isFakeTest ){
    $mail->SmtpClose();
}*/

// http://habrahabr.ru/post/114852/

// List-Unsubscribe Header
// Precedence: bulk
// PTR   http://centralops.net/co/DomainDossier.aspx
// DMARC   http://help.mail.ru/mail-help/postmaster/dmarc
// SPF
// DKIM
// Onbounce
// Unsubscribe
// https://postmaster.mail.ru/settings/