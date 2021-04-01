<?php

namespace app\index\controller;

use app\api\model\User;
use app\common\controller\Frontend;
use think\exception\DbException;
use think\Config;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function wechat()
    {
        $params = $this->request->param();
        $code = $params['code'];
        $app_id     = config('site.mp_appid');
        $app_secret = config('site.mp_app_secret');

        $oauth2Url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$app_id}&secret={$app_secret}&code={$code}&grant_type=authorization_code";
        $oauth2    = $this->get_json($oauth2Url);
        $access_token = $oauth2["access_token"];
        $openid       = $oauth2['openid'];

        $user = db('user')->where(['mp_openid' => $openid])->find();
        if ($user) {//判断用户是否存在
            $ret = $this->getUserInfo($user['mp_openid']);
            if ($ret) {
                $data = ['userinfo' => $this->auth->getUserinfo()];
            } else {
                $this->error($this->auth->getError());
            }
        } else {
            $get_user_info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";//"https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
            $userinfo = $this->get_json($get_user_info_url);
            $username  = $userinfo['nickname'];
            $openid    = $userinfo['openid'];
            $avatar    = $userinfo['headimgurl'];
            $ret = $this->auth->register1($openid,'', $avatar, $username, $userinfo['sex']);
            if ($ret) {
                $data = ['userinfo' => $this->auth->getUserinfo()];
            } else {
                $this->error($this->auth->getError());
            }
        }
        $url = ($_SERVER['SERVER_NAME'])?"http://".$_SERVER['SERVER_NAME'].'/#/pages/index/index':"http://".$_SERVER['HTTP_HOST'];
        $userinfo = json_encode($data);
        $url .= "?userinfo=" . $userinfo;
        $this->redirect($url);exit;
    }
    public function get_json($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }
}
