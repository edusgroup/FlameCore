<?php

namespace engine\classes\html;

class table {

    private static $attribute;

    public function init($p_attribute='') {
        $this->attribute = $p_attribute;
    }

    public static function render(array $p_data, array $p_head) {
        echo '<TABLE ', self::$attribute, '><THEAD>';
        $i_count = count($p_head);
        for ($i = 0; $i < $i_count; $i++) {
            echo '<TH>', $p_head[$i], '</TH>';
        }
        echo '</THEAD><TBODY>';
        $i_count = count($p_data);
        for ($i = 0; $i < $i_count; $i++) {
            self::TR($i, $p_data[$i]);
        }
        echo '</TBODY></TABLE>';
    }

    public static function TR(integer $tr_num, array $p_data) {
        echo '<TR>';
        $i_count = count($p_data);
        for ($i = 0; $i < $i_count; $i++) {
            self::TD($tr_num, $i, $p_data[$i]);
        }
        echo '</TR>';
    }

    public static function TD(integer $tr_num, integer $td_num, string $p_data) {
        echo '<TD>', $p_data, '</TD>';
    }

}

?>
