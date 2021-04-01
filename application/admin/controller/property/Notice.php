<?php

namespace app\admin\controller\property;

use app\common\controller\Backend;
use fast\Tree;
use think\Config;
use think\Db;
use app\admin\controller\LeSoft;
/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Notice extends Backend
{
    
    /**
     * Notice模型对象
     * @var \app\admin\model\Property\Notice
     */
    protected $model = null;
    protected $searchFields = 'title';

    public function _initialize()
    {
        /*$nodeList = \app\admin\model\UserRule::getTreeList();
        print_r($nodeList);die;*/
        parent::_initialize();
        $this->model = new \app\admin\model\Property\Notice;

        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    public function add(){
        if ($this->request->isPost()) {
            $params    = $this->request->post("row/a");
            $params['createtime'] = time();
            if ($params) {
                $params["starttime"]  = strtotime($params["starttime"]);
                $params["endtime"]    = strtotime($params["endtime"]);
                if (empty($params['status'])){//绑定指定小区
                    $regiond = Db("legroup")->field("pk_project,recchn")->whereIn("recguid",$params["reginonid"])->select();
                } else {//绑定所有小区
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
                //添加数据和绑定小区数据
                $voteid = $this->model->allowField(true)->insertGetId($params);
                $regiondata =array('aid'   => $voteid, 'lecommunity' => $pk_project);
                $result = Db::name('notice_region')->insertGetId($regiondata);
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

    //修改
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
        $rows = db('notice')->where('id', $row['id'])->find();
        $rows["starttime"] = date("Y-m-d H:i:s",$rows["starttime"]);
        $rows["endtime"]   = date("Y-m-d H:i:s",$rows["endtime"]);
        $regs = Db::name("notice_region")->where('aid',$rows['id'])->find();
        $regionids = $regs["lecommunity"];
        $group = legroup();//组织结构
        //默认小区
        if (empty($rows['status'])){//添加页面默认选中乐软小区
            foreach ($group as $k=>$v) {
                $group[$k]["state"] = ["selected"=>false];
                if($v["pk_project"] && strpos($regionids,$v["pk_project"])!==false && $v['pk_project']!="001C89772B00B3A2942B"){
                    $group[$k]["state"] = ["selected"=>true];
                }else{
                    $group[$k]["state"] = ["selected"=>false];
                }
            }
        }
        if ($this->request->isPost()) {//提交
            $params = $this->request->post("row/a");
            $params["starttime"] = strtotime($params["starttime"]);
            $params["endtime"]   = strtotime($params["endtime"]);
            if ($params) {
                $params = $this->preExcludeFields($params);
                //是所有小区清除初始化地区名称
                if(empty($params['status'])){//绑定指定小区
                    if(empty($params["reginonid"])){
                        $this->error(__("指定小区必须勾选！！"));
                    }
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
                $noteid = db('notice')->where('id',$id)->update($params);

                //查看是否有绑定记录
                if($regs){
                    $result = db('notice_region')->where('aid',$id)->setField('lecommunity',$pk_project);
                }else{
                    $result = db::name('notice_region')->insert(array("aid"=>$id,"lecommunity"=>$pk_project));
                }

                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->assign("nodeList", $group);
        $this->view->assign('row', $rows);
        return view();
    }

    /**
     * 删除
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
                Db::name('notice_region')->where('aid',$v->id)->delete();
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

}
