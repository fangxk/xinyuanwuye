<?php

namespace app\api\model;

use think\Model;

class User extends Model
{
    protected $name = 'user';

    protected $autoWriteTimestamp = true;

    protected $createTime = 'create_time';

    protected $updateTime = false;


    public static function doesUserExists($openid)
    {
        return self::get(['openid' => $openid]);
    }

    public static function doesUserInfo($id)
    {
        return self::get(['id' => $id]);
    }
}