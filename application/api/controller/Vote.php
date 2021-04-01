<?php
/**
 * Vote.php
 * Create By Company JUN HE
 * User XF
 * @date 2020-10-22 18:59
 */

namespace app\api\controller;

use app\api\model\User;
use app\common\controller\Api;
use think\Exception;
use think\exception\DbException;

class Vote extends Api
{
    protected $noNeedLogin = "*";
    protected $noNeedRight = '*';
    private $month = ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'];

    /**
     * 活动列表
     */
    public function getActivityList()
    {
        $page = max(1, intval($this->request->param('page')));
        $mobile = $this->auth->mobile;
        $uid = $this->auth->id;
        $time = time();
        if (empty($mobile)) {
            $this->error('您未绑定手机号！', '');
        }
        $userinfo = (new User())->doesUserInfo($uid);
        if ($userinfo["pk_project"]) {
            $pk_project = $userinfo["pk_project"];
        } else {
            $lemember = (!empty($mobile)) ? $this->auth->mc_lecheck(array("phone" => $mobile)) : '';
            $pk_project = (!empty($lemember)) ? $lemember["pk_project"] : '';
        }
        $where = "";
        if ($pk_project) {
            $where .= "find_in_set('$pk_project',a2.lecommunity) AND ";
        } else {
            $where .= "a1.status=1 AND ";
        }

        $list = db('activity')->alias('a1')
            ->field("a1.id,a1.title,a1.type,a1.desc,a1.picimage,a1.status,a1.starttime,a1.signtime,a1.endtime,a1.createtime,a2.lecommunity")
            ->join("activity_region a2", "a2.activityid=a1.id")
            ->where($where . "a1.statusswitch=1 AND a1.starttime<{$time} AND {$time}<a1.endtime ")
            ->order("a1.istop,a1.createtime","DESC,DESC")->page($page, 10)->select();
           /* ->order("a1.createtime","DESC")->page($page, 10)->select();*/

        if ($list) {
            foreach ($list as $k => $v) {
                //活动头像
                $da['id'] = $v["id"];
                $da['title'] = $v["title"];
                $da['type'] = $v["type"];
                $da['desc'] = $v["desc"];
                $da['picimage'] = $v["picimage"];
                unset($da["avatar"]);
                unset($da["reports"]);
                unset($da["peoples"]);
                unset($da["entime"]);
                if($v["type"]!=2){
                    $avatar = Db("activity_report")->field("avatar")->where("aid={$v['id']}")->limit('0', '5')->select();
                    if ($avatar) {
                        foreach ($avatar as $vs) {
                            $da['avatar'][] = $vs['avatar'];
                        }
                    }
                    //活动总人数
                    $total = Db("activity_report")->where("aid={$v['id']}")->count();
                    //是否参与
                    $reports = Db("activity_report")->where("aid={$v['id']} and uid={$uid}")->find();
                    $da['reports'] = empty($reports) ? 0 : 1;
                    if ($v["starttime"] < $time && $time < $v["endtime"]) {
                        //进行中
                        $da['showcode'] = "1";
                    }
                    if ($time < $v["starttime"]) {
                        //未开始
                        $da['showcode'] = "2";
                    }
                    $da["startm"] = $this->month[date("n", $v["starttime"]) - 1];
                    $da["startr"] = date("d", $v["starttime"]);
                    $da["entime"] = date("Y年m月d日", $v["endtime"]);
                    $da["peoples"] = $total;
                }else{
                    $da["startm"] = $this->month[date("n", $v["createtime"]) - 1];
                    $da["startr"] = date("d", $v["createtime"]);
                    $da["entime"]  =  date("Y.m.d", $v["createtime"]);
                }
                $da["project_name"] = empty($userinfo["project_name"]) ? "鑫苑物业" : $userinfo["project_name"];
                $data[] = $da;
            }
            $this->success("活动列表", imgUrl($data, 'picimage'));
        } else {
            $this->error("暂无活动！");
        }

    }

    /**
     * 调查问卷
     */
    public function getQuestionsList()
    {

        $page = max(1, intval($this->request->param('page')));
        $mobile = $this->auth->mobile;
        $uid = $this->auth->id;
        $time = time();
        if (empty($mobile)) {
            $this->error('您未绑定手机号！', '');
        }
        $userinfo = (new User())->doesUserInfo($uid);
        //切换小区
        if ($userinfo["pk_project"]) {
            $pk_project = $userinfo["pk_project"];
        } else {
            $lemember = (!empty($mobile)) ? $this->auth->mc_lecheck(array("phone" => $mobile)) : '';
            $pk_project = (!empty($lemember)) ? $lemember["pk_project"] : '';
        }
        $where = "";
        if ($pk_project) {
            $where .= "find_in_set('$pk_project',q2.lecommunity) AND ";
        } else {
            $where .= "q1.status=1 AND ";
        }
        $list = db('question')->alias('q1')
            ->field("q1.id,q1.title,q1.desc,q1.image,q1.status,q1.starttime,q1.endtime")
            ->join("question_region q2", "q2.aid=q1.id")
            ->where($where . "q1.switch=1 AND q1.starttime<{$time} AND {$time}<q1.endtime")
            ->order("q1.weigh", "DESC")->page($page, 10)->select();
        if ($list) {
            foreach ($list as $v) {
                $da["id"] = $v['id'];
                $da["title"] = $v['title'];
                $da["desc"] = $v['desc'];
                $da["image"] = $v['image'];
                if ($v["starttime"] < $time && $time < $v["endtime"]) {//进行中
                    $da['showcode'] = "1";
                }
                if ($time < $v["starttime"]) {//未开始
                    $da['showcode'] = "2";
                }
                //是否参与
                $showbutton = Db("question_user")->field("id")->where("uid={$uid} AND  voteid={$v['id']}")->find();
                $da["showbutton"] = empty($showbutton) ? 0 : 1;
                $da["endtime"] = date("Y年m月d日", $v["endtime"]);
                $data[] = $da;
            }
            $this->success("问卷列表请求成功", imgUrl($data, 'image'));
        } else {
            $this->error("暂无数据！", "");
        }

    }

