<?php

namespace app\admin\model\xshop;

use think\Model;
use fast\Tree;

class Articlecategory extends Model
{

    // 表名
    protected $name = 'xshop_article_category';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
    ];
    

    public function articles()
    {
        return $this->belongsToMany(Articlescategories::class);
    }
    

    public static function getTreeArray() {
        $tree = Tree::instance();
        $tree->init(collection(self::select())->toArray(), 'parent_id');
        return $tree->getTreeArray(0);
    }

    public static function getTreeList() {
        $tree = Tree::instance();
        $tree->init(collection(self::select())->toArray(), 'parent_id');
        return $tree->getTreeList($tree->getTreeArray(0), 'title');
    }






}
