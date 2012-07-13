<?php

namespace core\plugin\htmlTable;

use core\classes\DB\table;

/**
 * HTMLTable
 * 
 $component = new component(TABLE::COMPONENT);
 $tb1 = new htmlTable('tb1', 'table/test.tpl.php', $this->view);
 $tb1->setData($component->where(array('id>'=>9)));
 $tb1->setField(array(
      'name' => array('№', 'Название', 'Системное название')
 ));
 self::setVar('tb1', $tb1);

 <?$this->get('tb1')->render()?>
 *
 * @author Козленко В.Л.
 */
class htmlTable extends \core\classes\render {
    /**
     * Конструктор
     * @param string $pID ID таблицы
     * @param string $pTplFile файла шаблона таблицы
     */
    public function __construct(string $pID, $pTplFile=NULL, $pView=NULL) {
        if ( $pView )
            self::setViewParent($pView);
        $this->setVar('view', $pView);
        $this->setVar('id', $pID);
        $this->setVar('tplFile', $pTplFile);
    }

    public function setData($pData) {
        $this->setVar('data', $pData);
    }

    public function setField($pFieldParam) {
        $this->setVar('param', $pFieldParam);
    }

    public function thead() {
        $name = $this->getVarArr('param', 'name');
        if( $name ){
            echo '<THEAD><TR>';
            for ($i = 0; $i < count($name); $i++) {
                echo '<TH>', $name[$i], '</TH>';
            }
            echo '</TR></THEAD>';
        }
    }
    
    public function tbody() {
        $data = $this->get('data');
        if (isset($data)) {
            echo '<TBODY>';
            for ($i = 0; $i < count($data); $i++) {
                echo '<TR>';
                for ($j = 0; $j < count($data[$i]); $j++) {
                    echo '<TD>', $data[$i][$j], '</TD>';
                }
                echo '</TR>';
            }
            echo '</TBODY>';
        }
    }

    public function render() {
        $data = self::get('data');
        if ($data instanceof table) {
            $type = $this->get('type') ? : table::FETCH_NUM;
            self::setVar('data', $data->fetchAll($type));
        }
        
        $tplFile = $this->get('tplFile');
        if (!$tplFile) {
            echo '<TABLE id="', $this->get('id'), '">';
            self::thead();
            self::tbody();
            echo '</TABLE>';
        }else{
            parent::render($tplFile);
        }
    }

}