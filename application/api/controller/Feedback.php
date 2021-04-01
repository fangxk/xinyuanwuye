<?php


namespace app\api\controller;


use app\admin\controller\LeSoft;
use app\common\controller\Api;
use think\Loader;
use think\Validate;

class Feedback extends Api
{
    protected $noNeedLogin = '';

    protected $noNeedRight = '*';

    // 投诉建议接口
    const FEEDBACK_URL = 'http://117.158.24.187:8001/LsInterfaceServer/suggestAddAction';


    /**
     * 提交建议
     *
     * @param string pkHouse 房屋主键
     * @param string pkClient 业主主键
     * @param string houseName 房屋名称
     * @param string content 建议内容
     * @param string phone 手机号
     * @param string source 访问来源：CAPP/微信
     */
    public function submit()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
        }

        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if (empty($member)) {
            $this->error('您不是认证业主无权访问！');
        }

        $post = $this->request->post();

        $validate = Loader::validate('Feedback');
        if (!$validate->check($post)) {
            $this->error($validate->getError());
        }
        if (!Validate::regex($post['phone'], "^1\d{10}$")) {
            $this->error("手机号不正确");
        }
        $param = [
            'pkClient'  => $this->auth->pk_client,
            //'pkHouse'   => $this->auth->pk_house,
            //'houseName' => $this->auth->house_name,
            'source'    => '微信'
        ];
        //新增筛选相应的房屋默认切换小区的房屋进行提交
        $pkhouse   = $this->request->param("pk_house");
        $housename = $this->request->param("house_name");
        if($pkhouse && $housename){
            $param["pkHouse"]   = $pkhouse;
            $param["houseName"] = $housename;
        }else{
            $param["pkHouse"] = $this->auth->pk_house;
            $param["houseName"] = $this->auth->house_name;
        }
        //新增筛选相应的房屋

        $param = array_merge($param, $post);

        $resp = (new LeSoft())->GetSoftUrl(self::FEEDBACK_URL, $param);
        $result = json_decode($resp, true);

        if ($result['StateCode'] == 0) {
            $this->success('提交成功');
        } else {
            $this->error(isset($result['ErrorMsg']) ? $result['ErrorMsg'] : '报修失败');
        }
    }
}