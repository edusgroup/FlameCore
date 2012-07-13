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

    public abstract function init();
}