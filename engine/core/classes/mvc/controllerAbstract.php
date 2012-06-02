<?php

namespace core\classes\mvc;

use core\classes\render;
use core\classes\request;

/**
 * Description of controllerAbstract
 *
 * @author Козленко В.Л.
 */
abstract class controllerAbstract extends request {

    //protected $extObj;

    public function __construct(string $pTplPath, string $pThemeResUrl) {
        // TODO: Проверить нужны ли эти пути
        
        //$componentTplPath = sprintf(DIR::TPL_COMPONENT_ADMIN, SITE::THEME_NAME);
        $this->view = new render($pTplPath, $pThemeResUrl);
        $this->init();
    }

    public function setSiteName($pSiteName){
        $this->view->setVar('$siteName', $pSiteName);
    }

    public function setVar(string $pName, $pValue, $pSafe = true) {
        $this->view->setVar($pName, $pValue, $pSafe);
    }

    public function varList($pList) {
        foreach ($pList as $key => $val) {
            self::setVar($key, $val);
        }
    }

    public function setJSON(string $pName, $pValue) {
        $this->view->setJSON($pName, $pValue);
    }

    public function render() {
        $this->view->render();
    }

    // 
    /*public function setError(integer $pCode, string $pMessage) {
        if ($this->view->getRenderType() == render::JSON) {
            self::setVar('json', array('error' => array('code' => $pCode, 'msg' => $pMessage)));
        } else {
            // TODO: Сделать нормамальные вывод
            //$this->view->setVar('errData', array($pCode, $pMessage));
            self::setVar('errData', array($pCode, $pMessage));
            header('Content-Type: text/html; charset=UTF-8');
            print 'Exception: ' . $pMessage . "<br/>\n";
            //echo nl2br($e->getTraceAsString());
            exit;
        }
    }*/

    /**
     * Вызываем у контроллера public метод помеченных как Action т.е. 
     * что бы методы был доступ для вызова он должен иметь структру 
     * {methodName}Action
     * @param string $pMethodName имя метода
     * @throws \Exception в случае если метод не найден
     */
    public function callMethod(string $pMethodName){
        $methodName = $pMethodName ? $pMethodName . 'Action' : 'indexAction';
        if ( !method_exists($this, $methodName)){
            throw new \Exception('Method ' . $methodName . ' not found', 27);
        }
        $this->{$methodName}();
    }

//    public static function initStorage(string $pType) {
//        // TODO: написать как то более программно, без хардкода
//        $file = DIR::CORE . 'engine/classes/storage/' . $pType . '.php';
//        if (!is_readable($file)) {
//            throw new \Exception('Storage: ' . $pType . ' не найден или нет доступа', 24);
//        }
//        return include($file);
//    }
//
//    protected function addController($pObj) {
//        $this->extObj = $pObj;
//    }
//    public function __call($pName, $pArgs) {
//        call_user_func_array(array($this->extObj, $pName), $pArgs);
//    }

//    public function methodExists(string $pMethod) {
//        return method_exists($this->extObj, $pMethod) ? : method_exists($this, $pMethod);
//    }

    /**
     * Редирект на ресурс
     * @param string $pURL URL ресурса 
     */
//    public function redirect(string $pURL) {
//        header('Location: ' . $pURL);
//        exit;
//    }

    public abstract function init();
}

?>
