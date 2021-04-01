<?php

namespace addons\xshop\library\services;

use fast\Http;
use \think\Exception;

/**
 * App微信授权登录
 */
class WechatApp
{
    const GET_ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    const GET_USERINFO_URL = 'https://api.weixin.qq.com/sns/userinfo';

    protected $config = [];

    public function __construct($config = []) 
    {
        $this->setConfig($config);
    }

    public function setConfig($config)
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    public function getAuthUrl()
    {
        $data = [
            'appid' => $this->config['appid'],
            'secret' => $this->config['secret'],
            'js_code' => $this->config['code'],
            'grant_type' => 'authorization_code'
        ];
        return self::GET_ACCESS_TOKEN_URL . '?' . http_build_query($data);
    }

    public function login()
    {
        return $this->getUserInfo();
    }

    public function getUserInfo()
    {
        try {
            $token = $this->getAccessToken();
        } catch (\think\Exception $e) {
            throw new \think\Exception($e->getMessage());
        }
        $data = [
            'access_token' => $token['access_token'],
            'openid' => $token['openid'],
            'lang' => $lang ?? 'zh_CN'
        ];
        $ret = Http::post(self::GET_USERINFO_URL, $data);
        $userinfo = json_decode($ret, true);
        if (!$userinfo || isset($userinfo['errcode'])) {
            throw new Exception('[' . $userinfo['errcode'] . ']' . $userinfo['errmsg']);
        }
        $userinfo = $userinfo ? $userinfo : [];
        // $userinfo['avatar'] = isset($userinfo['headimgurl']) ? $userinfo['headimgurl'] : '';
        return $userinfo;
    }

    public function getAccessToken($code = '')
    {
        $data = [
            'appid' => $this->config['appid'],
            'secret' => $this->config['secret'],
            'code' => $this->config['code'],
            'grant_type' => 'authorization_code'
        ];
        $res = Http::post(self::GET_ACCESS_TOKEN_URL, $data);
        $ret = json_decode($res, true);
        if (isset($ret['errcode'])) throw new Exception('[' . $ret['errcode'] . ']' . $ret['errmsg']);
        $this->token = $ret;
        return $this->token;
    }
}