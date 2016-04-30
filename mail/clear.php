<?
/**
 * Class clearMail
 * Очистка БД от сообщение которые
 * были не доставлены и висят в очереди EXIM-a более 24 часов
 * и были возвращены сервером как Bounce message
 *
 * Запускать необходимо раз в день, читаются логи за вчерашний день
 */

class clearMail{
    const ITEM_PROJECT_MAIL = 2;
    const ITEM_MAIL = 3;
    private static $_tableList;
    private static $_mongoHandle;
    
    public function init(){
        try{
            self::$_mongoHandle = new \MongoClient("mongodb://localhost");
        }catch(MongoConnectionException $ex){
            echo 'ERROR[5]: '.$ex->getMessage().PHP_EOL;
            exit;
        }
        // func. init
    }

    /**
     * Удаление сообщений которые висят в очереди EXIM более 24 часов
     */
    public static function removeBadMessage(){
        exec('/usr/sbin/exiqgrep -o 86400', $output);
        $output = implode(' ', $output);

        preg_match_all('/\w+h\s+[^\s]+\s+([^\s]+)\s+<([^>]+)>\s+([^\s]+)/', $output, $logList, PREG_SET_ORDER);

        self::$_tableList = [];

        foreach($logList as $item){
            $projectEmail = $item[self::ITEM_PROJECT_MAIL];
            $email = $item[self::ITEM_MAIL];
            if ( isset(self::$_tableList[$projectEmail])){
                $table = self::$_tableList[$projectEmail];
            }else{
                $baseData = self::$_mongoHandle->email_base->clients->findOne(['eximMail'=>$projectEmail], ['_id'=>0, 'dbName'=>1]);
                if ( !$baseData ){
                    continue;
                }
                $table = $baseData['dbName'];
                self::$_tableList[$projectEmail] = $baseData['dbName'];
            } // if ( isset(self::$_tableList[$email]))

			// TODO: Удалить во всех таблицах
            self::$_mongoHandle->{$table}->users->remove(['email'=>$email]);
        } // foreach($logList as $item)

        exec('/usr/sbin/exiqgrep -o 86400 -i | xargs /usr/sbin/exim -Mrm 2>/dev/null', $output);
        // func. removeBadMessage
    }

    /**
     * Пасирнг лога EXIM и очистка Bounce message
     */
    public function removeBounceMessage(){
        $msgList = [];
        self::$_tableList = [];

        $date = new DateTime();
        $date->sub(new DateInterval('P1D'));
        $fileLog = '/var/log/exim4/exim-main-'.$date->format('Ymd').'.log';
        if ( !is_file($fileLog)) {
            return;
        }
        $fr = fopen($fileLog, 'r');
        while(!feof($fr)){
            $line = fgets($fr);
            /**
             * Format
             * 1WOS9l-0002hZ-Qv <= user@domian.ru U=user P=local-smtp S=9228 id=2-15-8-12-a4650edf5fe13c2d591974a248e2d8a3@swift.generated
             */
            if ( preg_match('/([^\s]+)\s+<=\s+[^\s]+.*id=(\d+)-\d+-(\d+)/', $line, $data) ){
                /**
                 * Format
                 * $data[1] - Message Id in Exim
                 * $data[2] - Client Id in DB
                 * $data[3] - User Id in DB
                 */
                $msgList[$data[1]] = [$data[2],  $data[3]];
            }elseif (preg_match('/([^\s]+)\s+Completed/', $line, $data)){
                /**
                 * Format
                 * $data[1] - Message Id in Exim
                 */
                unset($msgList[$data[1]]);
            }elseif (preg_match('/([^\s]+)\s+\*\*/', $line, $data)){ // Bounce
                /**
                 * Format
                 * $data[1] - Message Id in Exim
                 */
                if ( !isset($msgList[$data[1]])){
                    continue;
                }
                $clientData = $msgList[$data[1]];
                $clientId = (int)$clientData[0];
                if ( isset(self::$_tableList[$clientId])){
                    $table = self::$_tableList[$clientId];
                }else{
                    $baseData = self::$_mongoHandle->email_base->clients->findOne(['num'=>$clientId], ['_id'=>0, 'dbName'=>1]);
                    if ( !$baseData ){
                        continue;
                    }
                    $table = $baseData['dbName'];
                    self::$_tableList[$clientId] = $baseData['dbName'];
                } // if ( isset(self::$_tableList[$email]))

				// Удалить во всех таблицах
                $data = self::$_mongoHandle->{$table}->users->remove(['num'=>(int)$clientData[1]]);
            } // if

        } // wwhile(!feof($fr))
        fclose($fr);

        // func. removeBounceMessage
    }
} // class clearMail

clearMail::init();

clearMail::removeBadMessage();
clearMail::removeBounceMessage();
