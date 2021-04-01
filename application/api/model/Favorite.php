<?php


namespace app\api\model;


use think\exception\DbException;
use think\Model;

class Favorite extends Model
{
    protected $name = 'activity_favorite';

    protected $autoWriteTimestamp = true;

    protected $createTime = 'create_time';

    protected $updateTime = false;


    /**
     * 收藏/取消收藏
     *
     * @param int $user_id 用户id
     * @param int $activity_id 活动id
     * @return bool
     * @throws DbException
     */
    public function favorite($user_id, $activity_id)
    {
        if (!self::get(['user_id' => $user_id, 'activity_id' => $activity_id])) {
            self::create(['user_id' => $user_id, 'activity_id' => $activity_id]);
            return true;
        } else {
            self::where(['user_id' => $user_id, 'activity_id' => $activity_id])->delete();
            return false;
        }
    }
}