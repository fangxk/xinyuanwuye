<?php


namespace app\admin\controller\property;


use app\common\controller\Backend;

class Feedback extends Backend
{
    public function index()
    {
        return $this->view->fetch();
    }
}