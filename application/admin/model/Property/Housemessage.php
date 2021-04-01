<?php

namespace app\admin\model\property;

use think\Model;


class Housemessage extends Model
{

    

    

    // 表名
    protected $name = 'housemessage';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'lookswitch_text'
    ];
    

    
    public function getLookswitchList()
    {
        return ['0' => __('Lookswitch 0'), '1' => __('Lookswitch 1')];
    }


    public function getLookswitchTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['lookswitch']) ? $data['lookswitch'] : '');
        $list = $this->getLookswitchList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
