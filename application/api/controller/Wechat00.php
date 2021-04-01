<?php


namespace app\api\controller;

use app\common\controller\Api;
use app\api\model\User;
use think\exception\DbException;


class Wechat extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    /**
     *
     * @author 授权链接
     * @date 2020-10-28 15:07
     */
    public function authorizeUrl(){
        $redirect_url= urlencode("https://xywy.huijipin.cn/api/Wechat/authorize");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".config('site.mp_appid')."&redirect_uri=".$redirect_url."&connect_redirect=1&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
        $this->success('成功',$url);
    }

    /**
     * 用户授权
     * @throws DbException
     */
    public function authorize()
    {
        $code = $this->request->param('code');
        if (!$code) {
            $this->error('无效参数code');
        }

        $userInfo = $this->userInfo($code);

        if (isset($userInfo['is_reg']) && $userInfo['is_reg'] == 1) {
            $this->success('获取成功', $userInfo['userinfo']);
        }

        $ret = $this->auth->register1($userInfo['openid'], $userInfo['headimgurl'], $userInfo['nickname'], $userInfo['sex']);


        if ($ret) {
            $this->success('授权成功', $this->auth->getUserinfo());
        } else {
            $this->error($this->auth->getError());
        }
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
        $result = self::getAccessToken(config('site.mp_appid'), config('site.mp_app_secret'), $code);

        $access_token = $result['access_token'];
        $openid = $result['openid'];

        if ((new User())->where('mp_openid', $openid)->find()) {
            return ['is_reg' => 1, 'userinfo' => $this->getUserInfo($openid)];
        }

        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
        $resp = httpRequest($url);
        return json_decode($resp, true);
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