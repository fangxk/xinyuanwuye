<?php


namespace app\admin\controller\property\bill;

use app\common\controller\Backend;

class Bill extends Backend
{
    public function index()
    {
        return $this->view->fetch();
    }
}