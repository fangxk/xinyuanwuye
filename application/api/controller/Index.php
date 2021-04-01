<?php

namespace app\api\controller;

use app\common\controller\Api;
use wx\WXBizDataCrypt;
/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }


    /**
     * 获取用户微信绑定的手机号码
     * @param  code  用户登录是的code
     * @param  iv  前段的加密算法
     * @param  encryptedData  包括敏感数据在内的完整用户信息的加密数据
     */
    public function getPhone()
    {
        $code = $this->request->param('code');
        //前台用户的的令牌
        $session_key = $this->request->param('session_key','');
        $iv = $this->request->param('iv');
        $encryptedData = $this->request->param('encryptedData');
        if (!$code || !$iv || !$encryptedData) {
            $this->error('缺少参数');
        }
        if (!$session_key){
            $session_key = $this->getSessionKey($code);
        }

        // 解密
        $appid      = config('site.xshop_wx_mp_appid');
        //$appsecret  = config('site.xshop_wx_mp_AppSecret');
        $pc = new WXBizDataCrypt($appid, $session_key);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        print_r($data);die;
        if ($errCode == 0) {
            $this->success('获取成功', $data);
        } else {
            $this->error('获取失败');
        }
    }
    /**
     * 获取用户会话密钥 session_key
     *
     * @param string $code 微信登录code
     * @return string
     */
    private function getSessionKey($code)
    {
        $appId     = config('site.xshop_wx_mp_appid');
        $appSecret = config('site.xshop_wx_mp_AppSecret');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appId&secret=$appSecret&js_code=$code&grant_type=authorization_code";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($data, true);
        if (isset($response['session_key'])){
            return $response['session_key'];
        }
        return '';

    }

}
