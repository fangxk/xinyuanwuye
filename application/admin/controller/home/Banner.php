<?php

namespace app\admin\controller\home;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Banner extends Backend
{
    
    /**
     * Banner模型对象
     * @var \app\admin\model\Home\Banner
     */
    protected $model = null;
    protected $searchFields = 'title';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Home\Banner;
        $this->view->assign("showswitchList", $this->model->getShowswitchList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 编辑
     */
    /*public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();

        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            print_r($params);die;
            $this->updateImUserInfo($row->im, $params['nickname'], $params['avatar']);
        }
    }*/
    

}
