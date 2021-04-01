<?php


namespace app\api\controller;


use app\admin\controller\LeSoft;
use app\common\controller\Api;

class Pay extends Api
{
    protected $noNeedLogin = '';

    protected $noNeedRight = '*';

    // 获取缴费列表接口
    const PAY_LIST_URL     = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/wxNoPayFeesAndPreferential';
    // 缴费接口
    /*const PAY_REQUEST_URL  = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/submitFeeBill';*/
    const PAY_REQUEST_URL  = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/payment/advancePaymentAction';
    //获取缴费积分
    const PAY_INTEGRAL_URL = "http://117.158.24.187:8001/LsInterfaceServer/phoneServer/comfirmRewardpoints";
    //获取缴费优惠金额
    const PAY_DISCOUNT_URL = "http://117.158.24.187:8001/LsInterfaceServer/phoneServer/payment/confirmAmount";

    /**
     * 缴费列表
     */
    public function pay_list()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先完善信息！');
        }
        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if (empty($member)) {
            $this->error('您未是认证业主无权访问！');
        }

        /*$param = [
            'pk_client' => $this->auth->pk_client
        ];*/
        //新增筛选相应的房屋默认切换小区的房屋
        if($this->request->param("pk_house") && $this->request->param("pk_client")){
            $param["pk_house"]  = $this->request->param("pk_house");
            $param["pk_client"] = $this->request->param("pk_client");
        }else{
            $param["pk_house"] = $this->auth->pk_house;
            $param["pk_client"] = $this->auth->pk_client;
        }

        //新增筛选相应的房屋缴费数据列表
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
                $tmpArray[$key]['prices'][]      = $row['price'];

                $tmpArray[$key]['startdate'][]   = isset($row['cost_startdate'])?$row['cost_startdate']:$row['billdate'];
                $tmpArray[$key]['enddate'][]     = isset($row['cost_enddate'])?$row['cost_enddate']:$row['billdate'];
                //是否有缴费开始日期和结束日期
                $tmpArray[$key]["cost_startdate"] = isset($row['cost_startdate'])?$row['cost_startdate']:$row['billdate'];
                $tmpArray[$key]["cost_enddate"]   = isset($row['cost_enddate'])?$row['cost_enddate']:$row['billdate'];

            }
            foreach ($tmpArray as $k=>$v) {
                $v["time_section"] = $v["cost_startdate"]."至". $v["cost_enddate"];
                if(is_array($v["prices"])){
                    $v["price"] = "".array_sum($v["prices"])."";
                }
                if(is_array($v["startdate"]) && is_array($v["enddate"])){
                    sort($v["startdate"]);
                    sort($v["enddate"]);
                    $v["cost_startdate"] = reset($v["startdate"]);
                    $v["cost_enddate"]   = end($v["enddate"]);
                    $v["time_section"]   = reset($v["startdate"])."至". end($v["enddate"]);
                }
                unset($v["prices"]);
                unset($v["startdate"]);
                unset($v["enddate"]);
                $resdata[] = $v;
            }
        }
        //print_r($resdata);die;

        ini_set("serialize_precision","16");
        //echo json_decode($a);die;
        //return json_encode($a);
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
            $this->error('请先完善信息');
        }

        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if (empty($member)) {
            $this->error('您未是认证业主无权访问！');
        }
       /* $param = [
            'feeids'        =>$feedid121212000,
            'pk_client'     =>"00ACA332E500578FB0E7",
            'type'          =>22,
            'pk_house'      =>"003829AC4900D8EDDF34",
            "paybillcode"   =>\addons\xshop\library\Sn::get("O")
        ];*/
       $param = [
            'feeids'        => $feedid,
            'userId'        => $this->auth->pk_client,
            'payableAmount' => $price,
            'phone'         => $mobile
        ];
        $request = (new LeSoft())->GetSoftUrl(self::PAY_REQUEST_URL, $param);
        $data = json_decode($request, true);//备注
        if(!empty($data["StateCode"])){
            $this->error($data["ErrorMsg"]);
        }
        $pk_bill = $data['Result']['pk_bill'];
        //print_r($pk_bill);die;
        $url     = "https://yaoyao.cebbank.com/LifePayment/wap/short/index.html?urlType=3&canal=xykjwyf&ItemNo=361087474&userNo=$pk_bill&filed1=xywy";
        $this->success('缴费成功',  ['pay_url' => $url]);
    }

    /**
     * 获取缴费积分
     */
    public function  integral(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先完善信息！');
        }
        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if (empty($member)) {
            $this->error('您未是认证业主无权访问！');
        }
        $fieeds = $this->request->param("feeids");
        if($fieeds){
            $fieed   = explode('|',$fieeds);
            $fieedss = implode(',',$fieed);
        }else{
            $this->error('参数请求错误！');
        }
        //积分
        /*$a1 = explode("|","FU0FL8IZ3RFWFT2EW28I|NAP4VBF22AOOCFVQQJRP|PMQ86TXX0XR725YB6FIO|I7NF7BN5DT4MOYRT3N2W|9695FNZJVDE8RF5WXDEJ|N2KOWI06S17FMRTXVNY2|P1E499JMC5DRPNL5PAB8|P7ZH586M24ZMESOWC3B8|ODXIOSLC256KUXKVAKRB|EI9RY1N1QWEEO9QE0KT8|EC7X2JHPCG8QMKZMFLKF|O0QQFXYUID3OXY9G2QGB");
        $a2 = explode("|","IDLZW2Q7WOOC7UC9ZYK9|IB9MWJVSUCTHHYN52YG4|8MJYPATYB8RF5H0FWGSR|EJPDNBH9TTE8K488L3L8|SN70TTXEFFWHI6IWX18I|376WD0NRKMNP4ZR8DMR3|QERB0W4I5GRPBS45NVRE|FL3KFOPWSAZNFZU34BEI|HEGVESVSVIMT7VN52G7S|636KGLTF4JO4979UDZCA|7BP05MTHF2WAFK3X0ABI|CKX4QWOLV0G9SXTBQZ2V");
        $a3 = implode(",",$a1).','.implode(",",$a2);*/
        //金额
        /*$a1 = explode('|',"3JAHQT8KFVMBP6SKRCWD|P13SGFQBPQKRRJU8A9JN|9KEIETB3D5YG3R0T4G83|KVV4PSPD1JGK1WZ6L5IW|GLOVHJ2PFUU2Z9CQ04T3|AKDO0WHRONCU5ES5DD9I");
        $a4 = explode("|","A04DDOSSLH5NCH3QVOVL|4TQUVV8UO5MZDVFWP0WO|K1EK116SQH0AF4U6WD82|TFRZDKK2HZFGQA56VWMM|D9549H599OZJFOOXMZNJ|1L8HR6GA1HS9ME62WX9M");
        $a2 = explode('|',"YYPMGVB4KDJAY2XVBOT0|X882D3T98CMP5766C98A|HMA6IYX7XNEEJYCC2JVP|8XUQLPGBB56SB37F65AB|Q8NSAY28R1VL6212CQIT|4309RMUQ3MOTSJHJWWYC|RBWI87VNUP9B9AKIUHJE|LQ1JKD42Z0E32FCDAVWL|C32DX8BN01YHM0VBP1K7|79QW5MOJ6H47LLRIZD6L|E3KM0AFRGXLI5MIHPQX3|PFUMNQ0ME1X445U8K5OL");
        $a3 = implode(",",$a1).','.implode(",",$a2).','.implode(",",$a4);
        $a5 = "3JAHQT8KFVMBP6SKRCWD|P13SGFQBPQKRRJU8A9JN|9KEIETB3D5YG3R0T4G83|KVV4PSPD1JGK1WZ6L5IW|GLOVHJ2PFUU2Z9CQ04T3|AKDO0WHRONCU5ES5DD9I";*/
        //获取积分
        $param = array("userid"=>(new LeSoft())->SjStr(8), "feeids"=>$fieedss);
        $request = json_decode((new LeSoft())->GetSoftUrl(self::PAY_INTEGRAL_URL, $param),true);
        if(isset($request["Result"]) && $request["Result"]["ishavepoints"]) {
            if ($request["StateCode"]) {
                $this->error('获取失败！', $request["ErrorMsg"]);
            } else {
                $request["Result"]["type"] = 0;
                $this->success('获取成功', $request["Result"]);
            }
        }

        //优惠金额
        $params   = array("feeids"=>$fieeds);
        $requests = json_decode((new LeSoft())->GetSoftUrl(self::PAY_DISCOUNT_URL, $params),true);
        if(isset($requests["Result"]) &&  $requests["Result"]["adjust_amount"]){
            if($requests["StateCode"]){
                $this->error('获取失败！',$requests["ErrorMsg"]);
            }else{
                $requests["Result"]["type"] = 1;
                $this->success('获取成功',$requests["Result"]);
            }
        }
        //print_r($request);die;
        $this->success('暂无数据！',"");

    }

    /**
     * 获取缴费中优惠金额
     */
   /* public function  discount(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先完善信息！');
        }
        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if (empty($member)) {
            $this->error('您未是认证业主无权访问！');
        }
        $fieeds = $this->request->param("feeids");
        $param   = array("feeids"=>$fieeds);
        $request = json_decode((new LeSoft())->GetSoftUrl(self::PAY_DISCOUNT_URL, $param),true);
        if($request["StateCode"]){
            $this->error('获取失败！',$request["ErrorMsg"]);
        }else{
            $request["Result"]["type"] = 1;
            $this->success('获取成功',$request["Result"]);
        }
    }*/

}