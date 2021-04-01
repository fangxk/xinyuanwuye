<?php


namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Favorite;
use think\Exception;
use think\exception\DbException;

class Activity extends Api
{
    protected $noNeedLogin = '';
    protected $noNeedRight = '*';

    private $month = ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'];


    /**
     * 获取所有活动
     */
    public function getList()
    {
        $page = $this->request->param('page');
        $user_id = $this->auth->id;
        $project_name = $this->auth->project_name;

        if (!$page) {
            $this->error('无效参数');
        }

        $data = db('activity_report')->alias('b')->join('activity a', 'a.id = b.aid')->field('a.*,b.uid')->where('b.uid', $this->auth->id)->order('a.starttime asc')->page($page, 10)->select();

        $data = collection($data)->each(function ($item) use ($user_id,$project_name) {
            $month = date('m', $item['starttime']);
            $item['month'] = $this->month[$month - 1];
            $item['day'] = date('d', $item['starttime']);
            $item['is_apply'] = 1;

            $is_favorite = db('activity_favorite')->where('user_id', $user_id)->where('activity_id', $item['id'])->find();
            if (!empty($is_favorite)) {
                $item['is_favorite'] = 1;
            } else {
                $item['is_favorite'] = 0;
            }
            // 活动状态
            $time = time();
            if ($time < $item['starttime']) {
                $item['status'] = '未开始';
            } elseif ($time > $item['endtime']) {
                $item['status'] = '已结束';
            } elseif ($time > $item['starttime'] && $time < $item['endtime']) {
                $item['status'] = '进行中';
            }
            $item['people'] = db('activity_report')->where('aid', $item['id'])->count();
            $item['users'] = db('activity_report')->order('id desc')->where('aid', $item['id'])->limit(3)->column('avatar');
            /*$item['address'] = !empty($project_name)?$project_name:"鑫苑物业";*/
            $report = db('activity_report')->field("project_name")->where("aid={$item['id']} AND uid={$user_id}")->find();
            $item['address'] = isset($report["project_name"])?$report["project_name"]:"鑫苑物业";

            $item['starttime'] = date('Y年m月d日', $item['starttime']);
            return $item;
        })->toArray();
        $this->success('获取成功', imgUrl($data, 'picimage'));
    }


    /**
     * 我的报名
     */
    public function my_apply()
    {
        $page = $this->request->param('page');
        $user_id = $this->auth->id;
        //$project_name = $this->auth->project_name;

        if (!$page) {
            $this->error('无效参数');
        }

        $data = db('activity_report')->alias('b')->join('activity a', 'a.id = b.aid')->field('a.*,b.uid')->where('b.uid', $this->auth->id)->where('a.type', '0')->order('a.starttime asc')->page($page, 10)->select();

        $data = collection($data)->each(function ($item) use ($user_id) {
            $month = date('m', $item['starttime']);
            $item['month'] = $this->month[$month - 1];
            $item['day'] = date('d', $item['starttime']);
            $item['is_apply'] = 1;

            $is_favorite = db('activity_favorite')->where('user_id', $user_id)->where('activity_id', $item['id'])->find();
            if (!empty($is_favorite)) {
                $item['is_favorite'] = 1;
            } else {
                $item['is_favorite'] = 0;
            }
            // 活动状态
            $time = time();
            if ($time < $item['starttime']) {
                $item['status'] = '未开始';
            } elseif ($time > $item['endtime']) {
                $item['status'] = '已结束';
            } elseif ($time > $item['starttime'] && $time < $item['endtime']) {
                $item['status'] = '进行中';
            }

            $item['people'] = db('activity_report')->where('aid', $item['id'])->count();
            $item['users'] = db('activity_report')->order('id desc')->where('aid', $item['id'])->limit(3)->column('avatar');
            $report = db('activity_report')->field("project_name")->where("aid={$item['id']} AND uid={$user_id}")->find();
            $item['address'] = isset($report["project_name"])?$report["project_name"]:"鑫苑物业";
            //$item['address'] = !empty($project_name)?$project_name:"鑫苑物业";
            $item['starttime'] = date('Y年m月d日', $item['starttime']);
            return $item;
        })->toArray();

        $this->success('获取成功', imgUrl($data, 'picimage'));
    }


