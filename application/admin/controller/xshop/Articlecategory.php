<?php

namespace app\admin\controller\xshop;

use app\common\controller\Backend;
use fast\Tree;

/**
 * 文章分类
 *
 * @icon fa fa-circle-o
 */
class Articlecategory extends Base
{

    protected $modelValidate = true;
    
    /**
     * Articlecategory模型对象
     * @var \app\admin\model\xshop\Articlecategory
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\xshop\Articlecategory;
        $this->categoryList = $this->model->getTreeList();
        array_unshift($this->categoryList, ['id' => 0, 'title' => '无']);
        $this->assign('categoryList', $this->categoryList);

    }
    
    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        $this->searchFields = 'title';
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            
            $list = $this->model
                    ->where($where)
                    ->order($sort, $order)
                    ->limit(1000)
                    ->select();

            foreach ($list as $row) {
                $row->visible(['id', 'parent_id','title', 'description', 'sort']);
            }
            $list = collection($list)->toArray();
            $tree = Tree::instance();
            $tree->init($list, 'parent_id');
            $list = $tree->getTreeList($tree->getTreeArray(0), 'title');
            $result = array("rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
}
