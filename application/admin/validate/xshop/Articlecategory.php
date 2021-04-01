<?php

namespace app\admin\validate\xshop;

use think\Validate;

class Articlecategory extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'title|标题' => 'require'
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['title'],
        'edit' => ['title'],
    ];
    
}
