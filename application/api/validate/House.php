<?php


namespace app\api\validate;


use think\Validate;

class House extends Validate
{
    protected $rule = [
        'pk_project'   => 'require',
        'pk_house'     => 'require',
        'pk_client'    => 'require',
        'house_name'   => 'require',
        'project_name' => 'require'
    ];


    protected $message = [
        'pk_project.require'   => '小区ID不能为空',
        'pk_house.require'     => '房产ID不能为空',
        'pk_client.require'    => '业主ID不能为空',
        'house_name.require'   => '房屋名称不能为空',
        'project_name.require' => '小区名称不能为空'
    ];
}