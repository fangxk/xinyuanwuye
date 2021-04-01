<?php


namespace app\api\controller;

use app\common\controller\Api;
use app\api\model\User;
use think\exception\DbException;
use wx\WXBizDataCrypt;
use think\Config;


class Wechat extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    public function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    /**
     * 微信小程序
     */
    public function wxdecode(){
        $code = $this->request->param('code');

        //前台用户的的令牌
        $session_key = $this->request->param('session_key','');
        $iv = $this->request->param('iv');
        $encryptedData = $this->request->param('encryptedData');
        if (!$code || !$iv || !$encryptedData) {
            $this->error('缺少参数');
        }
        if (!$session_key){
            $session_keys = $this->getSessionKey($code);
            $session_keyss = json_decode($session_keys, true);
            if (isset($session_keyss['session_key'])){
                $session_key = $session_keyss['session_key'];
            }
        }
        // 解密
        $appid      = config('site.xshop_wx_mp_appid');
        $pc         = new WXBizDataCrypt($appid, $session_key);
        $errCode    = $pc->decryptData($encryptedData, $iv, $data);

        //判断用户是否存在
        if ($errCode == 0) {

            $user = db('user')->where(array("xc_openid"=>$data['openId']))->find();
            if ($user) {//判断用户是否存在
                $ret = $this->getUserInfo($user['xc_openid']);
                if ($ret) {
                    $this->success('用户获取成功', array('userinfo' => $this->auth->getUserinfo()));
                } else {
                    $this->error($this->auth->getError());
                }
            } else {
                $username  = $data['nickName'];
                $openid    = $data['openId'];
                $avatar    = $data['avatarUrl'];

                $ret = $this->auth->register1('',$openid, $avatar, $username,$data["gender"]);
                if ($ret) {
                    $this->success('用户注册成功', array('userinfo' => $this->auth->getUserinfo()));
                } else {
                    $this->error($this->auth->getError());
                }
            }
        } else {
            $this->error('解密失败！！');
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
        return $data;
    }

    /**
     * 获取用户信息
     *
     * @param string $openid 用户openid
     * @return array
     * @throws DbException
     */
    public function getUserInfo($openid)
    {
        $ret = $this->auth->login2($openid);
        if ($ret) {
            return true;
        } else {
            return false;
        }
    }



}