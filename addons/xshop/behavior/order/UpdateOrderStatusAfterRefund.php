<?php

namespace addons\xshop\behavior\order;

use addons\xshop\model\OrderModel;

/** 退款后更改订单状态 */
class UpdateOrderStatusAfterRefund
{
    public function xshopOrderRefund(&$payload)
    {
        $order = $payload['order'];
        if ($order->order_type == 0) { // 普通订单
            $order->after_sale_status = OrderModel::AFTER_SALE_REFUND;
            $order->save();
        }
    }
}
