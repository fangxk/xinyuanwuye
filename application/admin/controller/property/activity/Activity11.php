<?php

namespace app\admin\controller\property\activity;

use app\admin\controller\LeSoft;
use app\common\controller\Backend;
use think\Config;
use think\Db;

/**
 * 小区活动管理
 *
 * @icon fa fa-circle-o
 */
class Activity extends Backend
{
    
    /**
     * Activity模型对象
     * @var \app\admin\model\Property\Activity\Activity
     */
    protected $model = null;
    protected $searchFields = 'title';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Property\Activity\Activity;
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     *活动首页
     */
    public function index(){

        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order("istop,createtime","DESC,DESC")
                ->count();
            $list = $this->model
                ->where($where)
                ->where("type='0'")
                ->order("istop,createtime","DESC,DESC")
                ->limit($offset, $limit)
                ->select();
            $list = collection($list)->each(function($item){
                if($item["num"]<0){
                    $item["num"] = "不限";
                }else{
                    $item["num"] = $item["num"]."人";
                }
                if($item["istop"]){
                    $item["istop"] = "置顶";
                }else{
                    $item["istop"] = "普通";
                }
                return $item;
            })->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     *新增活动
     */
    public function add(){

        if ($this->request->isPost()) {
            $params    = $this->request->post("row/a");
            $params["starttime"] = strtotime($params["starttime"]);
            $params["endtime"]   = strtotime($params["endtime"]);
            $params["signtime"]  = strtotime($params["signtime"]);

            $params['createtime'] = time();
            if ($params) {
                if (empty($params['status'])){//绑定指定小区
                    $reginonids = $this->request->post("reginonid/a");
                    if($reginonids){
                        foreach ($reginonids as $v){//过滤空值
                            if($v){
                                $reginonid[] = $v;
                            }
                        }
                    }
                    if(empty($reginonid)){
                        $this->error(__('绑定小区不能为空！'));
                    }
                    //地址拼接
                    $citys = explode('/',$params['city']);
                    $params['provice']  = isset($citys['0'])?$citys['0']:'';
                    $params['city']     = isset($citys['1'])?$citys['1']:'';
                    //更改城市筛选是否一样
                    $checkcity = $this->checkarea($params['city']);
                    if(empty($checkcity)){
                        $this->error(__('所选城市暂无小区！'));
                    }
                    foreach ($reginonid as $v){
                        if(!in_array($v,$checkcity)){
                            $this->error(__('所选小区不匹配！'));
                        }
                    }
                } else {//绑定所有小区
                    $lesoft = new LeSoft();
                    $regions = json_decode($lesoft->GetSoftUrl(config::get('leapi.Lp')),true)["Result"];
                    foreach ($regions as $k => $v) {
                        $reginonid[] = $v['pk'];
                    }
                }
                //添加数据和绑定小区数据
                $voteid = $this->model->allowField(true)->insertGetId($params);
                $regiondata =array('activityid'   => $voteid, 'lecommunity' => implode(",",$reginonid));
                $result = Db::name('activity_region')->insertGetId($regiondata);

                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('插入失败！'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return view();
    }

    /**
     *修改活动
     */
    public function edit($ids=null){
        $row = $this->model->get($ids);
        $id  = $row['id'];
        $lesoft = new LeSoft();

        if(empty($id)){
            $this->error(__("暂无资源！"));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('您暂无权限访问'));
            }
        }
        //查询所有问卷调查
        $rows = db('activity')->where('id', $row['id'])->find();
        $regs = Db::name("activity_region")->where('activityid',$rows['id'])->find();
        //默认小区
        $regionids = '';
        if (empty($rows['status'])){//添加页面默认选中乐软小区
            $regionids = json_encode(explode(',',$regs["lecommunity"]));
        }

        if ($this->request->isPost()) {//提交
            $params = $this->request->post("row/a");
            $params["starttime"] = strtotime($params["starttime"]);
            $params["endtime"]   = strtotime($params["endtime"]);
            $params["signtime"]  = strtotime($params["signtime"]);
            if ($params) {
                $params = $this->preExcludeFields($params);
                $params['createtime'] = time();

                //是所有小区清除初始化地区名称
                if(empty($params['status'])){//绑定指定小区
                    $regiond = Db("legroup")->field("pk_project,recchn")->whereIn("recguid",$params["reginonid"])->select();
                }else{//绑定所有小区
                    $regiond = Db("legroup")->field("pk_project,recchn")->select();
                }
                $pk_project = '';
                foreach ($regiond as $v){
                    if($v["pk_project"]){
                        $pk_project.=$v["pk_project"].',';
                    }
                }

                $pk_project = substr($pk_project,'0','-1');
                unset($params["reginonid"]);
                $noteid = db('activity')->where('id',$id)->update($params);
                //查看是否有绑定记录
                if($regs){
                    $result = db('activity_region')->where('activityid',$id)->setField('lecommunity',$pk_project);
                }else{
                    $result = db::name('activity_region')->insert(array("activityid"=>$id,"lecommunity"=>$pk_project));
                }

                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        //省市级联组合展示
        $provice  = empty($rows['provice'])?'':$rows['provice'].'/';
        $city     = empty($rows['city'])?'':$rows['city'].'/';
        $rows['company_address'] = $provice.$city;

        $this->view->assign('regionids', $regionids);
        $this->view->assign('row', $rows);
        return view();
    }

    /**
     * 删除报名活动
     */
    public function del($ids = "")
    {
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            $count = 0;
            foreach ($list as $k => $v) {
                $count += $v->delete();
                Db::name('activity_region')->where('activityid',$v->id)->delete();
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 报名管理
     */
    public function areport($ids = ""){
        if ($this->request->post("op")=='delete') {
            $uids = $this->request->post("uids/a");
            if($uids){
                $uids = implode(",", $uids);
            }
            db('activity_report')->where("id","in",$uids)->delete();
            $this->success(__('删除成功~', ''));
        }
        $psize = 10;
        $condition["t1.aid"] = $ids;
        $keyword = $this->request->get("keyword");
        if($keyword){
            $condition["t1.relaname|t1.nickname"] = array("=",$keyword);
        }
        $list =  Db::name('activity_report')->alias("t1")
            ->field("t1.*,t2.title")
            ->join("activity t2","t1.aid=t2.id")
            ->where($condition)
            ->order("t1.createtime desc")
            ->paginate($psize,false,["query"=>['ids'=>$ids]]);
        $pager = $list->render();
        $this->view->assign(array("list"=>$list->items(),"pager"=>$pager,"keyword"=>$keyword,"ids"=>$ids));

        return view("property/activity/activity/order");
    }


}
