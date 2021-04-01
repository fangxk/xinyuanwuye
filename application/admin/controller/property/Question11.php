<?php

namespace app\admin\controller\property;

use app\admin\controller\LeSoft;
use app\admin\controller\Model_execl;
use app\api\model\CompanyInfo;
use app\api\model\LawyerInfo;
use app\common\controller\Backend;
use think\Config;
use think\Db;
use think\exception\PDOException;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Question extends Backend
{
    
    /**
     * Question模型对象
     * @var \app\admin\model\Property\Question
     */
    protected $model = null;
    protected $searchFields = 'title';
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Property\Question;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("switchList", $this->model->getSwitchList());
    }

    /**
     *问卷首页
     */
    public function index(){
        //添加
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
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as &$row) {
                $row["starttime"] = date("Y-m-d H:i:s",$row["starttime"]);
                $row["endtime"]   = date("Y-m-d H:i:s",$row["endtime"]);
                $question = Db::name("Question_question")->where("voteid",$row['id'])->find();
                if($question){//是否添加有问题
                    $row['isadd'] = '1';
                }else{
                    $row['isadd'] = '0';
                }
                //是否有用户数据
                $useranswer = Db::name("Question_user")->where("voteid",$row['id'])->find();
                if($useranswer){//是否添加有问题
                    $row['isinit'] = '1';
                }else{
                    $row['isinit'] = '0';
                }
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     *修改问卷问题
     */
    public function editquestion($ids = ""){
        //添加显示数据
        if ($ids) {
            $item = Db::name('Question')->where("id",$ids)->field("title")->find();
            $list = Db::name('Question_question')->where("voteid",$ids)->order("sort","ASC")->select();
            $nums = count($list);
            foreach ($list as $k => $v) {
                $list[$k]['num'] = $k;
                $list[$k]['options'] = Db::name('Question_options')->where("questionid",$v['id'])->order("sort","ASC")->select();
            }
        }
        if($this->request->isPost()){
            $question = array_merge($this->request->post("timu/a"));
            $options  = $this->request->post("list/a");//投票项
            $lists    = $this->request->post("lists/a");
            if($question){
                foreach ($question as $k=>$v){
                    if($v["type"]==3){
                        $options[$k] = array();
                        $lists[$k]   = array();
                    }
                }
            }
            for ($i = 0; $i < count($question); $i++) {
                $num = $i + 1;
                $data = array(
                    'voteid'     => $ids,
                    'title'      => trim($question[$i]['title']),
                    'remark'     => trim($question[$i]['i_desc']),
                    'maxnum'     => trim($question[$i]['i_max']),
                    'type'       => intval($question[$i]['type']),
                    'sort'       => $num,
                    'createtime' => time()
                );

                if (isset($question[$i]['id'])&&$question[$i]['id']){
                    Db::name('question_question')->where("id",$question[$i]['id'])->update($data);
                    $quesid = $question[$i]['id'];
                }else {
                    $quesid = Db::name('question_question')->insertGetId($data);
                }

                if($question[$i]['type']!=3){//不是填空题才执行排序
                    if (!empty($options[$i])) {
                        for ($j = 0; $j < count($options[$i]); $j++) {
                            $optiondata = array(
                                'questionid' => intval($quesid),
                                'title'      => trim($options[$i][$j]),
                                'sort'       => $j,
                                'createtime' => time()
                            );
                            if (isset($lists[$i][$j])&&$lists[$i][$j]) {
                                Db::name('question_options')->where("id",$lists[$i][$j])->update($optiondata);
                            }
                            else {
                                Db::name('question_options')->insertGetId($optiondata);
                            }
                        }
                    }
                }
            }
           $this->success("数据更新成功",'admin/property/Question');
        }
        $this->assign(array('list'=>$list,'item'=>$item,'nums'=>$nums));
        return view();
    }

    /**
     *添加问卷问题
     */
    public function addquestion($ids = ""){
        if ($ids) {
            $item = Db::name('Question')->where("id",$ids)->field("title")->find();
        }else {
            $this->error("数据不存在！");
        }
        //$this->error('参数错误!!');
        if($this->request->isPost()){
            $question = $this->request->post("timu/a");
            $options  = $this->request->post("list/a");
            if($question && $options){
                //循环所有问题执行插入
                for ($i = 0; $i < count($question); $i++) {
                    $num  = $i + 1;
                    $data = array(
                        'voteid'     => $ids,
                        'title'      => trim($question[$i]['title']),
                        'remark'     => trim($question[$i]['i_desc']),
                        'maxnum'     => trim($question[$i]['i_max']),
                        'type'       => intval($question[$i]['type']),
                        'sort'       => $num,
                        'createtime' => time()
                    );
                    //添加问题
                    $quesid = Db::name('question_question')->insertGetId($data);
                    //添加答案
                    if (!empty($options[$i]) && isset($options[$i])) {
                        for ($j = 0; $j < count($options[$i]); $j++) {//循环答案
                            $optiondata = array(
                                'questionid'     => intval($quesid),
                                'title'      => trim($options[$i][$j]),
                                'sort'       => $j,
                                'createtime' => time()
                            );
                            Db::name('question_options')->insert($optiondata);
                        }
                    }
                }
                $this->success('添加成功',"admin/property/question");
            }else{
                $this->error('参数错误!!');
            }
        }
        $this->assign("nums",'0');
        $this->assign("item",$item);
        return view();
    }

    /**
     *统计问卷填写信息
     */
    public function suminfo($ids='',$explort=''){

        if ($ids) {
            $list = Db::name('Question_question')->field('id,title,type')->where("voteid",$ids)->order("sort","ASC")->select();
            $rows = array();
            foreach ($list as $k => $v) {
                $list[$k]['total'] =Db("question_user")->where("voteid",$ids)->count();

                if ($v['type'] == 1 || $v['type'] == 2) {
                    $list[$k]['options'] = Db::name('question_options')->where("questionid",$v['id'])->select();
                    $answers = array();
                    foreach ($list[$k]['options'] as $key => $val) {
                        if ($v['type'] == 1) {
                            //单选
                            $list[$k]['options'][$key]['num'] = Db("question_user_answer")->where(array("optionsid"=>$val['id'],"type"=>1))->count();
                        }
                        if ($v['type'] == 2) {
                            $list[$k]['options'][$key]['num'] = Db("question_user_answer")->where(array("optionsid"=>$val['id'],"type"=>2))->count();
                        }
                        $list[$k]['options'][$key]['scale'] = ($list[$k]['options'][$key]['num'] && $list[$k]['total']) ? round($list[$k]['options'][$key]['num'] / $list[$k]['total'], 2) * 100 : 0;
                    }
                }
                if ($v['type'] == 3) {
                    $list[$k]['tk_answers'] = Db::name('Question_user_answer')->where(array("optionsid"=>$v['id'],"type"=>3))->select();
                }
            }
            //导出数据
            if($explort){
                foreach ($list as $k=>$q){
                    if($q['type']!=3){
                        foreach ($q['options'] as $k=>$v){

                            $a["qtitle"] = $v['title'];
                            $a["title"]  = $q['title'];
                            $a["scale"] = empty($v["scale"])?"0":$v["scale"]."%";
                            if($q['type']==1){
                                $a["qtype"] = '单选';
                            }
                            if($q['type']==2){
                                $a["qtype"] = '多选';
                            }
                            $export[] = $a;
                        }
                    }else{
                        $num  = Db("question_user_answer")->where(array("questionid"=>$q['id'],"type"=>3))->count();
                        $scale        = ($num && $list[$k]['total']) ? round($num / $list[$k]['total'], 2) * 100 : 0;
                        $das['scale'] = $scale."%";
                        $das["qtype"] = '填空';
                        $das["qtitle"] = $q['title'];
                        $das["title"]  = $q['title'];
                        $export[] = $das;
                    }
                }
                $model_execl = new  model_execl();
                $model_execl::export($export, array(
                    "title"   => "问卷调查数据统计-" . date('Y-m-d-H-i', time()),
                    "columns" => array(
                        array(
                            'title' => '问卷问题',
                            'field' => 'qtitle',
                            'width' => 15
                        ),
                        array(
                            'title' => '问卷选项',
                            'field' => 'title',
                            'width' => 30
                        ),
                        array(
                            'title' => '问卷类型',
                            'field' => 'qtype',
                            'width' => 10
                        ),
                        array(
                            'title' => '所占比例',
                            'field' => 'scale',
                            'width' => 10
                        )
                    )
                ));
            }

        }else{
            $this->error(__('数据请求错误！！'));
        }
        $this->assign(array("list"=>$list,"ids"=>$ids));
        return view();
    }

    /**
     * 查看问卷内容
     */
    public  function  showcontent(){
        $voteid = $this->request->get("id");
        $page = $this->request->get("page");
        if ($voteid) {
            $question =  Db::name('question_question')->where("id",$voteid)->field('id,title,type,voteid')->find();
            $psize =1;
            $list = Db::name('question_user_answer')->where(array("questionid"=>$voteid,"type"=>3))->order("createtime desc")->paginate($psize,false,["query"=>["id"=>$voteid]]);
            $page = $list->render();
        }
        $this->assign(array("question"=>$question,"list"=>$list,"pager"=>$page));
        return view("/property/question/content/show_tk");
    }


    /**
     *展示详细信息
     */
    public function Questionshow($ids="",$explort=""){
        $condition = '';
        if ($ids) {
            $condition .= "an.voteid={$ids}";
            $keyword = $this->request->post("keyword");
            if (!empty($keyword)) {
                $condition .=" AND wt.title like'%{$keyword}%'";
            }
            $psize = 20;
            // 问卷问题的选项
            $list =  Db::name('Question_user_answer')
            ->alias('an')
            ->field("an.*,wt.title,da.title as answer,us.relaname,us.address,us.nickname,us.mobile")
            ->join('Question_question wt','an.questionid = wt.id')
            ->join('Question_options da','an.optionsid = da.id')->join("Question_user us","an.userid = us.id")
            ->where($condition)->order("wt.type asc")
            ->paginate($psize,false,["query"=>["ids"=>$ids]]);
            //导出数据
            if ($explort) {
                foreach ($list as $item) {
                    $newlist["title"]   = $item["title"];
                    $newlist["address"]  = $item["address"];
                    $newlist["relaname"] = $item["relaname"];
                    $newlist["mobile"] = $item["mobile"];
                    $newlist["nickname"] = $item["nickname"];
                    $newlist["answer"] = $item["answer"];
                    $newlists[] = $newlist;
                }
                model_execl::export($newlists, array(
                    "title"   => "调查数据-" . date('Y-m-d-H-i', time()),
                    "columns" => array(
                        array(
                            'title' => '问卷标题',
                            'field' => 'title',
                            'width' => 40
                        ),
                        array(
                            'title' => '业主回答',
                            'field' => 'answer',
                            'width' => 30
                        ),
                        array(
                            'title' => '业主姓名',
                            'field' => 'relaname',
                            'width' => 20
                        ),
                        array(
                            'title' => '业主电话',
                            'field' => 'mobile',
                            'width' => 12
                        ),
                        array(
                            'title' => '微信昵称',
                            'field' => 'nickname',
                            'width' => 18
                        ),
                        array(
                            'title' => '业主地址',
                            'field' => 'address',
                            'width' => 20
                        )
                    )
                ));
            }
            $page = $list->render();
            $this->assign(array("voteid"=>$ids,"keyword"=>$keyword,"list"=>$list->items(),"pager"=>$page));
        }
        return view("/property/question/content/question_list");
    }

    /**
     *新增问卷
     */
    public function add(){
        if ($this->request->isPost()) {
            $params    = $this->request->post("row/a");
            if ($params) {
                $params["starttime"] = strtotime($params["starttime"]);
                $params["endtime"]   = strtotime($params["endtime"]);
                $params['createtime'] = time();
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
                $regiondata =array('aid'   => $voteid, 'lecommunity' => implode(",",$reginonid));
                $result = Db::name('Question_region')->insertGetId($regiondata);

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
     *修改问卷
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
        $rows = db('question')->where('id', $row['id'])->find();
        $rows["starttime"] = date("Y-m-d H:i:s",$rows["starttime"]);
        $rows["endtime"]   = date("Y-m-d H:i:s",$rows["endtime"]);
        $regs = Db::name("question_region")->where('aid',$rows['id'])->find();
        //默认小区
        $regionids = '';
        if (empty($rows['status'])){//添加页面默认选中乐软小区
            $regionids = json_encode(explode(',',$regs["lecommunity"]));
        }
        if ($this->request->isPost()) {//提交
            $params = $this->request->post("row/a");
            $params["starttime"] = strtotime($params["starttime"]);
            $params["endtime"] = strtotime($params["endtime"]);
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

                    $regiondata = implode(',',$reginonid);
                }else{//绑定所有小区
                    $params['provice']  = '';
                    $params['city']     = '';
                    //绑定所有小区
                    $regions = json_decode($lesoft->GetSoftUrl(config::get('leapi.Lp')),true)["Result"];
                    foreach ($regions as $k => $v) {
                        $dat[] = $v['pk'];
                    }
                    $regiondata = implode(',',$dat);
                }
                $noteid = db('Question')->where('id',$id)->update($params);

                //查看是否有绑定记录
                if($regs){
                    $result = db('Question_region')->where('aid',$id)->setField('lecommunity',$regiondata);
                }else{
                    $result = db::name('Question_region')->insert(array("aid"=>$id,"lecommunity"=>$regiondata));
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
     * 删除问卷
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
                Db::name('question_region')->where('aid',$v->id)->delete();
                $questions = Db::name('question_question')->field("id")->where('voteid',$v->id)->select();
                if($questions){//上删除问题项
                    foreach ($questions as $question) {
                        Db::name('question_options')->where('questionid',$question["id"])->delete();
                    }
                    Db::name('question_question')->where('voteid',$v->id)->delete();
                }
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
     *初始化数据
     */
    public function initdata($ids = ""){
        if ($ids) {
            $item = db('question')->where('id', $ids)->find();
            if ($item) {
                $res = Db('question_user')->where('voteid',$ids)->delete();
                if ($res) {
                    Db('question_user_answer')->where('voteid',$ids)->delete();
                }
                return $this->success("初始化成功",'');
            }else{
                return $this->error("初始化问卷不存在",'');
            }
        }else{
            return $this->error("参数请求错误！",'');
        }
    }

}
