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
    const HOUSEMAN_URL= 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/wechat/queryHouseManagerByHouseIDServlet';

    // 发票历史
    const INVOICE_URL = 'http://117.158.24.187:8001/LsInterfaceServer/electronic/queryinvoicehis';

    // 邀请家人
    const ADDUSER_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/acceptLinkman';

    //房屋信息
    const HOUSE_URL   = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/wxClientHouse';

    //我的成员
    const MY_MEMBER   = "http://117.158.24.187:8001/LsInterfaceServer/phoneServer/queryGuestsAction";

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
        $pk_client = $this->request->param("pk_client");
        $data = json_decode((new LeSoft())->GetSoftUrl(self::MY_MEMBER, array("pk_client"=>$pk_client)), true)["Result"];
        //print_r($data);die;
        if(isset($data)){
            foreach ($data as $v){
                //修改通过pk_client修改
                $d["client_name"]= $v["name"];
                $d["staffname"]  = $v["name"];
                $d["pk_staff"]   = $v["pk_staff"];
                $d["staffcode"]  = $v["code"];

                if(strstr($v["relation"],'租户')){
                    $d["relation"] = '2';
                }
                if(strstr($v["relation"],'家人')){
                    $d["relation"] = '4';
                }
                if(strstr($v["relation"],'朋友')){
                    $d["relation"] = '5';
                }
                if(isset($v["sex"])){
                    if(strstr($v["sex"],'男')){
                        $d["sex"] = '1';
                    }
                    if(strstr($v["sex"],'女')){
                        $d["sex"] = '2';
                    }
                }else{
                    $d["sex"] = '1';
                }
                $d["mobile"]  = $v["mobile"];
                /*if(strstr($v["relation"],'租户')){
                    $d["client_type"] = '1';
                }
                if(strstr($v["relation"],'家人')){
                    $d["client_type"] = '10';
                }
                if(strstr($v["relation"],'朋友')){
                    $d["client_type"] = '10';
                }*/
                $datas[] = $d;
            }
        }
        $this->success('成功！',isset($datas)?$datas:[]);
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

                if($v["client_type"]==0 || $v["client_type"]==1){
                    $d["pk_client"]   = $v["pk_client"];
                    $d["client_name"] = $v["client_name"];
                    $d["house_name"]  = $v["house_name"];
                    $d["client_type"] = $v["client_type"];
                    $d["pk_project"]  = $v["pk_project"];
                    $data[] = $d;
                }
               /*权限 if($v["client_type"]==0){
                    $d["pk_client"]   = $v["pk_client"];
                    $d["client_name"] = $v["client_name"];
                    $d["house_name"]  = $v["house_name"];
                    $d["client_type"] = $v["client_type"];
                    $d["pk_project"]  = $v["pk_project"];
                    $data[] = $d;
                }*/
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

        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if (!empty($member)) {
            $user->pk_house  = $member['pk_house'];
            $user->pk_client = $member['pk_client'];
            $user->house_name= $member['house_name'];
            $user->project_name = $member['project_name'];
            $user->pk_project   = $member['pk_project'];
        }
        $user->mobile = $mobile;
        $user->name   = $name;
        $user->save();
        $this->success('提交成功');
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
        $resp = (new LeSoft())->GetSoftUrl(self::HOUSE_URL, $param);
        $result = json_decode($resp, true);
        if (isset($result['Result'])) {
            $results = array();
            foreach ($result['Result'] as $v) {
                //权限 if ($v["client_type"] == 0) {
                    $param = [
                        'pkHouseClients' => $v["pk_house"] . ',' . $v["pk_client"],
                        'pageOprator' => 'bgoto',
                        'gotopage' => $page,
                        'pageSize' => 10
                    ];
                    $request = (new LeSoft())->GetSoftUrl(self::PAY_LOG_URL, $param);
                    $paylogs = json_decode($request, true)["Result"];
                    if(isset($paylogs["datas"])){
                        $type = $this->request->param("type",1);
                        foreach ($paylogs["datas"] as $vs) {
                            if($type==1){//全部
                                $results[] = $vs;
                            }
                            if($type==3){//未开
                                if($vs["invoiceStatus"] == "未开票"){
                                    $results[] = $vs;
                                }
                            }
                        }
                    }
                //}
            }
        }
        $this->success('获取成功', $results);
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
            $this->success('成功！',isset($request["Result"])?$request["Result"]:[]);
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

        //查询所有房屋
        $request = json_decode((new LeSoft())->GetSoftUrl(config::get('leapi.Uh'), array("phone"=>$mobile)), true)["Result"];
        if(isset($request)){
            foreach ($request as $v){
                if($v["client_type"]==0 || $v["client_type"]==1){
                    $d["pk_client"]   = $v["pk_client"];
                    $d["pk_house"]    = $v["pk_house"];
                    $houses[] = $d;
                }
                /*权限 if($v["client_type"]==0){
                    $d["pk_client"]   = $v["pk_client"];
                    $d["pk_house"]    = $v["pk_house"];
                    $houses[] = $d;
                }*/
            }
        }
        if(!empty($houses)){
            foreach ($houses as $v){
                $newhouse[] = $v["pk_house"].','.$v["pk_client"];
            }
        }else{
            $this->error('暂无房屋信息');
        }
        $param = [
            'gotopage' => $page,
            'pageSize' => $pageSize,
            'billType' => '05',
            'pageOprator' => 'bgoto',
            'pkHouseClients' => implode('|',$newhouse)
            /*'pkHouseClients' => $this->auth->pk_house . ',' . $this->auth->pk_client*/
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
                if($type==2){//待处理1.未分配
                    if(strstr($v["billState"],'1')){
                        $result[] = $v;
                    }
                }
                if($type==3){//处理中2.已分配 3.进行中
                    if(strstr($v["billState"],'2') || strstr($v["billState"],'3') ){
                        $result[] = $v;
                    }
                }
                if($type==4){//已处理 4已处理 5.已回访
                    if(strstr($v["billState"],'4') || strstr($v["billState"],'5')){
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
     * 管家评价展示
     *
     */
    public function my_housemessage(){
        //管家评价展示信息
        //满意度
        $satisfied = array(array("name"=>"满意","image"=>cdnurl("/public/assets/img/manyi.png",true),"activeimage"=>cdnurl("/public/assets/img/manyi1.png",true)),array("name"=>"一般","image"=>cdnurl("/public/assets/img/yiban.png",true),"activeimage"=>cdnurl("/public/assets/img/yiban1.png",true)),array("name"=>"不满意","image"=>cdnurl("/public/assets/img/bumanyi.png",true),"activeimage"=>cdnurl("/public/assets/img/bumanyi1.png",true)));
            $manyis = [];
            if(config("site.manyiyy")){
                $a = explode(',',config("site.manyiyy"));
                foreach($a as $k=>$v) {
                   $manyis["id"] = $k;
                   $manyis["name"] = $v;
                   $manyis["checked"] = false;
                   $manyi[] = $manyis;
               }
            }
        $yiban = [];
        if(config("site.yibanyy")){
            $a = explode(',',config("site.yibanyy"));
            foreach($a as $k=>$v) {
                $yibans["id"] = $k;
                $yibans["name"] = $v;
                $yibans["checked"] = false;
                $yiban[] = $yibans;
            }
        }
        $bumanyi = [];
        if(config("site.bumanyiyy")){
            $a = explode(',',config("site.bumanyiyy"));
            foreach($a as $k=>$v) {
                $bumanyis["id"] = $k;
                $bumanyis["name"] = $v;
                $bumanyis["checked"] = false;
                $bumanyi[] = $bumanyis;
            }
        }
        $reson   = array("manyi"=>$manyi,"yiban"=>$yiban,"bumanyi"=>$bumanyi);
        $placeholder = array("placeholdermayi"=>"请说出您对管家满意的地方，对管家进行鼓励","placeholderyiban"=>"说说哪里不好，帮助管家改进.....","placeholderbumanyi"=>"说说哪里不满意，帮助管家改进.....");
        $this->success('返回成功', ['satisfied' => $satisfied,"reson"=>$reson,"placeholder"=>$placeholder]);
    }

    /**
     * 提交管家评价
     */
    public function my_houseaddmessage(){
        $userinfo = $this->auth->getUserinfo();
        $param = $this->request->param();
        if(empty($param["satisfied"])){
            $this->error('满意度必填！');
        }
        if(empty($param["reson"])){
            $this->error('请选择原因！');
        }
        if(empty($param["evaluatetext"])){
            $this->error('请填写评价内容！');
        }
        $res = Db("housemessage")->field("id,createtime")->where("pk_house='{$userinfo['pk_house']}' AND user_mobile='{$userinfo['mobile']}'")->order("id desc")->find();

        if($res["createtime"]){//判断是否可以评价
           if(time()<$res["createtime"]+config("site.days")*24*3600){
               $this->error("近期已评价请".config("site.days")."天后评价！");
           }
        }
        $data = array(
            "house_name"  =>$userinfo["house_name"],
            "project_name"=>$userinfo["project_name"],
            "pk_house"    =>$userinfo["pk_house"],
            "pk_client"   =>$userinfo["pk_client"],
            "user_name"   =>$userinfo["name"],
            "user_mobile" =>$userinfo["mobile"],
            "satisfied"   =>$param["satisfied"],
            "reson"       =>$param["reson"],
            "evaluatetext"=>$param["evaluatetext"],
            "homename"    =>$param["homename"],
            "homemobile"  =>$param["homemobile"],
            "createtime"=>time()
        );
        $res = Db("housemessage")->insert($data);
        if($res){
            $this->success('管家评论成功！');
        }
        /*$url = "http://117.158.24.187:8001/LsInterfaceServer/phoneServer/getOrganizationInfo";
        $request = (new LeSoft())->GetSoftUrl($url, []);
        $a = json_decode($request, true);*/
        //print_r($a["Result"]);die();

    }
    /**
     * 是否允许评价
     */
    public function my_housemessagemsg(){
        $userinfo = $this->auth->getUserinfo();
        $res = Db("housemessage")->field("id,createtime")->where("pk_house='{$userinfo['pk_house']}' AND user_mobile='{$userinfo['mobile']}'")->order("id desc")->find();
        if($res["createtime"]){//判断是否可以评价
            if(time()<$res["createtime"]+config("site.days")*24*3600){
                $this->error("近期已评价请".config("site.days")."天后评价！");
            }
        }
        $this->success("允许进入评价！");
    }
    /**
     * 管家评论展示
     */
    public function my_housemessagelist(){
        $userinfo = $this->auth->getUserinfo();
        $page = $this->request->param('page', 1);
        $pageSize = 10;
        if (!$page) {
            $this->error('无效参数page');
        }
        $res = Db("housemessage")->order("createtime desc")->where("pk_house='{$userinfo['pk_house']}' AND user_mobile='{$userinfo['mobile']}'")->page($page,$pageSize)->select();
        $data =[] ;
        if($res){
            foreach ($res as $k=>$v){
                $re["reson"]       = $v["reson"];
                $re["satisfied"]   = $v["satisfied"];
                $re["evaluatetext"]= $v["evaluatetext"];
                $re["createtime"]  = $v["createtime"];
                $data[] = $re;
            }
        }
        $this->success("成功",$data);
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
            'pkClient'    => $this->auth->pk_client,
            'gotopage'    => $page,
            'pageSize'    => $pageSize,
        ];
        $request = (new LeSoft())->GetSoftUrl(self::INVOICE_URL, $param);
        $data = json_decode($request, true);
        $this->success('获取成功', isset($data['Result']['datas']) ? $data['Result']['datas'] : []);
    }

    /**
     * 用户是否是认证业主
     */
    public function  getuserinfo(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('手机号为空完善信息！',"","-1");
        }
        $param["phone"] = $mobile;
        $authos = $this->auth->mc_lecheck($param);
        if(empty($authos)){
            $this->error('身份认证审核中，暂时无法体验！',"","-2");
        }else{
            //判断是否有用户楼房信息
            $uid = $this->auth->id;
            $house = Db("user")->field("house_name")->where("id={$uid}")->find();
            if(empty($house["house_name"])){//更新数据
                $data = array(
                    "house_name"=>$authos["house_name"],
                    "pk_house"=>$authos["pk_house"],
                    "pk_project"=>$authos["pk_project"],
                    "project_name"=>$authos["project_name"],
                    "pk_client"=>$authos["pk_client"],
                );
                Db("user")->where("id={$uid}")->update($data);
            }
            $this->success('允许进入');
        }
    }

    /**
     * 测试用户是否是认证业主
     */
    public function  getusertest(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('手机号为空完善信息！',"","-1");
        }
        $param["phone"] = $mobile;
        $authos = $this->auth->mc_lechecks($param);

        if(empty($authos)){
            $this->error('身份认证审核中，暂时无法体验！',"","-2");
        }else{
            //判断是否有用户楼房信息
            $uid = $this->auth->id;
            $house = Db("user")->field("house_name")->where("id={$uid}")->find();
            if(empty($house["house_name"])){//更新数据
                $data = array(
                    "house_name"=>$authos["house_name"],
                    "pk_house"=>$authos["pk_house"],
                    "pk_project"=>$authos["pk_project"],
                    "project_name"=>$authos["project_name"],
                    "pk_client"=>$authos["pk_client"],
                    );
                Db("user")->where("id={$uid}")->update($data);
            }
            $this->success('允许进入');
        }
    }

    /**
     * 获取用户信息
     */
    public function  getuserinfos(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('手机号为空完善信息！',"","-1");
        }
        $user = $this->auth->getUserinfo();
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


    /**
     * 修改联系人
     */
    public function edithomepeople(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('未完善信息！');
        }
        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if(empty($member)){
            $this->error('您未是认证业主无权访问！',"","-2");
        }
        //修改通过页面传递pk_client
        $le = new LeSoft();
        $getparam = $this->request->param();
        $params = [
            'staffname'    =>$getparam["name"],
            'sex'          =>$getparam["sex"],
            'mobile'       =>$getparam["mobile"],
            'relation'     =>$getparam["relation"],
            'operationType'=>1,
            'nonce_str'    =>$le->SjStr("15"),
            'pk_staff'     =>$getparam["pk_staff"],//联系人主键
            /*'staffcode'    =>$le->SjStr("10"),
            'pk_staff'     =>$le->SjStr("20"),*/
            /*'createtime'   =>date("Y-d-m H:i:s",time()),*/
            'updatetime'   =>date("Y-d-m H:i:s",time())
        ];
        //修改通过页面传递pk_client
        if($this->request->param("pk_client")){//传递的有
            $params["pk_client"] = $this->request->param("pk_client");
        }else{
            $this->error('参数错误！！');
        }
        //修改通过页面传递pk_client
        $res = $le->GetSoftUrl(self::ADDUSER_URL, $params);
        $result = json_decode($res, true);
        if ($result['StateCode'] == 0) {
            $this->success('修改成功');
        } else {
            $this->error(isset($result['ErrorMsg']) ? $result['ErrorMsg'] : '修改失败！');
        }

    }

    /**
     * 删除家人
     */
    public function  delhomepeople(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('未完善信息！');
        }
        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if(empty($member)){
            $this->error('您未是认证业主无权访问！',"","-2");
        }
        $getparam = $this->request->param();
        $le = new LeSoft();
        $params = [
            'pk_staff'     =>$getparam["pk_staff"],//联系人主键
            'staffname'    =>$getparam["staffname"],//联系人名称
            'operationType'=> 2,
            'nonce_str'    =>$le->SjStr("15"),
            'staffcode'    =>$le->SjStr("10"),
            'createtime'   =>date("Y-d-m H:i:s",time()),
            'updatetime'   =>date("Y-d-m H:i:s",time())
        ];
        //删除通过页面传递pk_client
        if($this->request->param("pk_client")){//传递的有
            $params["pk_client"] = $this->request->param("pk_client");
        }else{
            $this->error('参数错误！！');
        }
        //删除通过页面传递pk_client
        $res = $le->GetSoftUrl(self::ADDUSER_URL, $params);
        $result = json_decode($res, true);
        if ($result['StateCode'] == 0) {
            $this->success('联系人删除成功');
        } else {
            $this->error(isset($result['ErrorMsg']) ? $result['ErrorMsg'] : '联系人删除失败！！');
        }
    }
}