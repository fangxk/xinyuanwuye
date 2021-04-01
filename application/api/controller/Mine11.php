<?php


namespace app\api\controller;


use app\common\controller\Api;
use app\admin\controller\LeSoft;
use think\Config;
use think\Validate;

class Mine extends Api
{
    protected $noNeedLogin = 'getuserinfo,getuserinfos,certification';
    protected $noNeedRight = '*';

    // 获取用户信息
    const HOME_USERS = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/wxClientHouse';

    // 获取我的缴费记录接口
    const PAY_LOG_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/gatheringListOnOwnerAction';

    // 获取我的缴费记录详情接口
    const PAYLOGINFO_URL = 'http://117.158.24.187:8001/LsInterfaceServer/lsServer/GetPaymentDetails';

    // 获取报修记录接口
    const REPAIR_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/pageQueryBillList';

    // 获取管家接口
    const HOUSEMAN_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/wechat/queryHouseManagerByHouseIDServlet';

    // 发票历史
    const INVOICE_URL = 'http://117.158.24.187:8001/LsInterfaceServer/electronic/queryinvoicehis';

    // 邀请家人
    const ADDUSER_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/acceptLinkman';

    /**
     * 我的成员
     *
     */
    public function homeusers()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('未完善信息！');
        }
        $member = $this->auth->mc_lecheck(array("phone"=>$mobile));
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $request = json_decode((new LeSoft())->GetSoftUrl(config::get('leapi.Uh'), array("phone"=>$mobile)), true)["Result"];
       // print_r($request);die;
        //新增入口携带pk_client
            $pk_client = $this->request->param("pk_client");
        //新增入口携带pk_client
        if(isset($request)){
            foreach ($request as $v){
               /* if($this->auth->pk_project==$v["pk_project"] && $this->auth->pk_house==$v["pk_house"] ){
                    $d["client_name"] = $v["client_name"];
                    $d["house_name"]  = $v["house_name"];
                    $d["client_type"] = $v["client_type"];
                    $d["pk_project"]  = $v["pk_project"];
                    $data[] = $d;
                }*/
                //修改通过pk_client修改
                if($v["pk_client"]==$pk_client){
                    $d["pk_client"]   = $v["pk_client"];
                    $d["client_name"] = $v["client_name"];
                    $d["house_name"]  = $v["house_name"];
                    $d["client_type"] = $v["client_type"];
                    $d["pk_project"]  = $v["pk_project"];
                    $data[] = $d;
                }
                //修改通过pk_client确定成员
            }
        }
        $this->success('成功！',isset($data)?$data:[]);
    }

    /**
     * 我的房屋
     *
     */
    public function myhomem()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('未完善信息！');
        }
        $member = $this->auth->mc_lecheck(array("phone"=>$mobile));
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $request = json_decode((new LeSoft())->GetSoftUrl(config::get('leapi.Uh'), array("phone"=>$mobile)), true)["Result"];
        if(isset($request)){
            foreach ($request as $v){
                if($v["client_type"]==0){
                    $d["pk_client"]   = $v["pk_client"];
                    $d["client_name"] = $v["client_name"];
                    $d["house_name"]  = $v["house_name"];
                    $d["client_type"] = $v["client_type"];
                    $d["pk_project"]  = $v["pk_project"];
                    $data[] = $d;
                }
            }
        }
        $this->success('成功！',isset($data)?$data:[]);
    }

    /**
     * 用户认证
     *
     * @param string name 真实姓名
     * @param string mobile 手机号码
     */
    public function certification()
    {
        $name   = $this->request->post('name');
        $mobile = $this->request->post('mobile');

        if (!$mobile || !$name) {
            $this->error(__('Invalid parameters'));
        }

        $user = $this->auth->getUser();

        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        /*if ((new \app\common\model\User)->where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }*/
       /* $pk_house  = $user->pk_house;
        $pk_client = $user->pk_client;
        $house_name= $user->house_name;
        $project_name= $user->project_name;
        $pk_project  = $user->pk_project;*/
       // if (!$pk_house && !$pk_client && !$house_name && !$project_name && !$pk_project) {
            $param["phone"] = $mobile;
            $member = $this->auth->mc_lecheck($param);
            if (!empty($member)) {
                $user->pk_house  = $member['pk_house'];
                $user->pk_client = $member['pk_client'];
                $user->house_name= $member['house_name'];
                $user->project_name = $member['project_name'];
                $user->pk_project   = $member['pk_project'];
            }
        //}
        $user->mobile = $mobile;
        $user->name   = $name;
        $user->save();
        $this->success('认证成功');
    }

    /**
     * 我的缴费记录
     */
    public function pay_log()
    {
        $page = $this->request->param('page');

        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('未完善信息！');
        }

        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }

        $param = [
            'pkHouseClients' => $this->auth->pk_house . ',' . $this->auth->pk_client,
            'pageOprator' => 'bgoto',
            'gotopage' => $page,
            'pageSize' => 10
        ];

        $request = (new LeSoft())->GetSoftUrl(self::PAY_LOG_URL, $param);
        $data = json_decode($request, true);
        //print_r($data);die;
        $type = $this->request->param("type",1);
        //缴费状态1所有 2已开  3已开
        $res  = isset($data['Result']['datas']) ? $data['Result']['datas'] : [];
        $result = array();
        if($res){
            foreach ($res as $ke=>$v){
                if($type==1){//全部
                    $result[] = $v;
                }
                if($type==2){//已开
                    if($v["invoiceStatus"] == "已开票"){
                        $result[] = $v;
                    }
                }
                if($type==3){//已开
                    if($v["invoiceStatus"] == "未开票"){
                        $result[] = $v;
                    }
                }
            }
        }
        $this->success('获取成功', !empty($result)?$result:[]);
    }

    /**
     * 缴费记录详情
     *
     */
    public function paylog_info(){

        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('未完善信息！');
        }
        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $pkGathering = $this->request->param('pkGathering');
        $request = json_decode((new LeSoft())->GetSoftUrl(self::PAYLOGINFO_URL, array("pkGathering"=>$pkGathering)),true);

        if($request["StateCode"]){
            $this->error('数据请求失败！',$request["ErrorMsg"]);
        }else{
            $this->error('成功！',isset($request["Result"])?$request["Result"]:[]);
        }
    }

    /**
     * 我的报事报修
     */
    public function my_repair()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('未完善信息！');
        }
        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $page = $this->request->param('page', 1);
        $pageSize = 10;
        if (!$page) {
            $this->error('无效参数page');
        }
        $param = [
            'gotopage' => $page,
            'pageSize' => $pageSize,
            'billType' => '05',
            'pageOprator' => 'bgoto',
            'pkHouseClients' => $this->auth->pk_house . ',' . $this->auth->pk_client
        ];
        $type = $this->request->param("type",1);
        //报修状态1所有 2待处理  3处理中  4已处理

        $request = (new LeSoft())->GetSoftUrl(self::REPAIR_URL, $param);
        $data = json_decode($request, true);
        $res  = isset($data['Result']['datas']) ? $data['Result']['datas'] : [];
        $result = array();
        if($res){
            foreach ($res as $ke=>$v){
                if($type==1){//全部
                    $result[] = $v;
                }
                if($type==2){//待处理
                    if($v["billState"] == "1.未分配"){
                        $result[] = $v;
                    }
                }
                if($type==3){//处理中
                    if($v["billState"] == "2.已分配" || $v["billState"] == "3.进行中" ){
                        $result[] = $v;
                    }
                }
                if($type==4){//已处理
                    if($v["billState"] == "4.已完成" || $v["billState"] == "5.已回访" ){
                        $result[] = $v;
                    }
                }

            }
        }
        $this->success('获取成功', !empty($result)?$result:[]);
    }

    /**
     * 我的管家
     */
    public function my_houseman()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('未完善信息！');
        }

        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $param = [
            'pk_house' => $this->auth->pk_house
        ];
        $request = (new LeSoft())->GetSoftUrl(self::HOUSEMAN_URL, $param);
        $data = json_decode($request, true);
        $this->success('获取成功', isset($data['Result']) ? $data['Result'] : []);
    }

    /**
     * 发票历史
     */
    public function invoice()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('未完善信息！');
        }

        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }

        $page = $this->request->param('page', 1);
        $pageSize = 10;

        if (!$page) {
            $this->error('无效参数page');
        }

        $param = [
            'pageOprator' => 'bgoto',
            'pkClient' => $this->auth->pk_client,
            'gotopage' => $page,
            'pageSize' => $pageSize,
        ];

        $request = (new LeSoft())->GetSoftUrl(self::INVOICE_URL, $param);
        $data = json_decode($request, true);
        $this->success('获取成功', isset($data['Result']['datas']) ? $data['Result']['datas'] : []);
    }

    /**
     * 用户是否可以认证
     */
    public function  getuserinfo(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('手机号为空完善信息！',"","-1");
        }
        $param["phone"] = $mobile;
        $authos = $this->auth->mc_lecheck($param);
        if(empty($authos)){
            $this->error('您未是认证业主无权访问！',"","-2");
        }else{
            $this->success('允许进入');
        }
    }

    /**
     * 用户信息
     */
    public function  getuserinfos(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('手机号为空完善信息！',"","-1");
        }
        $param["phone"] = $mobile;
        $authos = $this->auth->mc_lecheck($param);
        if(empty($authos)){
            $this->error('您未是认证业主无权访问！',"","-2");
        }else{
            $user = $this->auth->getUserinfo();
        }
        $this->success("数据成功",isset($user)?$user:[]);
    }

    /**
     * 我的车位
     */
    public function getcars(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('未完善信息！');
        }
        $member = $this->auth->mc_lecheck(array("phone"=>$mobile));
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $param["phone"] = $mobile;
        $request = json_decode((new LeSoft())->GetSoftUrl(config::get('leapi.Uh'), array("phone"=>$mobile)), true)["Result"];
       if($request){
           foreach ($request as $v) {
               if(strpos($v["house_name"],'车库')){
                   /*$da['pk_build']     = $v["pk_build"];
                   $da['client_name']  = $v["client_name"];
                   $da['project_name'] = $v["project_name"];
                   $da['house_name']   = $v["house_name"];*/
                   $data[] = $v;
               }
           }
       }
       $this->success('成功！！',isset($data)?$data:[]);
    }

    /**
     * 邀请家人
     */
    public function  adduser(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('未完善信息！');
        }
        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if(empty($member)){
            $this->error('您未是认证业主无权访问！',"","-2");
        }else{
            $user = $this->auth->getUserinfo();
        }

        $le = new LeSoft();
        $getparam = $this->request->param();
        $params = [
            'staffname'    =>$getparam["name"],
            'sex'          =>$getparam["sex"],
            'mobile'       =>$getparam["mobile"],
            'relation'     =>$getparam["relation"],
            'operationType'=> 0,
            'nonce_str'    =>$le->SjStr("15"),
            'staffcode'    =>$le->SjStr("10"),
            'pk_staff'     =>$le->SjStr("20"),
            'createtime'   =>date("Y-d-m H:i:s",time()),
            'updatetime'   =>date("Y-d-m H:i:s",time())
            /*'pk_client'    =>$user["pk_client"]*/
        ];
        //修改通过页面传递pk_client
        if($this->request->param("pk_client")){//传递的有
            $params["pk_client"] = $this->request->param("pk_client");
        }else{
            $params["pk_client"] = $user["pk_client"];
        }

        //修改通过页面传递pk_client
        $res = $le->GetSoftUrl(self::ADDUSER_URL, $params);

        $result = json_decode($res, true);
        if ($result['StateCode'] == 0) {
            $this->success('新增成功');
        } else {
            $this->error(isset($result['ErrorMsg']) ? $result['ErrorMsg'] : '新增失败！！');
        }
    }
}