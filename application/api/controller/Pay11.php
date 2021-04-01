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

        $temArr = $newdata ;
        $newArr = array();
        foreach($newdata as $num => $arr ){
            unset($temArr[$num]);
            $feeid = $arr['feeid'];
            foreach($temArr as $tNum =>$tArr ){
                $n_arr     = array();
                $tem_moeny = array();
                if($arr['feename'] == $tArr['feename']){
                    $feeid .= '+'.$tArr['feeid'];
                    $n_arr['feeid'] = $feeid;
                    $n_arr['feename'] = $arr['feename'];
                    $n_arr['billdate']     = $arr['billdate'];
                    if(is_array($arr['price'])){
                        array_push($n_arr['price'], $tArr['price']);
                    }else{
                        $tem_moeny [] = $arr['price'];
                        $tem_moeny [] = $tArr['price'];
                    }
                    $tem_moeny[] = $tArr['price'];
                    $n_arr['price'] = $tem_moeny;
                    $newArr[] = $n_arr ;
                }
            }
            if($feeid == $arr['feeid']){
                $newArr[] = $arr ;
            }
        }
        print_r($newArr);die;
        /*$time = time();
        $len = count($newdata);
        for ($i = 0; $i < $len -1; $i++) {//循环对比的轮数
            for ($j = 0; $j < $len - $i - 1; $j++) {//当前轮相邻元素循环对比
                if (strtotime($newdata[$j]["cost_enddate"]) > strtotime($newdata[$j + 1]["cost_enddate"])) {//如果前边的大于后边的
                    $tmp             = $newdata[$j];//交换数据
                    $newdata[$j]     = $newdata[$j + 1];
                    $newdata[$j + 1] = $tmp;
                }
            }
        }*/

        $l1 = $newdata;
        foreach ($newdata as $k=>$v){
            unset($newdata[$k]);
            $feeid = $v["feeid"];
            $price = $v["price"];
            foreach ($l1 as $k1=>$v1) {
                if ($v['feename'] == $v1["feename"] && $v['billdate'] == $v1["billdate"] && $v['cost_enddate'] == $v1["cost_enddate"]) {
                    $price += $v1["price"];
                    $newdata[$k]['price'] = $v1["price"];
                    //$newdata[$k]['price']   =  $v1["price"];
                    $newdata[$k]['feename'] = $v1["feename"];
                    //unset($l1[$k1]);
                }
            }
            //$newda[] = $v;
        }
        print_r($newdata);die;



        /*foreach ($return_Arr["0"] as $k=>$v){
            foreach ($v as $k1=>$v1){
                $da["feename"]  = $k;
                $da["billdate"] = $k1;
                $da["price"]    = $v1;
                $das[] = $da;
            }
        }*/

        /*$timer_arr=[];
          $arr=[];
          $return_Arr=[];*/
       /* foreach ($n as $key=>$value){
            $tempPirce=0;
            $temp_arr=[];

            foreach($value  as $k=>$v){
                if (!in_array($v['billdate'],$timer_arr)) {
                    array_push($timer_arr, $v['billdate']);
                    $tempPirce = $v['price'];
                    $arr[$key][$v['billdate']]= $tempPirce;
                } else {
                    $tempPirce += $v['price'];
                    $arr[$key][$v['billdate']]= $tempPirce;
                }
                $arr[$key]['feename']= $key;
            }
            $return_Arr[]=$arr;

        }*/
        /*print_r($newdata);die;
        echo  '<hr/>';*/
         // print_r($arr);echo  '<hr/>';
        //print_r($n);
        //die;
        //修改
        /*foreach ($newdata as $v){
            $a[$v["feename"]][]= $v;
        }
        print_r($newdata);die;*/
        /*foreach ($a as $k=>$v){

        }*/
        //print_r($newdata);die;

        /*if($newdata){
            $len = count($newdata);
            for ($i = 0; $i < $len -1; $i++) {//循环对比的轮数
                for ($j = 0; $j < $len - $i - 1; $j++) {//当前轮相邻元素循环对比
                    if (strtotime($newdata[$j]["cost_enddate"]) > strtotime($newdata[$j + 1]["cost_enddate"])) {//如果前边的大于后边的
                        $tmp             = $newdata[$j];//交换数据
                        $newdata[$j]     = $newdata[$j + 1];
                        $newdata[$j + 1] = $tmp;
                    }
                }
            }
            foreach ($newdata as $v){
                if(strtotime($v["cost_enddate"])>time()){
                    $showtime = "预缴";
                    $v["showtime"]     = $showtime;
                    $v["time_section"] = $v["cost_startdate"]."至".$v["cost_enddate"];
                    $resdata["pre_pay"][]=$v;
                }else{
                    $showtime = "欠费";
                    $v["time_section"] = $v["cost_startdate"]."至".$v["cost_enddate"];
                    $v["showtime"]     = $showtime;
                    $resdata["owe_fee"][] =$v;
                }
            }
        }*/
        //修改
        $this->success('获取成功', isset($resdata)?$resdata:[]);
        //$this->success('获取成功', isset($data['Result']['fees'])?$data['Result']['fees']:[]);
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