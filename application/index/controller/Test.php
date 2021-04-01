<?php

namespace app\index\controller;

use app\api\model\User;
use app\common\controller\Frontend;
use think\exception\DbException;
use think\Config;

class Test extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        $code = $this->request->param('code');
        if (!$code) {
            $this->error('无效参数code');
        }

        $userInfo = $this->userInfo($code);

        if (isset($userInfo['is_reg']) && $userInfo['is_reg'] == 1) {
            $this->redirect("https://xywy.huijipin.cn/#/pages/index/index?userinfo=".$userInfo);
        }
        $ret = $this->auth->register1($userInfo['openid'], $userInfo['headimgurl'], $userInfo['nickname'], $userInfo['sex']);
        $this->redirect("https://xywy.huijipin.cn/#/pages/index/index?userinfo=".$userInfo);



    }

    /**
     *
     * @author 授权链接
     * @date 2020-10-28 15:07
     */
    public function authorizeUrl(){
        $redirect_url= urlencode("https://xywy.huijipin.cn/index/test/index");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".config('site.mp_appid')."&redirect_uri=".$redirect_url."&connect_redirect=1&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
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
            return $this->auth->getUserinfo();
        } else {
            $this->error($this->auth->getError());
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
        $token  = self::getAccessToken(config('site.mp_appid'), config('site.mp_app_secret'), $code);
        $token  = $token['access_token'];
        $openid = $token['openid'];
        if ((new User())->where('mp_openid', $openid)->find()) {
            return ['is_reg' => 1, 'userinfo' => $this->getUserInfo($openid)];
        }

        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$token&openid=$openid&lang=zh_CN";
        $resp = httpRequest($url);
        return json_decode($resp, true);
    }

    //获取token
    private static function getAccessToken($appId, $appSecret, $code)
    {
        // 公众号获取 access_token
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appId&secret=$appSecret&code=$code&grant_type=authorization_code";
        $resp = httpRequest($url);
        return json_decode($resp, true);
    }

}
