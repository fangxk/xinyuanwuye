<?php

namespace addons\xshop\library\services;

use fast\Http;
use think\Exception;
use think\Session;

class WxH5
{
    const GET_AUTH_ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    const GET_USERINFO_URL = 'https://api.weixin.qq.com/sns/userinfo';
    const GET_OAUTH2_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    const GET_ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential';
    const GET_JSAPI_TIKET_URL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi';

    const ACCESS_TOKEN_CACHE_KEY = 'xshop_wechat_h5_token';
    const JSAPI_TIKET_KEY = 'xshop_wechat_jsapi_ticket';
    const AUTH_STATE = 'xshop_wx_auth_state';
    
    protected $config = [];

    public function __construct($config = []) 
    {
        $this->setConfig($config);
    }

    public function getAuthUrl($redirect_uri = '')
    {
        $redirect_uri = $redirect_uri ?? request()->url();
        $state = \fast\Random::numeric(6);
        Session::set(self::AUTH_STATE, $state);
        $form = [
            'appid' => $this->config['appid'],
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'snsapi_userinfo', 
            'state' => $state
        ];
        return self::GET_OAUTH2_URL . '?' . http_build_query($form) . '#wechat_redirect';
    }

    public function setConfig($config)
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    public function login()
    {
        return $this->getUserInfo();
    }

    /**
     * 获取用户信息
     * $code
     */
    public function getUserInfo()
    {
        try {
            $token = $this->getAuthAccessToken();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
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

    public function getAuthAccessToken($code = '')
    {
        $data = [
            'appid' => $this->config['appid'],
            'secret' => $this->config['secret'],
            'code' => $this->config['code'],
            'grant_type' => 'authorization_code'
        ];
        $res = Http::post(self::GET_AUTH_ACCESS_TOKEN_URL, $data);
        $ret = json_decode($res, true);
        if (isset($ret['errcode'])) throw new Exception('[' . $ret['errcode'] . ']' . $ret['errmsg']);
        $this->token = $ret;
        return $this->token;
    }

    public function getAccessToken()
    {
        if (cache(self::ACCESS_TOKEN_CACHE_KEY)) {
            return cache(self::ACCESS_TOKEN_CACHE_KEY);
        } else {
            $token = $this->fetchAccessToken();
            return $token;
        }
    }

    public function fetchAccessToken()
    {
        $url = self::GET_ACCESS_TOKEN_URL  . '&' . http_build_query([
            'appid' => $this->config['appid'],
            'secret' => $this->config['secret']
        ]);
        $res = Http::get($url);
        if (empty($res)) throw new Exception("请求出错");
        $data = (array)json_decode($res);
        if (isset($data['errcode'])) throw new Exception($data['errmsg'] . $data['errcode']);
        cache(self::ACCESS_TOKEN_CACHE_KEY, $data['access_token'], $data['expires_in'] - 10 * 60);
        return $data['access_token'];
    }

    public function getJsapiTicket()
    {
        if (cache(self::JSAPI_TIKET_KEY)) {
            return cache(self::JSAPI_TIKET_KEY);
        } else {
            $token = $this->fetchJsapiTicket();
            return $token;
        }
    }

    public function fetchJsapiTicket()
    {
        $url = self::GET_JSAPI_TIKET_URL . '&access_token=' . $this->getAccessToken();
        $res = Http::get($url);
        if (empty($res)) throw new Exception("请求服务器出错");
        $data = (array)json_decode($res);
        if (!empty($data['errcode'])) throw new Exception($data['errmsg'] . $data['errcode']);
        cache(self::JSAPI_TIKET_KEY, $data['ticket'], $data['expires_in'] - 10 * 60);
        return $data['ticket'];
    }

    /**
     * js-sdk签名
     */
    public function getJsSignature($url)
    {
        $param = [
            'noncestr' => \fast\Random::alnum(6),
            'jsapi_ticket' => $this->getJsapiTicket(),
            'timestamp' => time() . "",
            'url' => explode('#', $url)[0]
        ];
        ksort($param);
        $arr = [];
        foreach ($param as $k => $v) {
            $arr[] = "$k=$v";
        }
        $string = implode('&', $arr);
        $signature = sha1($string);
        return [
            'app_id' => $this->config['appid'],
            'nonceStr' => $param['noncestr'],
            'timestamp' => $param['timestamp'],
            'signature' => $signature
        ];
    }
}