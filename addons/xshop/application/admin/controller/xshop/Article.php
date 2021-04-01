<?php

namespace app\admin\controller\xshop;

use app\common\controller\Backend;
use app\admin\model\xshop\Articlecategory as ArticlecategoryModel;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use app\admin\model\xshop\Articlescategories;
use think\Db;

/**
 * 文章
 *
 * @icon fa fa-circle-o
 */
class Article extends Base
{
    
    /**
     * Article模型对象
     * @var \app\admin\model\xshop\Article
     */
    protected $model = null;
    protected $modelValidate = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\xshop\Article;
        $this->categoryList = ArticlecategoryModel::getTreeList();
        $this->assign('categoryList', $this->categoryList);
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        $category_id = $this->request->get('category_id');
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $query = function($q) use($category_id) {
                if (!empty($category_id)) {
                    $ids = Db::name('xshop_article_category')->field('id')->where('parent_id', $category_id)->whereOr('id', $category_id)->column('id');
                    $q->where('id', 'IN', Articlescategories::where('category_id', 'IN',  $ids)->column('article_id'));
                }
            };
            $total = $this->model
                    ->where($where)
                    ->where($query)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with('categories')
                    ->where($where)
                    ->where($query)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                $row->visible(['id','title','description','content','image','status','sort','create_time','update_time', 'categories']);
                
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $this->view->assign('category_id', $category_id);
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                $db = $this->model->db(false);
                $db->startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    $this->model->categories()->save($params['categories']);
                    $db->commit();
                } catch (ValidateException $e) {
                    $db->rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    $db->rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    $db->rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
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
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                $db = $this->model->db(false);
                $db->startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    $categories = $row->categories()->column('category_id');
                    $row->categories()->detach($categories);
                    $row->categories()->save($params['categories']);
                    $db->commit();
                } catch (ValidateException $e) {
                    $db->rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    $db->rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    $db->rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $cats = $row->categories()->column('category_id');
        $this->view->assign('cats', $cats);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 删除
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
            $db = $this->model->db(false);
            $db->startTrans();
            try {
                foreach ($list as $k => $v) {
                    $count += $v->delete();
                    $categories = $v->categories()->column('category_id');
                    $v->categories()->detach($categories);
                }
                $db->commit();
            } catch (PDOException $e) {
                $db->rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                $db->rollback();
                $this->error($e->getMessage());
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
     * 文章选择窗
     */
    public function select()
    {
        $params = $this->request->get();
        $list = \app\admin\model\xshop\Article::getList($params);
        $this->assign('list', $list);
        return $this->view->fetch();
    }
}
