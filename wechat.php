<?php

namespace app\index\controller;

use app\api\model\User;
use app\common\controller\Frontend;
use think\exception\DbException;
use think\Config;

class Wechat extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';




}
