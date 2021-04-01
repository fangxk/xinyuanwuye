<?php

namespace app\admin\validate\xshop;

use think\Validate;

class Article extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'title' => 'require',
        'categories|栏目' => 'require'
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
        'add'  => ['title', 'categories'],
        'edit' => ['title', 'categories'],
    ];
    
}
