<?php

namespace app\admin\model\xshop;

use think\Model;


class Article extends Model
{

    

    

    // 表名
    protected $name = 'xshop_article';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 追加属性
    protected $append = [
        'create_time_text',
        'update_time_text',
    ];
    
    public static function getList($params) {
        extract($params);
        $model = new self;
        if (!empty($kw)) $model->where('title', '%LIKE%', $kw);
        return $model->paginate(10);
    }
    

    public function categories()
    {
        return $this->belongsToMany(Articlecategory::class, 'xshop_articles_categories', 'category_id', 'article_id');
    }

    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUpdateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['update_time']) ? $data['update_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
