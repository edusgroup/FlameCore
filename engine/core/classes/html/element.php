<?php
namespace core\classes\html;

class element {

    private static $cssList;

    public static function selectIdName($pData, $pAttributes = '', $pSelectValue = null) {
        if (!$pData)
            return;
        if ($pSelectValue === null && isset($pData['val'])) {
            $pSelectValue = $pData['val'];
        }

        $list = $pData['list'];
        echo '<SELECT ', $pAttributes, '>';
        foreach ($list as $item) {
            $selected = $item['id'] == $pSelectValue ? ' selected="selected"' : '';
            echo '<OPTION VALUE="', $item['id'], '"', $selected, '>', $item['name'], '</OPTION>';
        }
        echo '</SELECT>';
        // func. selectIdName
    }

    public static function select($pData, $pAttributes = '', $pSelectValue = -1) {
        if (!$pData)
            return;
        $selValue = $pSelectValue;
        if ($selValue == -1 && isset($pData['val'])) {
            $selValue = $pData['val'];
        }
        $list = $pData['list'];
        echo '<SELECT ', $pAttributes, '>';
        foreach ($list as $value) {
            $selected = $value == $selValue ? ' selected="selected"' : '';
            echo '<OPTION VALUE="', $value, '"', $selected, '>', $value, '</OPTION>';
        }
        echo '</SELECT>';
        // fucn. select
    }

    public static function selectKeyName($pData, $pAttributes = '', $pSelectValue = -1) {
        if (!$pData)
            return;
        $selValue = $pSelectValue;
        if ($selValue == -1 && isset($pData['val'])) {
            $selValue = $pData['val'];
        }
        $list = $pData['list'];
        echo '<SELECT ', $pAttributes, '>';

        foreach ($list as $key => $value) {
            $selected = $key == $selValue ? ' selected="selected"' : '';
            echo '<OPTION VALUE="', $key, '"', $selected, '>', $value, '</OPTION>';
        }
        echo '</SELECT>';
        // fucn. selectKeyName
    }

    // TODO: Подумать о необходимости
    public static function printCSS($p_param = '') {
        $i_count = count(self::$cssList);
        for ($i = 0; $i < $i_count; $i++) {
            echo '<LINK rel="stylesheet" type="text/css" href="', self::$cssList[$i], '" ', $p_param, '/>';
        }
        // func. printCSS
    }

    public static function dirList2Select(array $pList) {
        $iCount = count($pList);
        $return = [];
        for ($i = 0; $i < $iCount; $i++) {
            $return[] = ['id' => $pList[$i], 'name' => $pList[$i]];
        }
        return $return;
        // func. dirList2Select
    }

    public function addCSS($p_filename) {
        self::$cssList[] = $p_filename;
    }

    public static function clearCSS() {
        unset(self::$cssList);
    }

    public static function checkbox($pParam, $pChecked = false, $pDisable = false) {
        $pChecked = $pChecked ? 'checked="" ' : '';
        $pDisable = $pDisable ? 'disabled="" ' : '';
        return '<INPUT TYPE="checkbox" ' . $pParam . $pChecked . $pDisable . '/>';
        // func. checkbox
    }

    public static function radio($pParam, $pChecked = false, $pDisable = false) {
        $checked = $pChecked ? ' checked="" ' : '';
        $disable = $pDisable ? ' disabled="" ' : '';
        return '<INPUT TYPE="radio" ' . $pParam . $checked . $disable . '/>';
        // func. radio
    }

    public static function text($pParam, $pValue = null, $pDisable = false) {
        $pValue = $pValue !== null ? ' value="' . $pValue . '" ' : '';
        $pDisable = $pDisable ? ' disabled="" ' : '';
        return '<INPUT TYPE="text" ' . $pParam . $pDisable . $pValue . '/>';
        // func. text
    }

    public static function textarea($pParam, $pValue = '', $pDisable = false) {
        $pDisable = $pDisable ? ' disabled="" ' : '';
        return '<TEXTAREA ' . $pParam . $pDisable . '>' . $pValue . '</TEXTAREA>';
        // func. textarea
    }

    public static function img($pUrl, $pParam = '') {
        if (!$pUrl) {
            return;
        }
        return '<IMG SRC="' . $pUrl . '" ' . $pParam . '/>';
        // func. img
    }

    //public static function var2js($pVar){
    //    
    //}

    /**
     * Создаем меню
     *
     * @param mixed $p Многомерный массив с данными о меню.
     * $['current'] - содержит текущее выделенно меню
     * $['menu'][]['name'] - имя меню
     * $['menu'][]['href'] - ссылка
     * $['sub'][]['href']  - Многомерный массив на подменю.
     *
     */
    public static function printMenu($p) {
        if (!$p)
            return;
        $current = isset($p['current']) ? $p['current'] : -1;
        $menu = $p['menu'];
        $i_count = count($menu);
        for ($i = 0; $i < $i_count; $i++) {
            echo '<li';
            if ($i == $current)
                echo ' class="current"';
            echo '><a href="', $menu[$i]['href'], '">';
            echo $menu[$i]['name'];
            echo '</a>';
            if (isset($menu[$i]['sub'])) {
                echo '<ul class="subnav">';
                self::printMenu($menu[$i]['sub']);
                echo '</ul>';
            }
            echo '</li>';
        }
        // func. printMenu
    }
    // class element
}