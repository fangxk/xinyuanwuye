<?php

namespace addons\xshop\controller;

use addons\xshop\model\ProductModel;
use addons\xshop\model\ProductSkuModel;
use addons\xshop\model\FavoriteModel;
use addons\xshop\validate\ProductValidate;
use addons\xshop\model\ReviewModel;
use think\Db;

/**
 * 商品
 * @ApiWeigh (5)
 */
class Product extends Base
{
    protected $needLogin = ['favorite'];
    /**
     * 获取商品信息
     * @ApiParams (name="id", type="integer", required=true, description="商品id")
     */
    public function index()
    {
        return $this->success('', ProductModel::info((int)input('id')));
    }
    /**
     * 获取商品评价信息
     * @ApiParams (name="id", type="integer", required=true, description="商品id")
     */
    public function getReviews()
    {
        $params = $this->request->get();
        $result = $this->validate($params, ProductValidate::class . '.id');
        if (true !== $result) {
            return $this->error($result);
        }
        return $this->success('', ReviewModel::getList($params));
    }

    /**
     * 商品列表
     * @ApiParams (name="cat_id", type="integer", required=false, description="商品分类")
     * @ApiParams (name="kw", type="string", required=false, description="关键词")
     * @ApiParams (name="sort", type="integer", required=false, description="排序方式：0、默认排序，1、销量排序，2、价格排序")
     * @ApiParams (name="priceOrder", type="integer", required=false, description="1、价格升序，2、价格降序")
     */
    public function getList()
    {
        $params = $this->request->get();
        return $this->success('', ProductModel::getList($params));
    }

    /**
     * 获取首页推荐商品
     */
    public function getHomeProducts()
    {
        return $this->success('', ProductModel::getHomeProducts());
    }

    /** 
     * 收藏/取消收藏商品
     * @ApiMethod (POST)
     * @ApiHeaders (name=Xshop-Token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="id", type="integer", required=true, description="商品id")
     * @ApiParams (name="state", type="integer", required=false, description="1、收藏，0、取消收藏")
     */
    public function favorite()
    {
        $params = $this->request->post();
        $result = $this->validate($params, ProductValidate::class . '.favorite');
        if (true !== $result) {
            return $this->error($result);
        }
        return $this->success('', FavoriteModel::add($params));
    }
}
