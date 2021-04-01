<?php

namespace app\admin\model\xshop;

use think\Model;

class Template extends Model
{

    

    

    // 表名
    protected $name = 'xshop_template';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    
    public static function getContent($code, $params = [])
    {
        $tpl = self::where('code', $code)->find();
        if (empty($tpl)) {
            throw new \think\Exception("模板不存在");
        }
        $view = \think\View::instance();
        $view->engine->layout(false);
        return $view->display($tpl->content, $params);
    }
}
