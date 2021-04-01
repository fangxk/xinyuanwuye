<?php


namespace app\admin\controller\property;


use app\common\controller\Backend;

class Housekeeper extends Backend
{
    public function index()
    {
        return $this->view->fetch();
    }
}