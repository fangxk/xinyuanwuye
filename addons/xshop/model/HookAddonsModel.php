<?php

namespace addons\xshop\model;

class HookAddonsModel extends Model
{

    protected $name = 'xshop_hook_addons';

    public static function getAddons()
    {
        $list = self::where('status', 1)->group("addon_name")->column('addon_name');
        $res = [];
        foreach ($list as $item) {
            $addon = get_addon_info($item);
            if (!empty($addon) && $addon['state'] == 1) $res[] = $item;
        }
        $addons = get_addon_list();
        $list1 = [];
        foreach ($addons as $k => $v) {
            if (!empty($v['dep']) && $v['dep'] == 'xshop' && !in_array($k, $res) && $v['state'] == 1) $res[] = $k; 
        }
        return $res;
    }

    public static function disable($addon_name) {
        return self::where('addon_name', $addon_name)->update(['status' => 0]);
    }
}
