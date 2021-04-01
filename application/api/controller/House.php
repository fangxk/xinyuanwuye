<?php


namespace app\api\controller;


use app\admin\controller\LeSoft;
use app\common\controller\Api;
use think\Loader;

class House extends Api
{
    protected $noNeedLogin = '';

    protected $noNeedRight = '*';

    const HOUSE_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/wxClientHouse';


    /**
     * 获取所有小区
     */
    public function getProjectList()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
        }
        $param = ['phone' => $mobile];
        $resp = (new LeSoft())->GetSoftUrl(self::HOUSE_URL, $param);
        $result = json_decode($resp, true);
        if (isset($result['Result'])) {
            foreach ($result['Result'] as $v) {
                if ($v["client_type"] == 0 || $v["client_type"] == 1) {
                    $data[] = $v;
                }
                /*权限 if ($v["client_type"] == 0) {
                    $data[] = $v;
                }*/
            }
        }
        $this->success('获取成功', isset($data) ? $this->assoc_unique($data, 'project_name') : []);
    }

    /**
     * 获取所有房屋信息
     */
    public function getHouseList()
    {
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
        }
        $param = ['phone' => $mobile];
        $resp = (new LeSoft())->GetSoftUrl(self::HOUSE_URL, $param);
        $result = json_decode($resp, true);
       //print_r($result);die;
        if (isset($result['Result'])) {
            $data = array();
            foreach($result['Result'] as $ks=>$v){
                $a = $v["pk_client"].'='.$v["pk_house"];
                if(!isset($data[$a])){
                    $data[$a] = $v;
                }else{
                    $arr[] = $v;
                }
            }
            /*foreach ($result['Result'] as $v) {
                $data[] = $v;
                /*权限 if ($v["client_type"] == 1) {
                    $data[] = $v;
                }
            }*/
        }
        $data = array_values($data);
        //print_r($data);die;
        $this->success('获取成功', isset($data) ? $data: []);
    }

    public function assoc_unique($arr, $key) {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($arr); //sort函数对数组进行排序
        return $arr;
    }


    /**
     * 切换小区
     */
    public function switchHouse()
    {
        $post = $this->request->post();
        $validate = Loader::validate('House');
        if (!$validate->check($post)) {
            $this->error($validate->getError());
        }
        $user = $this->auth->getUser();
        $user->save($post);
        $this->success('切换成功');
    }
}