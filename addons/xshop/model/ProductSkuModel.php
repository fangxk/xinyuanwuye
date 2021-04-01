<?php

namespace addons\xshop\model;

use think\Model;

class ProductSkuModel extends Model
{
    protected $name = "xshop_product_sku";

    protected $append = [
        'price', 'image'
    ];

    public static function getListBySkuIds($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        return self::with('product')->where('id', 'IN', $ids)->select();
    }

    public function getPriceAttr($value, $data)
    {
        \think\Hook::listen('xshop_get_sku_price', $data);
        return $data['price'];
    }

    public function getImageAttr($value, $data)
    {
        return empty($data['image']) ? "" : cdnurl($data['image'], true);
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'id');
    }
}
