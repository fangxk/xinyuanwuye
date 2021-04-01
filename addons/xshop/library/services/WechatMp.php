<?php

namespace addons\xshop\library\services;

use fast\Http;
use \think\Exception;
use think\Session;

class WechatMp
{    
    const AUTH_URL = 'https://api.weixin.qq.com/sns/jscode2session';
    
    // GET appid+secret
    const API_ACCESS_TOKEN = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential';

    // 获取微信小程序码 B接口
    const API_WXACODE_UNLIMITED = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit';

    const ACCESS_TOKEN_CACHE_KEY = 'mp_access_token';

    const OK = 0;
    const IllegalAesKey = -41001;
	const IllegalIv = -41002;
	const IllegalBuffer = -41003;
	const DecodeBase64Error = -41004;

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
        return self::AUTH_URL . '?' . http_build_query($data);
    }

    /**
     * code2Session
     */
    public function login()
    {
        $url = $this->getAuthUrl();
        $res = Http::sendRequest($url, [], 'GET');
        if (!$res['ret']) {
            throw new Exception($res['msg']);
        }
        $data = json_decode($res['msg'], 1);
        if (!empty($data['errcode'])) {
            throw new Exception($data['errmsg']);
        }
        $token = [
            'openid' => $data['openid'],
            'session_key' => $data['session_key'],
            'unionid' => $data['unionid'] ?? '',
            'expires_in' => 2 * 24 * 60 * 60
        ];
        $this->token = $token;
        return $token;
    }

    public function getUserInfo()
    {
        $errCode = $this->decryptData($this->config['encryptedData'], $this->config['iv'], $data);
        if ($errCode == 0) {
            return $data;
        } else {
            throw new Exception("解密失败{$errCode}");
        }
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     */
    public function decryptData($encryptedData, $iv, &$data)
    {
        $sessionKey = $this->config['session_key'];
        if (strlen($sessionKey) != 24) {
			return self::IllegalAesKey;
		}
		$aesKey=base64_decode($sessionKey);
        
		if (strlen($iv) != 24) {
			return self::IllegalIv;
		}
		$aesIV=base64_decode($iv);

		$aesCipher=base64_decode($encryptedData);

		$result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

		$dataObj=json_decode( $result );
		if( $dataObj  == NULL )
		{
			return self::IllegalBuffer;
		}
		if( $dataObj->watermark->appid != $this->config['appid'] )
		{
			return self::IllegalBuffer;
		}
		$data = [
            'openid' => $dataObj->openId,
            'nickname' => $dataObj->nickName ?? '',
            'sex' => $dataObj->gender ?? 0,
            'province' => $dataObj->province ?? '',
            'city' => $dataObj->city ?? '',
            'headimgurl' => $dataObj->avatarUrl ?? '',
            'unionid' => $dataObj->unionId
        ];
		return self::OK;
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

    protected function fetchAccessToken()
    {
        $url = self::API_ACCESS_TOKEN  . '&' . http_build_query([
            'appid' => $this->config['appid'],
            'secret' => $this->config['secret']
        ]);
        $res = Http::get($url);
        if (empty($res)) throw new \think\Exception("请求出错");
        $data = (array)json_decode($res);
        if (isset($data['errcode'])) throw new \think\Exception($data['errmsg'] . $data['errcode']);
        cache(self::ACCESS_TOKEN_CACHE_KEY, $data['access_token'], $data['expires_in'] - 10 * 60);
        return $data['access_token'];
    }

    public function getQrcode(array $query = [])
    {
        $default = [];
        $param = json_encode(array_merge($default, $query));
        $res = Http::post(self::API_WXACODE_UNLIMITED . '?access_token=' . $this->getAccessToken(), $param, [
            CURLOPT_HTTPHEADER => ["Content-Type: application/json;charset=UTF-8","Accept: application/json"],
            CURLOPT_HEADER => 0
        ]);
        if (empty($res)) throw new \think\Exception("请求出错");
        $data = (array)json_decode($res);
        if (isset($data['errcode'])) throw new \think\Exception($data['errmsg'] . $data['errcode']);
        return $res;
    }
        
}