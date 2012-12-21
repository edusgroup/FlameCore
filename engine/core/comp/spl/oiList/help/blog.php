<?php

namespace core\comp\spl\oiList\help;

trait blog{

    protected static function getPaginationList($pPageNum, $pCount){
        $maxCount = 8;

        $firstNum = $pPageNum - $maxCount / 2;
        $firstNum  = $firstNum < 1 ? 1 : $firstNum;
        $lastNum = $pPageNum + $maxCount / 2;
        $lastNum = $lastNum > $pCount ? $pCount : $lastNum;

        // Корректировка значений, так начальные и последнии позиции особенные
        $korect = $pPageNum - $firstNum + $lastNum - $pPageNum;
        $korect = $maxCount - $korect - 1;
        $firstNum -= $pPageNum > $pCount - $maxCount / 2 ? $korect : 0;
        $firstNum  = $firstNum < 1 ? 1 : $firstNum;
        $lastNum += $pPageNum < $maxCount / 2 ? $korect : 0;
        $lastNum = $lastNum > $pCount ? $pCount : $lastNum;

        $prev = $firstNum != 1;
        $next = $lastNum != $pCount;

        return ['count' => $pCount,
                'firstNum' => $firstNum,
                'lastNum' => $lastNum,
                'prev' => $prev,
                'next' => $next];
        // func. getPaginationList
    }

    // func. blog
}