<?php


namespace app\api\controller;


use app\common\controller\Api;

class Config extends Api
{
    protected $noNeedLogin = '*';

    protected $noNeedRight = '*';


    /**
     * 获取系统配置项
     *
     * @param string type 配置项变量名称
     */
    public function getConfigOption()
    {
        $type = $this->request->param('type');

        $this->success('获取成功', config('site.' . $type));
    }
}