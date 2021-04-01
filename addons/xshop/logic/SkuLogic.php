<?php

namespace addons\xshop\logic;

use addons\xshop\model\ProductSkuModel;
use addons\xshop\model\DeliveryModel;
use addons\xshop\exception\NotFoundException;

class SkuLogic
{
    protected $skus = []; // ProductSku::class
    public $products = []; // <list> CartModel::class
    public $products_price = 0; // 商品总价
    public $delivery_price = 0; // 运费
    public $discount_price = 0; // 总优惠
    public $order_price = 0; // 订单最终价格
    protected $address = null;

    public static function instance()
    {
        return new self;
    }
    public function product($products)
    {
        $this->products = $products;
        return $this;
    }
    /**
     * 计算商品实际总价格
     */
    public function clacRealProductsPrice()
    {
        $total = 0;
        foreach ($this->products as $product) {
            $total += $product->quantity * $product->sku['price'];
        }
        $this->products_price = number_format($total, 2, '.', '');
        return $this;
    }

    /**
     * 计算运费
     */
    public function clacDeliveryPrice($area_id)
    {
        $price = 0;
        foreach ($this->products as $cart) {
            $price += $this->calcProductDeliveryPrice($cart, $area_id);
        }
        $this->delivery_price = $price;
        \think\Hook::listen('xshop_calc_products_delivery_price', $this);
        return $this;
    }

    /**
     * 计算单个商品运费
     */
    protected function calcProductDeliveryPrice($cart, $area_id)
    {
        $price = 0;
        list($rule, $delivery) = $this->getDeliveryRule($cart, $area_id);
        switch ($delivery->type) {
            case 0: { // 按重量计费
                $weigh = $cart['quantity'] * $cart['sku']['weight'];
                $rest_weigh = ceil($weigh - 1);
                $rest_weigh = $rest_weigh < 0 ? 0 : $rest_weigh;
                $price = $rule->first_price + $rest_weigh * $rule->rest_price;
                break;
            }
            case 1: { // 按数量计费
                $count = $cart['quantity'];
                $price = $rule->first_price + ($count - 1) * $rule->rest_price;
                break;
            }
        }
        return $price;
    }

    /**
     * 获取商品运费模板、适用规则
     */
    protected function getDeliveryRule($cart, $area_id)
    {
        $delivery = $this->getDeliveryTpl($cart);
        if (empty($delivery)) {
            throw new NotFoundException("商家没有设置运费规则");
        }
        $address = $this->getAddress($area_id);
        if (empty($address)) {
            throw new NotFoundException("您的地址信息错误");
        }
        foreach ($delivery->deliveryRules as $row) { // 查找完全一致的地址Id
            if (in_array($address->id, $row->area_ids)) {
                $rule = $row;
                break;
            }
        }
        if (empty($rule)) {
            if ($address->level == 2) { // 查找上级地址
                foreach ($delivery->deliveryRules as $row) {
                    if (in_array($address->pid, $row->area_ids)) {
                        $rule = $row;
                        break;
                    }
                }
            }
        }
        if (empty($rule)) {
            foreach ($delivery->deliveryRules as $row) {
                if (in_array(0, $row->area_ids)) {
                    $rule = $row;
                    break;
                }
            }
        }
        if (empty($rule)) {
            throw new NotFoundException("该地区暂时缺货");
        }
        return [$rule, $delivery];
    }

    /**
     * 获取运费模板
     */
    protected function getDeliveryTpl($cart)
    {
        if (empty($this->deliveryList)) {
            $deliveryList = DeliveryModel::with(['deliveryRules'])->select();
            $newDeliveryList = [];
            foreach ($deliveryList as $row) {
                $newDeliveryList[$row['id']] = $row;
            }
            $this->deliveryList = $newDeliveryList;
        }
        return $this->deliveryList[$cart['product']['delivery_tpl_id']] ?? $this->getDefaultDeliveryTpl();
    }

    protected function getDefaultDeliveryTpl()
    {
        $this->defaultDeliveryTpl = $this->defaultDeliveryTpl ?? DeliveryModel::with(['deliveryRules'])->order("is_default", "DESC")->order("id")->find();
        return $this->defaultDeliveryTpl;
    }

    /**
     * 获取地址
     */
    protected function getAddress($area_id)
    {
        if (empty($this->address)) {
            $this->address = \addons\xshop\model\AreaModel::where('id', $area_id)->find();
        }
        if ($this->address->level == 3) { // 计算运费只到市一级，区县地址重定向到市一级
            $this->address = \addons\xshop\model\AreaModel::find($this->address->pid);
        }
        return $this->address;

    }

    /** 计算优惠金额 */
    public function clacDiscountPrice()
    {
        \think\Hook::listen('xshop_clac_discount_price', $this);
        return $this;
    }

    /** 计算商品最后价格 */
    public function clacOrderPrice()
    {
        $order_price = number_format($this->products_price + $this->delivery_price - $this->discount_price, 2, '.', '');
        $this->order_price = $order_price > 0 ? $order_price : 0.01;
        return $this;
    }

    /** 计算各项价格 */
    public function clacPrice($products, $area_id)
    {
        $this->product($products)
            ->clacRealProductsPrice()   // 计算商品价格
            ->clacDeliveryPrice($area_id)       // 计算运费
            ->clacDiscountPrice()           // 计算优惠
            ->clacOrderPrice();      // 计算订单价格
        $payload = [
            'skuLogic' => $this
        ];
        \think\Hook::listen('xshop_calc_price_after', $payload);
        return $this;
    }
}
