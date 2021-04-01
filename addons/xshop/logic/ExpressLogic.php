<?php

namespace addons\xshop\logic;

use \fast\Http;
use \addons\xshop\exception\IgnoreException;
use \addons\xshop\model\OrderModel;

class ExpressLogic
{
    /**
     * 获取订单物流信息
     */
    public static function getInfo($order_sn)
    {
        $config = get_addon_config('xshop');
        extract($config);
        if (empty($express_open)) throw new IgnoreException("接口不可用");
        if (!class_exists($express_api)) throw new IgnoreException("物流接口不存在");
        $order = OrderModel::info(['order_sn' => $order_sn]);
        if (empty($order)) throw new IgnoreException("订单不存在");
        return (new $express_api)->query($order);
    }

}