    /**
     * 我的投票
     */
    public function my_vote()
    {
        $page = $this->request->param('page');
        $user_id = $this->auth->id;
        //$project_name = $this->auth->project_name;

        $data = db('activity_report')->alias('b')->join('activity a', 'a.id = b.aid')->field('a.*,b.uid')->where('b.uid', $this->auth->id)->where('a.type', '1')->order('a.starttime asc')->page($page, 10)->select();

        $data = collection($data)->each(function ($item) use ($user_id) {
            $month = date('m', $item['starttime']);
            $item['month'] = $this->month[$month - 1];
            $item['day'] = date('d', $item['starttime']);
            $item['is_apply'] = 1;
            // 活动状态
            $time = time();
            if ($time < $item['starttime']) {
                $item['status'] = '未开始';
            } elseif ($time > $item['endtime']) {
                $item['status'] = '已结束';
            } elseif ($time > $item['starttime'] && $time < $item['endtime']) {
                $item['status'] = '进行中';
            }
            $is_favorite = db('activity_favorite')->where('user_id', $user_id)->where('activity_id', $item['id'])->find();
            if (!empty($is_favorite)) {
                $item['is_favorite'] = 1;
            } else {
                $item['is_favorite'] = 0;
            }

            $item['people'] = db('activity_report')->where('aid', $item['id'])->count();

            $users = db('activity_report')->order('id desc')->where('aid', $item['id'])->limit(3)->column('uid');
            $item['users'] = db('user')->where('id', 'in', $users)->column('avatar');
            $report = db('activity_report')->field("project_name")->where("aid={$item['id']} AND uid={$user_id}")->find();
            $item['address'] = isset($report["project_name"])?$report["project_name"]:"鑫苑物业";
            //$item['address'] = !empty($project_name)?$project_name:"鑫苑物业";
            $item['starttime'] = date('Y年m月d日', $item['starttime']);
            return $item;
        })->toArray();

        $this->success('获取成功', imgUrl($data, 'picimage'));
    }


    /**
     * 我的收藏
     */
    public function my_favorite()
    {
        $page = $this->request->param('page');
        $user_id = $this->auth->id;
        $project_name = $this->auth->project_name;

        $data = db('activity_favorite')->alias('f')->join('activity a', 'a.id = f.activity_id')->where('f.user_id', $this->auth->id)->order('a.starttime asc')->page($page, 10)->select();
        $data = collection($data)->each(function ($item) use ($user_id,$project_name) {
            $item['is_favorite'] = 1;
            if($item["type"] !=2){
                $month = date('m', $item['starttime']);
                $item['month'] = $this->month[$month - 1];
                $item['day'] = date('d', $item['starttime']);
                $is_vote = db('activity_report')->where('aid', $item['id'])->where('uid', $user_id)->find();
                if (!empty($is_vote)) {
                    $item['is_apply'] = 1;
                } else {
                    $item['is_apply'] = 0;
                }
                // 活动状态
                $time = time();
                if ($time < $item['starttime']) {
                    $item['status'] = '未开始';
                } elseif ($time > $item['endtime']) {
                    $item['status'] = '已结束';
                } elseif ($time > $item['starttime'] && $time < $item['endtime']) {
                    $item['status'] = '进行中';
                }
                $item['people'] = db('activity_report')->where('aid', $item['id'])->count();
                $users = db('activity_report')->order('id desc')->where('aid', $item['id'])->limit(3)->column('uid');
                $item['users'] = db('user')->where('id', 'in', $users)->column('avatar');
                $report = db('activity_report')->field("project_name")->where("aid={$item['id']} AND uid={$user_id}")->find();
                //$item['address'] = isset($report["project_name"])?$report["project_name"]:"鑫苑物业";;
                //$item['address'] = !empty($project_name)?$project_name:"鑫苑物业";
                $item['starttime'] = date('Y年m月d日', $item['starttime']);
            }else{
                $month = date('m', $item['createtime']);
                $item['month'] = $this->month[$month - 1];
                $item['day'] = date('d', $item['createtime']);
                $item['starttime'] = date('Y年m月d日', $item['createtime']);
            }
            $item['address'] = !empty($project_name)?$project_name:"鑫苑物业";
            return $item;
        })->toArray();

        $this->success('获取成功', imgUrl($data, 'picimage'));
    }


    /**
     * 收藏/取消收藏
     *
     * @param int activity_id 活动id
     * @throws DbException
     */
    public function favorite()
    {
        $activity_id = $this->request->post('activity_id');

        if (!$activity_id) {
            $this->error('无效参数');
        }

        $res = (new Favorite())->favorite($this->auth->id, $activity_id);
        if ($res) {
            $this->success('收藏成功');
        }

        $this->success('已取消收藏');
    }


    /**
     * 活动详情
     *
     * @param int activity_id 活动id
     * @throws DbException|Exception
     */
    public function detail()
    {
        $mobile = $this->auth->mobile;
        $uid = $this->auth->id;
        if (empty($mobile)) {
            $this->error('请先认证');
        }
        $param["phone"] = $mobile;
        $member = $this->auth->mc_lecheck($param);
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }

