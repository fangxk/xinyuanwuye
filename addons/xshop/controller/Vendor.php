<?php

namespace addons\xshop\controller;

use addons\xshop\library\Account;
use addons\xshop\model\ConfigModel;
use addons\xshop\model\VendorModel;
use addons\xshop\model\UserModel;
use addons\xshop\model\Auth;
use think\Session;


/**
 * 第三方登录
 * @ApiInternal
 */
class Vendor extends Base
{

    /**
     * 获取授权登录URL
     */
    public function getAuthUrl()
    {
        $param = $this->request->get();
        $redirect_uri = addon_url('xshop/vendor/login', array_merge($param['payload'] ?? [], ['vendor' => $param['vendor']]), false, true);
        $redirect_uri = rawurldecode($redirect_uri);
        switch ($param['vendor']) {
            case 'WxH5' :
                $config = ConfigModel::getByCodes(['xshop_h5_appid', 'xshop_h5_AppSecret']);
                $option = [
                    'appid' => $config['xshop_h5_appid'],
                    'secret' => $config['xshop_h5_AppSecret']
                ];
                $account = Account::register($param['vendor'], $option);
                $url = $account->getAuthUrl($redirect_uri);
                header("Location:" . $url);
                break;
        }
    }

    /**
     * 获取微信公众号js-sdk权限签名参数
     */
    public function getWechatH5JsSignParams()
    {
        $url = $this->request->get('url', '');
        $config = ConfigModel::getByCodes(['xshop_h5_appid', 'xshop_h5_AppSecret']);
        $option = [
            'appid' => $config['xshop_h5_appid'],
            'secret' => $config['xshop_h5_AppSecret']
        ];
        $account = Account::register('WxH5', $option);
        try {
            $res = $account->getJsSignature($url);
            return $this->success('', $res);
        } catch (\think\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 登录
     */
    public function login()
    {
        $vendor = $this->request->get('vendor');
        switch ($vendor) {
            case 'WxH5' :
                $param = $this->request->get();
                $auth_state = Account::getConstant($vendor, "AUTH_STATE");
                if ($param['state'] === Session::get($auth_state) && !empty($param['code'])) {
                    Session::delete($auth_state);
                    $config = ConfigModel::getByCodes(['xshop_h5_appid', 'xshop_h5_AppSecret']);
                    $option = [
                        'appid' => $config['xshop_h5_appid'],
                        'secret' => $config['xshop_h5_AppSecret'],
                        'code' => $param['code']
                    ];
                    VendorModel::binding('Wechat', 'WxH5', $option);
                    $config = get_addon_config('xshop');
                    $url = $config['__DOMAIN__'] . $config['__H5_BASE_PATH__'];
                    if ($config['__H5_ROUTE_MODE__'] == 'hash') {
                        $url .= '#/';
                    }
                    $query = [];
                    if (!empty($param['referer'])) $query['referer'] = $param['referer'];
                    $query['token'] = $this->auth->getToken();
                    $url .= "pages/public/login?" . http_build_query($query);
                    header("Location:" . $url);
                }
                break;
            case 'WechatMp' :
                $config = ConfigModel::getByCodes(['xshop_wx_mp_appid', 'xshop_wx_mp_AppSecret']);
                $param = $this->request->post();
                $vendorModel = VendorModel::where([
                    'platform' => 'WechatMp',
                    'openid' => $param['openid']
                ])->find();
                if (empty($vendorModel)) return $this->error("");
                $option = [
                    'appid' => $config['xshop_wx_mp_appid'],
                    'secret' => $config['xshop_wx_mp_AppSecret'],
                    'encryptedData' => $param['encryptedData'],
                    'iv' => $param['iv'],
                    'session_key' => $vendorModel['session_key']
                ];
                VendorModel::binding('Wechat', 'WechatMp', $option);
                return $this->success('', UserModel::getUserInfo());
                break;
            case 'App' :
                $param = $this->request->post();
                $config = ConfigModel::getByCodes(['xshop_wx_app_appid', 'xshop_wx_app_secret']);
                $option = [
                    'appid' => $config['xshop_wx_app_appid'],
                    'secret' => $config['xshop_wx_app_secret'],
                    'code' => $param['code']
                ];
                VendorModel::binding('Wechat', 'WechatApp', $option);
                return $this->success('', UserModel::getUserInfo());
                break;
        }
    }

    /**
     * 用于小程序openid等临时保存，后续登录需要关联unionid
     */
    public function login2()
    {
        $vendor = $this->request->get('vendor');
        switch ($vendor) {
            case 'WechatMp' :
                $config = ConfigModel::getByCodes(['xshop_wx_mp_appid', 'xshop_wx_mp_AppSecret']);
                $param = $this->request->post();
                $option = [
                    'appid' => $config['xshop_wx_mp_appid'],
                    'secret' => $config['xshop_wx_mp_AppSecret'],
                    'code' => $param['code']
                ];
                $model = VendorModel::saveAccount('Wechat', 'WechatMp', $option);
                $data = ['openid' => $model['openid']];
                if (!empty($model['user_id'])) {
                    $auth = Auth::instance();
                    $auth->direct($model['user_id']);
                    $data['userinfo'] = UserModel::getUserInfo();
                }
                return $this->success('', $data);
                break;
        }

    }

}