    /**
     * 调查问卷详情
     */
    public function getQuestionInfo()
    {
        $id = $this->request->param("id");
        $uid = $this->auth->id;
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('手机号为空完善信息！');
        }
        $param["phone"] = $mobile;
        $authos = $this->auth->mc_lecheck($param);
        if (empty($authos)) {
            $this->error('您未是认证业主无权访问！');
        }
        $time = time();
        $activity = Db("question")->field("id,title,desc,infoimage")->where("id={$id}")->select();
        $question = Db("question_question")->field("id,title,remark,type")->where("voteid={$id}")->order("sort ASC")->select();
//        $showbutton = Db("question_user")->field("id")->where("uid={$uid} AND  voteid={$id}")->find();
        $showbutton = Db("question_user")->field("id")->where(['uid' => $uid, 'voteid' => $id])->find();

//        var_dump($showbutton);exit;

        if ($question) {
            foreach ($question as $k => $v) {
                $answer = Db("question_options")->field("id,questionid,title,remark")->where("questionid={$v['id']}")->select();
                if ($v["type"] !== 3) {
                    foreach ($answer as $kk => $vv) {
                        $optionid = Db("question_user_answer")->where(['userid' => $uid, 'optionsid' => $vv['id']])->find();
                        $answer[$kk]["checked"] = 0;
                        if ($optionid) {
                            $answer[$kk]["checked"] = 1;
                        }
                    }
                }

                if ($v["type"] == 3) {//填空题
                    $content = Db("question_user_answer")->where(['userid' => $uid, 'optionsid' => $vv['id'], 'type' => 3])->find();
                    $question[$k]["content"] = $content["content"];
                } else {
                    $question[$k]["answer"] = $answer;
                }

            }
            $data["vote"] = imgUrl($activity, "infoimage")["0"];
            $data["option"] = $question;

            if ($showbutton) {
                $data['showbutton'] = 0;
            } else {
                $data['showbutton'] = 1;
            }
//            $data["showbutton"] = empty($showbutton) ? 0 : 1;

            $this->success("问卷详情数据请求成功", $data);
        } else {
            $this->success("问卷暂无数据!", "");
        }
    }

    /**
     * 公告
     */
    public function getNoticeList()
    {

        $page = max(1, intval($this->request->param('page')));
        $mobile = $this->auth->mobile;
        $uid = $this->auth->id;
        $time = time();
        if (empty($mobile)) {
            $this->error('您未绑定手机号！', '');
        }
        $userinfo = (new User())->doesUserInfo($uid);
        //切换小区
        if ($userinfo["pk_project"]) {
            $pk_project = $userinfo["pk_project"];
        } else {
            $lemember = (!empty($mobile)) ? $this->auth->mc_lecheck(array("phone" => $mobile)) : '';
            $pk_project = (!empty($lemember)) ? $lemember["pk_project"] : '';
        }
        $where = "";
        if ($pk_project) {
            $where .= "find_in_set('$pk_project',n2.lecommunity) AND ";
        } else {
            $where .= "n1.status=1 AND ";
        }

        $list = db('notice')->alias('n1')
            ->field("n1.id,n1.title,n1.desc,n1.createtime,n1.labeldata,n1.color")
            ->join("notice_region n2", "n2.aid=n1.id")
            ->where($where . "n1.show=1 AND n1.starttime<{$time} AND {$time}<n1.endtime")
            ->order("n1.weigh", "DESC")->page($page, 10)->select();
        if ($list) {
            foreach ($list as &$v) {
                $v["createtime"] = date("m月d日", $v["createtime"]);
                $data[] = $v;
            }
            $this->success("公告列表请求成功", $data);
        } else {
            $this->error("暂无数据！", "");
        }
    }

    /**
     * 公告详情
     */
    public function getNoticeInfo()
    {
        $id = $this->request->param("id");
        $uid = $this->auth->id;
        if (empty($id)) {
            $this->error("请求参数错误！", "");
        }
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('手机号为空完善信息！');
        }
        $param["phone"] = $mobile;
        $authos = $this->auth->mc_lecheck($param);
        if (empty($authos)) {
            $this->error('您未是认证业主无权访问！');
        }
        $data = db('notice')->field("id,title,desc,createtime,content")->where('id', $id)->find();
        if ($data) {
            $data["content"] = str_replace("&nbsp;","&ensp;",$data["content"]);
            $data["content"] = str_replace("<img src=\"/uploads/", "<img src=\"".request()->domain()."/uploads/", $data["content"]);
            $data["createtime"] = date("Y-m-d H:i", $data["createtime"]);
            $this->success("公告详情数据请求成功", $data);
        } else {
            $this->success("公告暂无数据!", "");
        }
    }

}