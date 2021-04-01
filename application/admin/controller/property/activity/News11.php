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
class News extends Backend
{
    
    /**
     * News模型对象
     * @var \app\admin\model\Property\Activity\News
     */
    protected $model = null;
    protected $searchFields = 'title';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Property\Activity\News;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("votetypeList", $this->model->getVotetypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     *文章首页
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
            //list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order("istop,createtime","DESC,DESC")
                ->count();
            $list = $this->model
                ->where($where)
                ->where("type='2'")
                ->order("istop,createtime","DESC,DESC")
                ->limit($offset, $limit)
                ->select();
            $list = collection($list)->each(function ($item){
                if($item["istop"]){
                    $item["istop"] = "置顶";
                }else{
                    $item["istop"] = "普通";
                }
                $item["desc"] =substr($item["desc"],0,100);
                return $item;
            })->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     *新增文章
     */
    public function add(){
        if ($this->request->isPost()) {
            $params    = $this->request->post("row/a");
            $params["starttime"] = strtotime("1977");
            $params["endtime"]   = strtotime("3000");
            $params['createtime'] = time();
            $params['type'] = 2;
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
        //获取乐软组织结构
        $res = legroup();
        $this->assign("nodeList", $res);
        return view();
    }

    /**
     *修改文章
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
        $rows["createtime"] = date("Y-m-d H:i",$rows["createtime"]);
        $regs = Db::name("activity_region")->where('activityid',$rows['id'])->find();
        //默认小区
        $regionids = '';
        if (empty($rows['status'])){//添加页面默认选中乐软小区
            $regionids = json_encode(explode(',',$regs["lecommunity"]));
        }
        if ($this->request->isPost()) {//提交
            $params = $this->request->post("row/a");
            $params["starttime"] = strtotime("1977");
            $params["endtime"]   = strtotime("3000");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $params['createtime'] = time();
                //是所有小区清除初始化地区名称
                if(empty($params['status'])){//绑定指定小区
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
                    $address  = explode('/',$params["city"]);
                    $params['provice']  = isset($address['0'])?$address['0']:'';
                    $params['city']     = isset($address['1'])?$address['1']:'';
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
                    $regiondata = implode(",",$reginonid);
                }else{//绑定所有小区
                    $params['provice']  = '';
                    $params['city']     = '';
                    //绑定所有小区
                    $regions = json_decode($lesoft->GetSoftUrl(config::get('leapi.Lp')),true)["Result"];
                    foreach ($regions as $k => $v) {
                        $dat[] = $v['pk'];
                    }
                    $regiondata = implode(",",$dat);
                }
                $noteid = db('activity')->where('id',$id)->update($params);

                //查看是否有绑定记录
                if($regs){
                    $result = db('activity_region')->where('activityid',$id)->setField('lecommunity',$regiondata);
                }else{
                    $result = db::name('activity_region')->insert(array("activityid"=>$id,"lecommunity"=>$regiondata));
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
}
