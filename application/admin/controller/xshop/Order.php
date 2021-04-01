<?php

namespace app\admin\controller\xshop;

use app\common\controller\Backend;

/**
 * 订单管理
 *
 * @icon fa fa-circle-o
 */
class Order extends Base
{
    
    /**
     * Order模型对象
     * @var \app\admin\model\xshop\Order
     */
    protected $model = null;
    protected $searchFields = ['order_sn', 'contactor', 'contactor_phone'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\xshop\Order;
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->with(['user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['user', 'express'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                $row->visible(['id','order_sn','is_pay','pay_time','is_delivery','delivery','status','contactor','contactor_phone','delivery_price','order_price','pay_price','discount_price','payed_price', 'create_time','update_time', 'express_no', 'products_price', 'address', 'pay_type', 'pay_method', 'remark', 'order_type', 'groupon_status', 'after_saler_remark', 'after_buyer_remark', 'after_sale_status', 'refund_fee']);
                $row->visible(['user']);
                $row->getRelation('user')->visible(['username','nickname']);
                $row->visible(['express']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 付款
     */
    public function pay()
    {
        $id = $this->request->post('id');
        try {
            $this->model->pay($id);
            $this->success('', null);
        } catch (\think\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 退款
     */
    public function refund()
    {
        $param = $this->request->post();
        $type = $this->request->get('type');
        $rule = [
            'order_sn' => 'require',
            'price' => 'require|number|>:0'
        ];
        if (($result = $this->validate($param, $rule)) !== true) {
            $this->error($result);
        }
        try {
            $order = \addons\xshop\model\OrderModel::where('order_sn', $param['order_sn'])->find();
            // if ($order->after_sale_status == \addons\xshop\model\OrderModel::AFTER_SALE_REFUND) {
            //     $this->error("订单已退款");
            // }
            $order->refund_fee = $param['price'] + $order->refund_fee;
            $order->after_saler_remark = $param['after_saler_remark'];
            if ($param['close_order'] == 1) {
                $order->status = \addons\xshop\model\OrderModel::ORDER_STATUS_CANCEL;
            }
            if ($type == 1) { // 原路退款
                $result = \addons\xshop\model\PayModel::refund($param['order_sn'], $param['price']);
                if ($result !== false) {
                    $payload = ['order'=> $order];
                    \think\Hook::listen('xshop_order_refund', $payload);
                }
            } elseif ($type == 2) { // 手工退款
                $payload = ['order'=> $order];
                \think\Hook::listen('xshop_order_refund', $payload);
            } else {
                $this->error("选择退款方式");
            }
            if ($order->status == \addons\xshop\model\OrderModel::ORDER_STATUS_CANCEL) {
                $payload = [
                    'order' => $order,
                    'user' => \addons\xshop\model\UserModel::get($order->user_id)
                ];
                \think\Hook::listen('xshop_order_cancel_after', $payload);
            }
            $this->success(null, null, $result);
        } catch (\think\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 发货
     */
    public function ship()
    {
        $param = $this->request->post();
        $result = $this->validate($param, [
            'id' => 'require',
            'express_code|快递公司编号' => 'require',
            'express_no|快递单号' => 'require'
        ]);
        if (true !== $result) {
            return $this->error($result);
        }
        try {
            return $this->success('', null, $this->model->ship($param));
        } catch (\think\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 拒绝退款
     */
    public function reject()
    {
        $param = $this->request->post();
        $rule = [
            'order_sn' => 'require',
            'after_saler_remark|拒绝原因' => 'require'
        ];
        if (true !== ($result = $this->validate($param, $rule))) {
            $this->error($result);
        }
        try {
            $this->success('', null, $this->model->reject($param));
        } catch (\think\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
