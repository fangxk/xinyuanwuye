<?php


namespace app\api\controller;


use app\admin\controller\LeSoft;
use app\common\controller\Api;

class Pay extends Api
{
    protected $noNeedLogin = '';

    protected $noNeedRight = '*';

    // 获取缴费列表接口
    const PAY_LIST_URL    = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/wxNoPayFeesAndPreferential';

    // 缴费接口
    const PAY_REQUEST_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/payment/advancePaymentAction';

    /**
     * 缴费列表
     */
    public function pay_list()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
        }
        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if (empty($member)) {
            $this->error('您未是认证业主无权访问！');
        }
        $param = [
            'pk_house'  => $this->auth->pk_house,
            'pk_client' => $this->auth->pk_client
        ];
        $request = (new LeSoft())->GetSoftUrl(self::PAY_LIST_URL, $param);
        $data = json_decode($request, true);
        $newdata = isset($data['Result']['fees'])?$data['Result']['fees']:[];
        if($newdata){
            $tmpArray = array();
            foreach ($newdata as $row) {
                $billdate = isset($row['billdate'])?$row['billdate']:'';
                if(isset($row["receipt_enddate"])){
                    $key = $row['feename'] .$billdate. $row['receipt_enddate'];
                }else{
                    $key = $row['feename'] .$billdate;
                }
                if (array_key_exists($key, $tmpArray)) {
                    $tmpArray[$key]['feeid']   = $tmpArray[$key]['feeid'] . '|' . $row['feeid'];
                } else {
                    $tmpArray[$key] = $row;
                }
                $tmpArray[$key]['prices'][]      = $row['price'];//'12.11';
                $tmpArray[$key]['startdate'][]   = $row['cost_startdate'];
                $tmpArray[$key]['enddate'][]     = $row['cost_enddate'];
            }
            foreach ($tmpArray as $k=>$v) {
                $v["time_section"] = $v["cost_startdate"]."至". $v["cost_enddate"];
                if(is_array($v["prices"])){
                    $v["price"] = array_sum($v["prices"]);
                }
                if(is_array($v["startdate"]) && is_array($v["enddate"])){
                    $v["time_section"] = reset($v["startdate"])."至". end($v["enddate"]);
                }
                unset($v["prices"]);
                unset($v["startdate"]);
                unset($v["enddate"]);
                $resdata[] = $v;
            }
        }
        ini_set("serialize_precision","16");
        $this->success('获取成功', isset($resdata)?$resdata:[]);
    }

    /**
     * 缴费
     */
    public function payment()
    {
        $feedid = $this->request->post('feedid');
        $price  = $this->request->post('price');

        if (!$feedid || !$price) {
            $this->error('无效参数');
        }

        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
        }

        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if (empty($member)) {
            $this->error('您未是认证业主无权访问！');
        }

        $param = [
            'feeids'        => $feedid,
            'userId'        => $this->auth->pk_client,
            'payableAmount' => $price,
            'phone'         => $mobile
        ];

        $request = (new LeSoft())->GetSoftUrl(self::PAY_REQUEST_URL, $param);
        $data = json_decode($request, true);

        $pk_bill = $data['Result']['pk_bill'];
        $url = "https://yaoyao.cebbank.com/LifePayment/wap/short/index.html?urlType=3&canal=xykjwyf&ItemNo=361087474&userNo=$pk_bill&filed1=xywy";

        $this->success('获取成功',  ['pay_url' => $url]);
    }

}