        $activity_id = $this->request->param('activity_id');

        if (!$activity_id) {
            $this->error('无效参数');
        }

        $data = db('activity')->where('id', $activity_id)->find();
        // 封面图
        $data['picimage'] = cdnurl($data['picimage'], true);

        // 参与人数、头像
        $data['people'] = db('activity_report')->where('aid', $data['id'])->count();
        $users = db('activity_report')->where('aid', $data['id'])->order('id desc')->limit(6)->column('uid');
        $data['users'] = db('user')->where('id', 'in', $users)->column('avatar');
        //收藏状态
        $favorite = Db("activity_favorite")->where("activity_id={$activity_id} AND user_id={$uid}")->find();
        $data['favorite'] = empty($favorite)?0:1;
        // 活动状态
        $time = time();
        if ($time < $data['starttime']) {
            $data['status_text'] = '未开始';
            $data['status'] = 0;
        } elseif ($time > $data['endtime']) {
            $data['status_text'] = '已结束';
            $data['status'] = 2;
        } elseif ($time > $data['starttime'] && $time < $data['endtime']) {
            $data['status_text'] = '进行中';
            $data['status'] = 1;
        }

        // 是否已报名/已投票
        $is_apply = db('activity_report')->where('aid', $data['id'])->where('uid', $this->auth->id)->find();
        if (!empty($is_apply)) {
            $data['is_apply'] = 1;
        } else {
            $data['is_apply'] = 0;
        }

        // 活动倒计时
        if ($data['starttime'] > $time) {
            $data['countdown'] = '活动未开始';
        } elseif ($data['endtime'] > $time) {
            // 天数
            $date = floor(($data['endtime'] - $time) / 86400);
            // 小时数
            $hour = floor(($data['endtime'] - $time) % 86400 / 3600);
            if ($date > 0) {
                $data['countdown'] = '距离结束还有' . $date . '天' . $hour . '小时';
            } else {
                $data['countdown'] = '距离结束还有' . $hour . '小时';
            }
        } else {
            $data['countdown'] = '活动已结束';
        }

        // 投票活动选项
        if ($data['type'] == 1) {
            $options = db('activity_vote_options')->where('voteid', $data['id'])->where('statusswitch', 1)->order('id desc')->select();
           foreach ($options as $ke=>$item){
               $total  = db('activity_report')->where('aid', $item['voteid'])->count();
               $people = db('activity_report')->where('aid', $item['voteid'])->where("FIND_IN_SET({$item['id']}, option_ids)")->count();
               $options[$ke]['people'] = $people;
               if($total){
                   $options[$ke]['proportion'] = round($people / $total, 1) * 100;
               }else{
                   $options[$ke]['proportion'] = 0;
               }
               $options[$ke]["is_selected"] = 0;
               if(!empty($is_apply["option_ids"])){
                   if(strpos("{$is_apply['option_ids']}","{$item['id']}")!==false){
                       $options[$ke]["is_selected"] = 1;
                   }
               }
           }
            //print_r($options);die;
            /*$options = collection($options)->each(function ($item) {
                $total  = db('activity_report')->where('aid', $item['voteid'])->count();
                $people = db('activity_report')->where('aid', $item['voteid'])->where("FIND_IN_SET({$item['id']}, option_ids)")->count();
                $item['people'] = $people;
                if($total){
                    $item['proportion'] = round($people / $total, 1) * 100;
                }else{
                    $item['proportion'] = 0;
                }
                $item["is_selected"] = '';
                print_r($item);die;
                if(!empty($is_apply["option_ids"])){
                    if(strpos($is_apply["option_ids"],$item["id"])){
                        $item["is_selected"] = 1;
                    }
                }
                //$item["is_selected"] = isset($is_apply["option_ids"])?;
                return $item;
            })->toArray();*/
            $data['options'] = $options;
        }
        if($data["type"]!=2){
            // 截至日期
            $data['endtime'] = date('Y年m月d日', $data['endtime']);
        }else{
            $data['endtime'] = date('Y年m月d日', $data['createtime']);
        }
        if(isset($data["content"])){
            $content = str_replace("<img src=\"/uploads/", "<img src=\"".request()->domain()."/uploads/", $data["content"]);
            /*$content = str_replace("<img src=\"/uploads/", "<img src=".request()->domain()."\"/uploads/", $data["content"]);*/
            //$content = str_replace("max-width:100%;","max-width:10%;",$content);
            $data['content'] = $content;
        }
        //print_r($data);die;
        $this->success('获取成功', $data);
    }
}