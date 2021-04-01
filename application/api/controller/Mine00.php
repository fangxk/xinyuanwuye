<?php


namespace app\api\controller;


use app\common\controller\Api;
use app\admin\controller\LeSoft;
use think\Config;
use think\Validate;

class Mine extends Api
{
    protected $noNeedLogin = '';
    protected $noNeedRight = '*';

    // 获取用户信息
    const HOME_USERS = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/wxClientHouse';

    // 获取我的缴费记录接口
    const PAY_LOG_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/gatheringListOnOwnerAction';

    // 获取报修记录接口
    const REPAIR_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/pageQueryBillList';

    // 获取管家接口
    const HOUSEMAN_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/wechat/queryHouseManagerByHouseIDServlet';

    // 发票历史
    const INVOICE_URL = 'http://117.158.24.187:8001/LsInterfaceServer/electronic/queryinvoicehis';

    // 邀请家人
    const ADDUSER_URL = 'http://117.158.24.187:8002/LsInterfaceServer/phoneServer/acceptLinkman';

    /**
     * 我的成员
     *
     */
    public function homeusers()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
        }
        $member = $this->auth->mc_lecheck(array("phone"=>$mobile));
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $request = json_decode((new LeSoft())->GetSoftUrl(config::get('leapi.Uh'), array("phone"=>$mobile)), true)["Result"];
        if(isset($request)){
            foreach ($request as $v){
               // if($v["client_type"]==0){
                    $d["client_name"] = $v["client_name"];
                    $d["house_name"]  = $v["house_name"];
                    $d["client_type"] = $v["client_type"];
                    $d["pk_project"]  = $v["pk_project"];
                    $data[] = $d;
                //}
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
            $this->error('请先认证');
        }
        $member = $this->auth->mc_lecheck(array("phone"=>$mobile));
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $request = json_decode((new LeSoft())->GetSoftUrl(config::get('leapi.Uh'), array("phone"=>$mobile)), true)["Result"];
        if(isset($request)){
            foreach ($request as $v){
                if($v["client_type"]==0){
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
        $name = $this->request->post('name');
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

        $pk_house = $user->pk_house;
        $pk_client = $user->pk_client;
        $house_name = $user->house_name;
        $project_name = $user->project_name;
        $pk_project = $user->pk_project;

        if (!$pk_house && !$pk_client && !$house_name && !$project_name && !$pk_project) {
            $param["phone"] = $mobile;
            $member = $this->auth->mc_lecheck($param);
            if (!empty($member)) {
                $user->pk_house = $member['pk_house'];
                $user->pk_client = $member['pk_client'];
                $user->house_name = $member['house_name'];
                $user->project_name = $member['project_name'];
                $user->pk_project = $member['pk_project'];
            }
        }

        $user->mobile = $mobile;
        $user->name = $name;
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
            $this->error('请先认证');
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

        $this->success('获取成功', isset($data['Result']['datas']) ? $data['Result']['datas'] : []);
    }


    /**
     * 我的报事报修
     */
    public function my_repair()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
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

        $request = (new LeSoft())->GetSoftUrl(self::REPAIR_URL, $param);
        $data = json_decode($request, true);
        $this->success('获取成功', isset($data['Result']['datas']) ? $data['Result']['datas'] : []);
    }


    /**
     * 我的管家
     */
    public function my_houseman()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
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
            $this->error('请先认证');
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
            $this->error('请先认证');
        }
        $member = $this->auth->mc_lecheck(array("phone"=>$mobile));
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $param["phone"] = $mobile;
        $request = json_decode((new LeSoft())->GetSoftUrl(config::get('leapi.Uh'), array("phone"=>$mobile)), true)["Result"];
       if($request){
           foreach ($request as $v) {

               if(strpos($v["house_name"],'车库') && $v["client_type"]==0){
                   $da['pk_build']     = $v["pk_build"];
                   $da['client_name']  = $v["client_name"];
                   $da['project_name'] = $v["project_name"];
                   $da['house_name']   = $v["house_name"];
                   $data[] = $da;
               }
           }
       }
       $this->success('成功！！',isset($data)?$data:[]);
    }

    /**
     * 邀请家人
     */
    public function  adduser(){
        /*$mobile = $this->auth->mobile;

        if (empty($mobile)) {
            $this->error('请先认证');
        }
        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if(empty($member)){
            $this->error('您未是认证业主无权访问！',"","-2");
        }else{*/
            //测试
            /*$param["phone"] = "13260004111";
            $member = $this->auth->mc_lecheck($param);*/

            $user = $this->auth->getUserinfo();
        //print_r($user);die;
        //}13260004111
        //print_r($user);die;
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
            'updatetime'   =>date("Y-d-m H:i:s",time()),
            'pk_client'    =>$user["pk_client"]
        ];
        print_r($params);die;
        $res = $le->GetSoftUrl(self::ADDUSER_URL, $params);
        print_r($res);die;
        //$data = json_decode($request, true);
        //$this->success('新增成功',"");
    }
}