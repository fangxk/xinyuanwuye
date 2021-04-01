<?php


namespace app\api\controller;


use app\api\model\User;
use app\common\controller\Api;
use app\common\library\LeSoft;

class Notice extends Api
{
    protected $noNeedLogin = " ";
    protected $noNeedRight = '*';
    protected $searchFields = 'title';


    /**
     * 获取公告列表
     */
    public function getNoticeList()
    {
        //判断是否是认证用户
        $mobile = $this->auth->mobile;
        $uid = $this->auth->id;

        if(empty($mobile)){
            $this->error('未认证先认证！！！','');
        }
        $userinfo = (new User())->doesUserInfo($uid);
        //切换小区
        if($userinfo["pk_project"]){
            $pk_project = $userinfo["pk_project"];
        }else{
            $lemember = (!empty($mobile))?$this->auth->mc_lecheck(array("phone"=>$mobile)):'';
            $pk_project = (!empty($lemember))?$lemember["pk_project"]:'';
        }
        $time = time();
        $advlist = db('notice')->alias("n")
            ->field("n.id,n.title,n.content,n.status,nr.lecommunity as lehouse")
            ->join("notice_region nr","n.id=nr.aid")
            ->where('n.show=1 AND n.starttime<'.$time.' AND n.endtime>'.$time.'')
            ->order("n.weigh DESC")
            ->select();
        //根据认证展示所属区域公告
        if ($this->auth->mobile) {
            if ($advlist) {
                foreach ($advlist as $v) {
                    if (in_array($pk_project, json_decode($v["lehouse"])) || $v["status"] == 1) {//默认公告或指定小区公告
                        $data[] = $v;
                    }
                }
            }
        }
        $this->success("公告获取成功",$data);
    }


}