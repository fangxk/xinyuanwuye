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

    const REPAIR_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/wxAddWorkBill';
    /*const REPAIR_URL   = 'http://117.158.24.187:8002/LsInterfaceServer/phoneServer/wxAddWorkBill';*/

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
        if (empty($mobile)) {
            $this->error('请先认证');
        }
        /*print_r(Config::get('upload'));
        die;*/
        $param["phone"] = $mobile;
        /*$param["phone"] = '18510626928';*/
        $member = $this->auth->mc_lecheck($param);
        if(empty($member)){
            $this->error('您不是认证业主无权访问！');
        }
        $post = $this->request->post();
       // $post = ["isPub"=>"0","mobile"=>"18510626928","content"=>"测试数据叽叽叽叽","clientid"=>"00A9A3476E0049D7ED09","houseid"=>"003829AC0F00C579D1E5"];
        $validate = Loader::validate('Repair');
        if (!$validate->check($post)) {
            $this->error($validate->getError());
        }
        if (!Validate::regex($post['mobile'], "^1\d{10}$")) {
            $this->error("手机号不正确");
        }
        $files = isset($_POST["fileimage"])?explode(',',$_POST["fileimage"]):[];
        $data = [];
        if(!empty($files)){
            foreach ($files as $ke=>$v){
                $newfils = curl_file_create(ROOT_PATH.'public'.$v, 'image/png', pathinfo(ROOT_PATH.'public'.$v,PATHINFO_BASENAME));
                $data["fiel[{$ke}]"]=$newfils;
            }
        }
        $param = [
            'billtype'=> 1,
            'sourceType' => '微信',
        ];
        $param  = array_merge($param,$post,$data);
        $resp   = (new LeSoft())->GetSoftUrl(self::REPAIR_URL, $param);
        $result = json_decode($resp, true);

        if ($result['StateCode'] == 0) {
            $this->success('提交成功','',2);
        } else {
            $this->error(isset($result['ErrorMsg']) ? $result['ErrorMsg'] : '报修失败');
        }

    }
    /*
     * 删除图片
     */
    public function delimage(){
        $files = isset($_POST["fileimage"])?explode(',',$_POST["fileimage"]):[];
        //删除图片
        if(!empty($files)){
            foreach ($files as $v){
                $attachmentFile = ROOT_PATH . 'public' . $v;
                if (is_file($attachmentFile)) {
                    @unlink($attachmentFile);
                }
            }
        }
        $this->success('报修成功');
    }

}