<?php
/**
 * Submi.php
 * Create By Company JUN HE
 * User XF
 * @date 2020-10-23 13:54
 */

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\controller\LeSoft;
use app\api\model\User;

class Submi extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    //提交问卷调查
    public function SubmitQuestion(){
        $voteid   = $this->request->param("id");
        $mobile   = $this->auth->mobile;
        $uid      = $this->auth->id;
        $userinfo = (new User())->doesUserInfo($uid);
        if(empty($voteid)){
            $this->error("参数请求错误！！");
        }
        if(empty($mobile)){
            $this->error('您未绑定手机号！','');
        }
        if(empty($userinfo["pk_project"])){
            $this->error('您未是认证业主！！','');
        }
        $dataquestion = Db("question")->where("id={$voteid}")->find();

        if(!isset($dataquestion)){
            $this->error('暂无结果！！','');
        }
        $radio    = empty($this->request->param("radio"))?'':$this->request->param("radio");
        $checkbox = empty($this->request->param("checkbox"))?array():$this->request->param("checkbox");
        $content  = empty($this->request->param("content"))?array():$this->request->param("content");
        $newdata  = array_merge($radio,$checkbox,$content);
        print_r($radio);die;
        echo substr($radio,1,-1);die;


        $total    = Db("question_question")->where("voteid={$voteid}")->count();
        //是否有用户
        $userwhere = '';
        if($userinfo["pk_project"]){
            $userwhere = "pk_project='{$userinfo["pk_project"]}' AND ";
        }
        $userreport= Db("question_user")->where($userwhere."voteid={$voteid} AND uid={$uid}")->find();

        if($userreport){
            $this->error("问卷已回答!");
        }
        if(count($newdata)<$total){
            $this->error("所有题目都为必填项!");
        }
        if($content){
            foreach ($content as $v){
                $contents = explode("-",$v);
                if(empty($contents['2'])){
                    $this->error("所有题目都为必填项!");
                }
            }
        }

        for($i=0;$i<count($newdata);$i++){
            $new = explode("-",$newdata[$i]);
            $data["questionid"]= $new['1'];
            $data["userid"] = $uid;
            $data["type"] = $new['0'];
            $data["createtime"]= time();
            $data["content"]   = '';
            $data["optionsid"] = ' ';
            $data["voteid"] = $voteid;
            if($new['0']==3){
                $data["content"]   = $new['2'];
            }else{
                $data["optionsid"] = $new['2'];
            }
            $answers[] = $data;
        }

        $voteuser = array("uid"=>$uid,"voteid"=>$voteid,"avatar"=>$userinfo["avatar"],"relaname"=>$userinfo["relaname"],"mobile"=>$userinfo["mobile"],"nickname"=>$userinfo["nickname"],"house_name"=>$userinfo['house_name'],"pk_client"=>$userinfo['pk_client'],"pk_house"=>$userinfo['pk_house'],"pk_project"=>$userinfo['pk_project'],"createtime"=>time());
        $answersuid = Db("question_user")->insertGetId($voteuser);
        foreach ($answers as $v){
            $v["userid"] = $answersuid;
            Db("question_user_answer")->insert($v);
        }
        $this->success("问卷提交成功");
    }


    //立即报名
    public function SubmitActivity(){
        $id   = $this->request->param("id");
        $mobile = $this->auth->mobile;
        $uid  = $this->auth->id;
        $time = time();
        if(empty($id)){
            $this->error('参数请求错误！！！','');
        }
        $userinfo = (new User())->doesUserInfo($uid);
        if(empty($mobile)){
            $this->error('您未绑定手机号！','');
        }
        if(empty($userinfo["pk_project"])){
            $this->error('您未是认证业主！！','');
        }
        $active = Db("activity")->alias("a")->field("a.*,ar.lecommunity")->join("activity_region ar","ar.activityid=a.id")->where("a.id={$id}")->find();
        if(!isset($active)){
            $this->error('暂无结果！！','');
        }
        if($active["type"]){
            $this->error('活动类型不是报名活动！！','');
        }
        if(!in_array($userinfo["pk_project"],explode(",",$active["lecommunity"]))){
            $this->error('您不在指定小区范围内！！','');
        }
        //是否有用户
        $userwhere = '';

        if($userinfo["pk_project"]){
            $userwhere = "pk_project='{$userinfo["pk_project"]}' AND ";
        }

        $total  = Db("activity_report")->where($userwhere."aid={$id}")->count();//总人数
        if($active){
            $userreport = Db("activity_report")->where($userwhere."aid={$id} AND uid={$uid}")->find();//是否已报名
            if($userreport){
                $this->error("您已报名！！");
            }
            if($active['endtime']<$time){
                $this->error("报名活动已结束！");
            }
            if($active['signtime']<$time){
                $this->error("报名截止时间已到！");
            }
            if($active['num']>0 && $active['num']<$total+1){
                $this->error("超出最多人数限制！");
            }
            //验证用户是否完善信息
            $data = array("uid"=>$uid,"nickname"=>$userinfo['nickname'],"house_name"=>$userinfo['house_name'],"pk_client"=>$userinfo['pk_client'],"pk_house"=>$userinfo['pk_house'],"pk_project"=>$userinfo['pk_project'],"relaname"=>$userinfo['relaname'],"mobile"=>$userinfo['mobile'],"aid" =>$id,"avatar"=>$userinfo['avatar'],"address"=>$userinfo['house_name'],"createtime"=>time());
            $res = Db("activity_report")->insert($data);
            if($res){
                $this->success("报名成功！！");
            }
        }else{
            $this->error("暂无数据无法提交！");
        }
    }

    //立即投票
    public function SubmitVote(){
        $id       = $this->request->param("id");
        $optionid = $this->request->param("optionids");
        $mobile = $this->auth->mobile;
        $uid    = $this->auth->id;
        $time   = time();
        if(empty($id)){
            $this->error('参数请求错误！！！','');
        }
        if(empty($optionid)){
            $this->error("投票项ID不能为空！！");
        }
        $userinfo = (new User())->doesUserInfo($uid);
        if(empty($mobile)){
            $this->error('您未绑定手机号！','');
        }
        if(empty($userinfo["pk_project"])){
            $this->error('您未是认证业主！！','');
        }

        $vote = Db("activity")->alias("a")->field("a.*,ar.lecommunity")->join("activity_region ar","ar.activityid=a.id")->where("a.id={$id}")->find();
        if(empty($vote["type"])){
            $this->error('活动类型不是投票活动！！','');
        }
        if(!isset($vote)){
            $this->error('暂无结果！！','');
        }
        if(!in_array($userinfo["pk_project"],explode(",",$vote["lecommunity"]))){
            $this->error('您不在查看范围内！！','');
        }
        //是否有用户
        $userwhere = '';
        if($userinfo["pk_project"]){
            $userwhere = "pk_project='{$userinfo["pk_project"]}' AND ";
        }
        //根据乐软的项目Pk 查询报名总人数
        $total= Db("activity_report")->where($userwhere."aid={$id}")->count();
        if($vote){
            $userreport = Db("activity_report")->where($userwhere."aid={$id} AND uid={$uid}")->find();
            if($userreport){
                $this->error("您已投票！！");
            }
            if($vote['endtime']<$time){
                $this->error("投票活动已结束！");
            }
            if($vote['signtime']<$time){
                $this->error("投票时间已截止！");
            }
            if($vote['num']>0 && $vote['num']<$total+1){
                $this->error("超出投票最多人数限制！");
            }
            $data = array("uid"=>$uid,"option_ids"=>$optionid,"nickname"=>$userinfo['nickname'],"relaname"=>$userinfo['relaname'],"mobile"=>$userinfo['mobile'],"aid" =>$id,"avatar"=>$userinfo['avatar'],"address"=>$userinfo['house_name'],"createtime"=>time());
            $data["pk_house"]  = $userinfo['pk_house'];
            $data["house_name"]= $userinfo['house_name'];
            $data["pk_project"]= $userinfo['pk_project'];
            $data["pk_client"] = $userinfo['pk_client'];
            $res = Db("activity_report")->insert($data);
            if($res){
                $this->success("投票成功！！");
            }
        }else{
            $this->error("暂无数据无法提交！");
        }
    }

    

}