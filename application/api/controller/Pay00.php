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
        $time = time();
        //修改
        if($newdata){
            $len = count($newdata);
            for ($i = 0; $i < $len -1; $i++) {//循环对比的轮数
                if(strtotime($v["cost_enddate"])>time()){
                    $showtime = "预缴";
                }else{
                    $showtime = "欠费";
                }
                $v["time_section"] = $v["cost_startdate"]."至".$v["cost_enddate"];
                $v["showtime"]     = $showtime;
                for ($j = 0; $j < $len - $i - 1; $j++) {//当前轮相邻元素循环对比
                    if (strtotime($newdata[$j]["cost_enddate"]) > strtotime($newdata[$j + 1]["cost_enddate"])) {//如果前边的大于后边的
                        $tmp             = $newdata[$j];//交换数据
                        $newdata[$j]     = $newdata[$j + 1];
                        $newdata[$j + 1] = $tmp;
                    }
                }
            }
            print_r($newdata);die;
            /*foreach ($newdata as $v){
                if(strtotime($v["cost_enddate"])>time()){
                    $showtime = "预缴";
                }else{
                    $showtime = "欠费";
                }
                $v["time_section"] = $v["cost_startdate"]."至".$v["cost_enddate"];
                $v["showtime"]     = $showtime;
                $newda[] = $v;
            }*/
        }

        //修改
        $this->success('获取成功', isset($newda)?$newda:[]);
        /*$this->success('获取成功', isset($data['Result']['fees'])?$data['Result']['fees']:[]);*/
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