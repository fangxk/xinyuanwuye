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
//        $url = Request::instance()->param('local');
        $code = $params['code'];
        $app_id = config('site.mp_appid');
        $app_secret = config('site.mp_app_secret');
        //第一步:取全局access_token
        
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$app_id}&secret={$app_secret}";
        $token = $this->get_json($url);

        //第二步:取得openid
        $oauth2Url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$app_id}&secret={$app_secret}&code={$code}&grant_type=authorization_code";
        $oauth2 = $this->get_json($oauth2Url);

        //第三步:根据全局access_token和openid查询用户信息
        $access_token = $token["access_token"];
        $openid = $oauth2['openid'];
        $get_user_info_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
        $userinfo = $this->get_json($get_user_info_url);

        $user = db('user')->where(['mp_openid' => $userinfo['openid']])->find();

        if ($user) {//判断用户是否存在
            $ret = $this->getUserInfo($user['mp_openid']);
            if ($ret) {
                $data = ['userinfo' => $this->auth->getUserinfo()];
            } else {
                $this->error($this->auth->getError());
            }
        } else {
            $username  = $userinfo['nickname'];
            $openid    = $userinfo['openid'];
            $avatar    = $userinfo['headimgurl'];
            $ret = $this->auth->register1($openid, $avatar, $username, $userinfo['sex']);
            if ($ret) {
                $data = ['userinfo' => $this->auth->getUserinfo()];
            } else {
                $this->error($this->auth->getError());
            }
        }

        $url = 'https://xywy.huijipin.cn/#/pages/index/index';
        $userinfo = json_encode($data);
        $url .= "?userinfo=" . $userinfo;
        $this->redirect($url);
        die();
    }


    public function str_url($url)
    {
        $url = htmlspecialchars_decode($url);
        parse_str(parse_url($url)['query'], $params);
        return isset($params['code']) ? $params['code'] : 0;
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
//        print_r($ch);
        return json_decode($output, true);
    }


    public function index()
    {
        $params = file_get_contents("php://input");
        print_r($params);
        $code = $this->request->param('code');
        print_r($this->request->param());
        if (!$code) {
            $this->error('无效参数code');
        }
        $userInfo = $this->userInfo($code);

        $user = json_encode($userInfo);
        if (isset($userInfo['is_reg']) && $userInfo['is_reg'] == 1) {
            $this->redirect("https://xywy.huijipin.cn/#/pages/index/index?userinfo=");
        }
        $ret = $this->auth->register1($userInfo['openid'], $userInfo['headimgurl'], $userInfo['nickname'], $userInfo['sex']);
        $this->redirect("https://xywy.huijipin.cn/#/pages/index/index?userinfo=");


        /*if ($ret) {
            $this->success('授权成功', $this->auth->getUserinfo());
        } else {
            $this->error($this->auth->getError());
        }*/

    }

    /**
     *
     * @author 授权链接
     * @date 2020-10-28 15:07
     */
    public function authorizeUrl()
    {
        $redirect_url = urlencode("https://xywy.huijipin.cn");
        $redirect_url = "https://xywy.huijipin.cn/index/index/wechat";
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . config('site.mp_appid') . "&redirect_uri=" . $redirect_url . "&connect_redirect=1&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
        $this->redirect($url);
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
        $ret = $this->auth->login1($openid);
        if ($ret) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 获取用户信息
     *
     * @param string $code 用户登录code
     * @return array
     * @throws DbException
     */
    public function userInfo($code)
    {
        $result = $this->getOpenID($code);
        $access_token = $result['access_token'];
        $openid = $result['openid'];
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
        $resp = httpRequest($url);
        return json_decode($resp, true);
    }

    /**
     * 获取用户的openid
     *
     * */
    public function getOpenID($code)
    {
        $result = self::getAccessToken(config('site.mp_appid'), config('site.mp_app_secret'), $code);
//        $access_token = $result['access_token'];
//        $openid = $result['openid'];
        return $result;
    }


    /**
     * 获取access_token
     *
     * @param string $appId 公众号的appId
     * @param string $appSecret 公众号的appSecret
     * @param string $code 微信code
     * @return array
     */
    private static function getAccessToken($appId, $appSecret, $code)
    {
        // 公众号获取 access_token
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appId&secret=$appSecret&code=$code&grant_type=authorization_code";
        $resp = httpRequest($url);
        return json_decode($resp, true);
    }

}
