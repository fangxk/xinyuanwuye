<?php

namespace app\admin\controller\Property;

use app\common\controller\Backend;
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

    public function _initialize()
    {
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
            $reginonid = $this->request->post("reginonid/a");
            $params['createtime'] = time();
            $citys = explode('/',$params['city']);
            $params['provice']  = isset($citys['0'])?$citys['0']:'';
            $params['city']     = isset($citys['1'])?$citys['1']:'';
            $params['district'] = isset($citys['2'])?$citys['2']:'';

            if(empty($params['status']) && empty($reginonid)){
                $this->error(__('绑定小区不能为空！'));
            }
            if ($params) {
                $params = $this->preExcludeFields($params);
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                //是否采用模型验证
                if ($this->modelValidate) {
                    $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                    $this->model->validateFailException(true)->validate($validate);
                }
                $noteid = $this->model->allowField(true)->insertGetId($params);

                if ($params['status'] == 1) {//选择了所有小区
                    $lesoft = new LeSoft();
                    $regions = json_decode($lesoft->GetSoftUrl(config::get('leapi.Lp')),true)["Result"];
                    foreach ($regions as $k => $v) {
                        $dat[] = $v['pk'];
                    }
                    $regiondata = array('aid'   => $noteid,"lecommunity"=>json_encode($dat));
                } else {//选择了指定小区
                    $regiondata =array('aid'   => $noteid, 'lecommunity' => json_encode($reginonid));
                }
                $result = Db::name('notice_region')->insertGetId($regiondata);

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
        //查询所有公告
        $rows = db('notice')->where('id', $row['id'])->find();
        $regs = Db::name("notice_region")->where('aid',$rows['id'])->find();
        //默认小区
        $regionids = '';
        if (empty($rows['status'])){//添加页面默认选中乐软小区
            $regionids = $regs["lecommunity"];
        }
        if ($this->request->isPost()) {
                $params = $this->request->post("row/a");
                $reginonid = $this->request->post("reginonid/a");
                if(empty($params['status']) && empty($reginonid)){
                    $this->error(__('绑定小区不能为空！'));
                }
                if ($params) {
                    $params = $this->preExcludeFields($params);
                    //是所有小区清除初始化地区名称
                    if(empty($params['status'])){
                        //地址拼接
                        $address  = explode('/',$params["city"]);
                        $params['provice']  = isset($address['0'])?$address['0']:'';
                        $params['city']     = isset($address['1'])?$address['1']:'';
                        $params['district'] = isset($address['2'])?$address['2']:'';
                    }else{
                        $params['provice']  = '';
                        $params['city']     = '';
                        $params['district'] = '';
                    }
                    $noteid = db('notice')->where('id',$id)->update($params);
                    //更新绑定小区
                    if ($params['status'] == 1) {//选择了所有小区
                        $regions = json_decode($lesoft->GetSoftUrl(config::get('leapi.Lp')),true)["Result"];
                        foreach ($regions as $k => $v) {
                            $dat[] = $v['pk'];
                        }
                        $regiondata = json_encode($dat);
                    } else {//选择了指定小区
                        $regiondata = json_encode($reginonid);
                    }
                    //查看是否有绑定记录
                    if($regs){
                        $result = db('notice_region')->where('aid',$id)->setField('lecommunity',$regiondata);
                    }else{
                        $result = db::name('notice_region')->insert(array("aid"=>$id,"lecommunity"=>$regiondata));
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
