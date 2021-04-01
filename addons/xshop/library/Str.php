<?php

namespace addons\xshop\library;

class Str 
{
    /**
     * 比较版本号大小
     */
    public static function compareVersion($val1, $val2)
    {
        $arr1 = explode('.', $val1);
        $arr2 = explode('.', $val2);
        foreach ($arr1 as $k => $v) {
            if (isset($arr2[$k])) {
                if (intval($arr2[$k]) < intval($v)) return true;
                else {
                    continue;
                }
            } else {
                return false;
            }
        }
        return false;
    }
}