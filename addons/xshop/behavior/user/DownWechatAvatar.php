<?php

namespace addons\xshop\behavior\user;

class DownWechatAvatar
{
    public function xshopGetWechatUserinfo(&$payload)
    {
        $userinfo = $payload['wechat_userinfo'];
        $user = $payload['user'];
        if (class_exists('think\Queue')) { // 异步下载
            $params = [
                'url' => $userinfo['headimgurl'],
                'id' => $user->id
            ];
            \think\Queue::push('addons\xshop\jobs\DownWechatAvatar', $params, 'xshop');
        } else { // 同步下载
            $img = (new \addons\xshop\library\ImageHandle)->save($userinfo['headimgurl']);
            $user->avatar = $img;
        }
    }
}