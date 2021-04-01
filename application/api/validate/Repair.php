<?php

namespace app\api\validate;

use think\Validate;

class Repair extends Validate
{
    protected $rule = [
        /*'billtype' => 'require',*/
        'clientid'  =>'require',
        'houseid'  =>'require',
        'content'  => 'require',
        'isPub'    => 'require',
        'mobile'   => 'require|max:11'
    ];


    protected $message = [
        /*'billtype.require' => '请选择报修类型',*/
        'clientid'         => '请选择业主ID',
        'houseid'          => '请选择房屋ID',
        'content.require'  => '请输入报修内容',
        'isPub.require'    => '请输入报修区域',
        'mobile.require'   => '请输入手机号码',
        'mobile.max'       => '手机号格式不正确'
    ];
}