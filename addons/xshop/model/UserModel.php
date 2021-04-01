<?php

namespace addons\xshop\model;

use app\common\library\Auth;
use app\common\library\Sms;
use addons\xshop\exception\Exception;
use addons\xshop\exception\NotFoundExceptionException;

class UserModel extends Model
{
    protected $name = "user";
    protected $visible = [
        'nickname', 'avatar'
    ];
    protected $append = [
        'avatar'
    ];

    public static function info()
    {
        return Auth::instance();
    }

    public static function getUserInfo()
    {
        $userinfo = self::info()->getUserInfo();
        if (empty($userinfo['avatar'])) $userinfo['avatar'] = letter_avatar($userinfo['avatar']);
        $userinfo['avatar'] = cdnurl($userinfo['avatar'], true);
        return $userinfo;
    }

    /**
     * 编辑用户资料
     */
    public static function editInfo($attributes)
    {
        $user = self::info();
        $allowEditFields = ['password', 'nickname'];
        $data = [];
        foreach ($attributes as $k => $v) {
            if (in_array($k, $allowEditFields)) $data[$k] = $v;
        }
        if (empty($data)) throw new Exception("没有任何修改");
        $userModel = $user->getUser();
        foreach ($data as $k => $v) {
            switch ($k) {
                case 'password': {
                    if (empty($v)) throw new Exception("密码不能为空");
                    $userData = $userModel->getData();
                    $userModel->$k = $user->getEncryptPassword($v, $userData['salt']);
                    break;
                }
                default : {
                    if (empty($v)) throw new Exception("昵称不能为空");
                    $userModel->$k = $v;
                    break;
                }
            }
        }
        $userModel->save();
        return self::getUserInfo();
    }

    /**
     * 手机验证码注册或登录
     */
    public static function registerOrLogin($attributes)
    {
        extract($attributes);
        if (!Sms::check($mobile, $code, 'XShopRegister')) {
            throw new NotFoundException("验证码错误");
        }
        $auth = Auth::instance();
        $user = self::where('mobile', $mobile)->find();
        if (empty($user)) { // 注册
            $auth->register($mobile, '', '', $mobile);
            $payload = [
                'user' => $auth->getUser()
            ];
            \think\Hook::listen('xshop_user_register_successed', $payload);
            $auth->direct($auth->id);
            return self::getUserInfo();
        } else { // 登录
            $auth->direct($user->id);
            return self::getUserInfo();
        }
    }

    public function getAvatarAttr($value, $data)
    {
        if (empty($value)) {
            return letter_avatar($data['nickname']);
        }
        return cdnurl($value, true);
    }
}
