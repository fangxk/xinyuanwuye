<?php

namespace addons\xshop\model;

use app\common\library\Auth;
use app\common\library\Sms;
use addons\xshop\exception\Exception;
use addons\xshop\exception\NotFoundException;
use addons\xshop\library\Account;
use addons\xshop\library\Weixin;
use addons\xshop\library\TT;
use fast\Random;
use think\Db;
use think\exception\PDOException;

class VendorModel extends Model
{
    protected $name = "xshop_vendor";
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'create_time';
    protected $updateTime = false;

    /**
     * 微信小程序登录
     */
    public static function wxMPLogin($code)
    {
        $config = ConfigModel::getByCodes(['xshop_wx_mp_appid', 'xshop_wx_mp_AppSecret']);
        $config = [
            'appid' => $config['xshop_wx_mp_appid'],
            'secret' => $config['xshop_wx_mp_AppSecret']
        ];
        $wx = Weixin::instance($config);
        $res = $wx->loginByCode($code);
        if (!$res['ret']) {
            throw new Exception($res['msg']);
        }
        $data = json_decode($res['msg'], 1);
        if (!empty($data['errcode'])) {
            throw new Exception($data['errmsg']);
        }
        return $data;
    }

    /**
     * 头条小程序登录
     */
    public static function ttMPLogin($code)
    {
        $config = ConfigModel::getByCodes(['xshop_tt_mp_appid', 'xshop_tt_mp_AppSecret']);
        $config = [
            'appid' => $config['xshop_tt_mp_appid'],
            'secret' => $config['xshop_tt_mp_AppSecret']
        ];
        $tt = TT::instance($config);
        $res = $tt->loginByCode($code);
        if (!$res['ret']) {
            throw new Exception($res['msg']);
        }
        $data = json_decode($res['msg'], 1);
        if (!empty($data['error'])) {
            throw new Exception($data['message']);
        }
        return $data;
    }
    
    /**
     * 第三方账户绑定
     */
    public static function binding($vendor, $platform, $config)
    {
        $now = time();
        $auth = Auth::instance();
        $account = Account::register($platform, $config);
        try {
            $userinfo = $account->getUserInfo();
        } catch (\think\Exception $e) {
            throw new Exception($e->getMessage());
        }
        $appendData = [
            'platform' => $platform,
            'vendor' => $vendor,
            'access_token' => $account->token['access_token'] ?? '',
            'refresh_token' => $account->token['refresh_token'] ?? '',
            'expires_in' => $account->token['expires_in'] ?? 0,
            'login_time' => $now,
            'expires_time' => $now + ($account->token['expires_in'] ?? 0)
        ];
        foreach ($appendData as $k => $v) {
            $userinfo[$k] = $v;
        }

        $vendorModel = self::createOrUpdateAccount($userinfo);

        if (empty($vendorModel['user_id'])) { // 如果未绑定账号则创建一个账号
            $username = Random::alnum(20);
            $password = Random::alnum(6);
            $domain = request()->host();

            Db::startTrans();
            try {
                // 默认注册一个会员
                $result = $auth->register($username, $password, $username . '@' . $domain, '');
                if (!$result) {
                    return false;
                }
                $user = $auth->getUser();
                $fields = ['username' => 'u' . $user->id, 'email' => 'u' . $user->id . '@' . $domain];
                if (isset($userinfo['nickname'])) {
                    $fields['nickname'] = $userinfo['nickname'];
                }
                if (!empty($userinfo['headimgurl'])) { // 用户头像下载到本地 由于网络原因，采用异步下载
                    $payload = [
                        'wechat_userinfo' => $userinfo,
                        'user' => $user
                    ];
                    $fields['avatar'] = $userinfo['headimgurl'];
                    \think\Hook::listen('xshop_get_wechat_userinfo', $payload);
                }

                // 更新会员资料
                $user = UserModel::get($user->id);
                $user->save($fields);

                // 保存第三方信息
                $vendorModel->save(['user_id' => $user['id']]);
                $auth->direct($user->id);
                $payload = [
                    'user' => $auth->getUser()
                ];
                \think\Hook::listen('xshop_user_register_successed', $payload);
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $auth->logout();
                return false;
            }
        } else {
            if ($userinfo['headimgurl'] != $vendorModel['headimgurl'] && !empty($userinfo['headimgurl'])) { // 用户更新头像，下载到本地
                $user = UserModel::get($vendorModel['user_id']);
                $user['avatar'] = $userinfo['headimgurl'];
                $payload = [
                    'wechat_userinfo' => $userinfo,
                    'user' => $user
                ];
                \think\Hook::listen('xshop_get_wechat_userinfo', $payload);
                $user->save();
            }
            $auth->direct($vendorModel['user_id']);
        }

        return UserModel::getUserInfo();
    }

    /**
     * 保存第三方账号
     */
    public static function saveAccount($vendor, $platform, $config)
    {
        $account = Account::register($platform, $config);
        try {
            $userinfo = $account->login();
        } catch (\think\Exception $e) {
            throw new Exception($e->getMessage());
        }
        $userinfo['vendor'] = $vendor;
        $userinfo['platform'] = $platform;
        
        return self::createOrUpdateAccount($userinfo);
    }

    /**
     * 更新或保存第三方账号
     */
    public static function createOrUpdateAccount($userinfo)
    {
        $model = self::where([
            'vendor' => $userinfo['vendor'],
            'platform' => $userinfo['platform'],
            'openid' => $userinfo['openid']
        ])->find();
        
        if ($model) {
            $model->allowField(true)->save($userinfo);
        } else { // 首次登录
            $model = new VendorModel($userinfo);
            $model->allowField(true)->save();
        }

        // 如果是临时账号，且获取了unionid说明已登录过同主体账号,这时可以直接绑定账号
        if (empty($model['user_id']) && !empty($model['unionid'])) { 
            $vendorModel = self::where([
                'unionid' => $model['unionid'],
                'vendor' => $model['vendor']
            ])->find();
            if (!empty($vendorModel)) {
                $model->allowField(true)->save([
                    'user_id' => $vendorModel['user_id']
                ]);
            }
        }
        return $model;
    }
}
