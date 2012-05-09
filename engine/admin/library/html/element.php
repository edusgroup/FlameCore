<?php
namespace admin\library\classes\html;

use admin\library\classes\constant;

class element {

    public static function showMessageBox(\string $p_boxname, $p_view){
        $varible = $p_view->getVarbile();
        self::boxSuccess($p_boxname, $varible);
        self::boxError($p_boxname, $varible);
        self::boxWarning($p_boxname, $varible);
        self::boxInfo($p_boxname, $varible);
    }

    public static function boxSuccess($block, $p_varible) {
        if (isset($p_varible[$block]['succes'])) {
            $text = $p_varible[$block]['succes'];
            self::printBoxSuccess($text);
        }
    }

    public static function printBoxSuccess($text, $param='') {
        echo '<div><a href="#" title="Succes box" class="box succes corners" ', $param, '><span class="close">&nbsp;</span>', $text, '</a></div>';
    }

    public static function boxError($block, $p_varible) {
        if (isset($p_varible[$block])) {
            $text = $p_varible[$block];
            $text = 'Ошибка №' . $text[0] . ': ' . $text[1];
            self::printBoxError($text);
        }
    }

    public static function printBoxError($text, $param='') {
        echo '<div><a href="#" title="Error box" class="box error corners" ' . $param . '><span class="close">&nbsp;</span>', $text, '</a></div>';
    }

    public static function boxWarning($block, $p_varible) {
        if (isset($p_varible[$block]['warning'])) {
            $text = $p_varible[$block]['warning'];
            echo '<div><a href="#" title="Error box" class="box warning corners"><span class="close">&nbsp;</span>', $text, '</a></div>';
        }
    }

    public static function boxInfo($block, $p_varible) {
        if (isset($p_varible[$block]['info'])) {
            $text = $p_varible[$block]['info'];
            echo '<div><a href="#" title="Error box" class="box info corners"><span class="close">&nbsp;</span>', $text, '</a></div>';
        }
    }
    
    /**
     * Выводит полный URL директории в которой мы находимся
     * @param integer $pCompID 
     */
    /*public static function treeURLArray2str(array $pHistory, integer $pCompID) {
        if (!$pHistory)
            return '';
        $result = '';
        
        $i_count = count($pHistory);
        if ( $i_count > 1)
            foreach( $pHistory as $id => $text ){

            //for($i = 0; $i < $i_count; $i++ ){
                //$text = current($pHistory);
                //$id = key($pHistory);
                $result .= ' / <a href="?action=' . constant::CONTENT_TREE_VIEW . '&compid=' . $pCompID . '&treeid=' . $id . '" onclick="return treeLinkClick(this);">' . $text . '</a>';
               // next($pHistory);
            }
        //if ($i_count > 0){
        if ($i_count > 0){
            $text = end($pHistory);
            $result .= ' / '.$text;
        }
        return '@TODO: доделать';//$result;
    }*/

}

?>