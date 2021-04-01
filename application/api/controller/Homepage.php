<?php


namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\LeSoft;
use app\api\model\User;

class Homepage extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';


    /**
     * 获取首页菜单
     */
    public function menu()
    {
        $top = db('link')->where('showswitch', 1)->where('topswitch', 1)->order('weigh desc')->select();
        $menu = db('link')->where('showswitch', 1)->where('topswitch', 0)->order('weigh desc')->select();

        $this->success('获取成功', [
            'top'  => imgUrl($top, 'image'),
            'menu' => imgUrl($menu, 'image')
        ]);
    }

    /**
     * 添加点击量
     */
    public function addlink()
    {
        $id     = $this->request->param("id");
        $mobile = $this->auth->mobile;
        $uid    = $this->auth->id;

        if(empty($id)){
            $this->error("参数请求错误！");
        }
        $userinfo = (new User())->doesUserInfo($uid);
        if(empty($mobile)){
            $this->error('您未绑定手机号！','');
        }
        if(empty($userinfo["pk_project"])){
            $this->error('您未是认证业主！！','');
        }
        /*$link = db('link')->field("id,title,link,numbers")->where("id={$id}")->find();
        if(empty($link)){
            $this->error("暂无数据！！！");
        }*/
        Db("link")->where("id={$id}")->setInc("numbers");
        $this->success("数据点击量增加成功");
    }


    /**
     * 首页轮播图
     */
    public function banner()
    {
        $data = db('banner')->where('showswitch', 1)->order('weigh desc')->select();
        foreach ($data as &$v){
            $v["type"]=2;
        }
        $this->success('获取成功', imgUrl($data, 'image'));
    }


    /**
     * 获取公告列表
     */
    public function getNoticeList()
    {
        //判断是否是认证用户
        $mobile = $this->auth->mobile;
        $uid    = $this->auth->id;
        $userinfo = (new User())->doesUserInfo($uid);
        if($userinfo["pk_project"]){
            $pk_project = $userinfo["pk_project"];
        }else{
            $lemember = (!empty($mobile))?$this->auth->mc_lecheck(array("phone"=>$mobile)):'';
            $pk_project = (!empty($lemember))?$lemember["pk_project"]:'';
        }

        $where ="";
        if($pk_project){
            $where.="find_in_set('$pk_project',nr.lecommunity) AND ";
        }else{
            $where.="n.status=1 AND ";
        }
        $time = time();
        $advlist = db('notice')->alias("n")
            ->field("n.id,n.desc,n.title,n.content,n.status,n.labeldata,n.color")
            ->join("notice_region nr","n.id=nr.aid")
            ->where($where."n.show=1 AND n.starttime<{$time} AND n.endtime>{$time}")
            ->order("n.weigh DESC")
            ->select();
        $this->success("公告获取成功",$advlist);
    }


    /**
     * 获取活动列表
     */
    public function getActivityList()
    {
        //判断是否是认证用户
        $mobile = $this->auth->mobile;
        $uid    = $this->auth->id;
        $userinfo = (new User())->doesUserInfo($uid);
        if($userinfo["pk_project"]){
            $pk_project = $userinfo["pk_project"];
        }else{
            $lemember = (!empty($mobile))?$this->auth->mc_lecheck(array("phone"=>$mobile)):'';
            $pk_project = (!empty($lemember))?$lemember["pk_project"]:'';
        }

        $time = time();
        $where ="";
        if($pk_project){
            $where.="find_in_set('$pk_project',a.lecommunity) AND ";
        }else{
            $where.="an.status=1 AND ";
        }

        $activelist = db('activity')
            ->alias("an")->join("activity_region a","an.id=a.activityid")
            ->field("an.id,an.title,an.type,an.desc,an.picimage,an.starttime,an.signtime,an.endtime,an.createtime")
            ->where($where."an.statusswitch=1 AND an.istop=1 AND an.starttime<{$time} AND {$time}<an.endtime")
            ->order("an.createtime","DESC")->limit("10")->select();

       if($activelist){
           foreach ($activelist as $v) {
               $da['id']     = $v["id"];
               $da['title']  = $v["title"];
               $da['type']   = $v["type"];
               $da['desc']   = $v["desc"];
               $da['picimage'] = $v["picimage"];
               unset($da["avatar"]);
               unset($da["reports"]);
               unset($da["peoples"]);
               unset($da["entime"]);
               if($v["type"]!=2){
                   //活动总人数
                   $total = Db("activity_report")->where("aid={$v['id']}")->count();
                   //活动头像
                   $avatar = Db("activity_report")->field("id,avatar")->where("aid={$v['id']}")->limit("5")->select();
                   if ($avatar) {
                       foreach ($avatar as $vs) {
                           $da['avatar'][] = $vs['avatar'];
                       }
                   }
                   //是否参与
                   if($uid){
                       $reports = Db("activity_report")->where("aid={$v['id']} AND uid={$uid}")->find();
                   }else{
                       $reports["reports"] = '0';
                   }
                   $da['reports'] = empty($reports)?0:1;
                   $da["entime"]  =  date("Y.m.d", $v["endtime"]);
                   $da["peoples"] = $total;
               }else{
                   $da["entime"]  =  date("Y.m.d", $v["createtime"]);
               }
               $da["project_name"] = empty($userinfo["project_name"])?"鑫苑物业":$userinfo["project_name"];
               $data[] = $da;
           }
       }
       $this->success('获取成功', isset($data)?imgUrl($data, 'picimage'):[]);
    }
    
}