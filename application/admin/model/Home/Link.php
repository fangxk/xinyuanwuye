<?php

namespace app\admin\model\Home;

use think\Model;


class Link extends Model
{

    

    

    // 表名
    protected $name = 'link';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'showswitch_text',
        'topswitch_text'
    ];

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }
    

    public function getShowswitchList()
    {
        return ['1' => __('Showswitch 1'), '0' => __('Showswitch 0')];
    }

    public function getTopswitchList()
    {
        return ['1' => __('Topswitch 1'), '0' => __('Topswitch 0')];
    }


    public function getShowswitchTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['showswitch']) ? $data['showswitch'] : '');
        $list = $this->getShowswitchList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTopswitchTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['topswitch']) ? $data['topswitch'] : '');
        $list = $this->getTopswitchList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
