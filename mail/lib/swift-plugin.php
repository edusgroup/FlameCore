<?
class getEximIdPlugin implements Swift_Events_ResponseListener{
    private $_id = '';
    public function getId(){
        return $this->_id;
    }
    public function responseReceived(Swift_Events_ResponseEvent $evt){
        $resp = $evt->getResponse();
        if ( !preg_match('/250 OK id=(.*)/', $resp, $this->_id)){
            return;
        }
        //var_dump($this->_id[1]);
        $this->_id = trim($this->_id[1]);
        // func. responseReceived
    }
    // class getEximIdPlugin
}