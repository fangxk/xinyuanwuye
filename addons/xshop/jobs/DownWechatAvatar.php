<?php

namespace addons\xshop\jobs;

use think\queue\Job;
use addons\xshop\model\UserModel;

class DownWechatAvatar
{
    public function fire(Job $job, $params)
    {
        $img = (new \addons\xshop\library\ImageHandle)->save($params['url']);
        $user = UserModel::get($params['id']);
        if (!empty($user)) {
            $user->avatar = $img;
            $user->save();
        }
        $job->delete();
    }
}