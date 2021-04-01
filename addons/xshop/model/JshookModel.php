<?php

namespace addons\xshop\model;

use addons\xshop\exception\Exception;
use addons\xshop\exception\NotFoundException;
use addons\xshop\exception\NotLoginException;

class JshookModel extends Model
{
    protected $name = 'xshop_jshook';
    
    public static function enable($addon)
    {
        return self::where('addon_name', $addon)->update(['state' => 1]);
    }

    public static function disable($addon)
    {
        return self::where('addon_name', $addon)->update(['state' => 0]);
    }
}
