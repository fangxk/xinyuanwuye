<?php

namespace addons\xshop\controller;

use addons\xshop\model\ArticleModel;
use think\addons\Controller;

/**
 * 文章
 * @ApiWeigh (10)
 */
class Article extends Controller
{
    protected function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $id = $this->request->get('id');
        $article = ArticleModel::get($id);
        $this->view->assign('article', $article);
        $this->view->assign('H5Url', (new \addons\xshop\library\Addon('xshop'))->getH5BasePath());
        return $this->view->fetch();
    }
}