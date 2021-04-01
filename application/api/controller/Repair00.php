<?php


namespace app\api\controller;


use app\admin\controller\LeSoft;
use app\common\controller\Api;
use think\Config;
use think\Loader;
use think\Validate;

class Repair extends Api
{
    protected $noNeedLogin = '';

    protected $noNeedRight = '*';

    //const REPAIR_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/wxAddWorkBill';
    const REPAIR_URL = 'http://117.158.24.187:8002/LsInterfaceServer/phoneServer/wxAddWorkBill';



    /**
     * 提交报事报修
     *
     * @param string clientid 住户主键
     * @param string houseid 房屋主键
     * @param int billtype 报修类型:1=维修单,2=投诉单
     * @param string content 报修内容
     * @param string isPub 报修区域
     * @param string mobile 手机号码
     * @param string sourceType 来源(业主APP/微信)
     */
    public function submit()
    {
        $mobile = $this->auth->mobile;
        /*//测试
        $lesoft = new LeSoft();
        $pa["phone"] = "159371646061";
        $regions = json_decode($lesoft->GetSoftUrl("http://117.158.24.187:8002/LsInterfaceServer/phoneServer/wxClientHouse", $pa), true);
        print_r($regions);die;
        //测试*/

        if (empty($mobile)) {
            $this->error('请先认证');
        }

        /*$param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if(empty($member)){
            $this->error('您不是认证业主无权访问！');
        }*/

        $post = $this->request->post();
        $validate = Loader::validate('Repair');
        if (!$validate->check($post)) {
            $this->error($validate->getError());
        }
        if (!Validate::regex($post['mobile'], "^1\d{10}$")) {
            $this->error("手机号不正确");
        }

        $param = [
            'billtype' => 1,
            'sourceType' => '微信'
        ];
        /*$param = [
            'billtype' => 1,
            'clientid'   => $this->auth->pk_client,
            'houseid'    => $this->auth->pk_house,
            'sourceType' => '微信'
        ];*/
        $param = array_merge($param, $post);
        $resp = (new LeSoft())->GetSoftUrl(self::REPAIR_URL, $param);
        $result = json_decode($resp, true);

        if ($result['StateCode'] == 0) {
            $this->success('报修成功');
        } else {
            $this->error(isset($result['ErrorMsg']) ? $result['ErrorMsg'] : '报修失败');
        }

    }
}