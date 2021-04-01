<?php

namespace app\admin\controller\xshop;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 导航管理
 *
 * @icon fa fa-circle-o
 */
class Nav extends Base
{
    
    /**
     * Nav模型对象
     * @var \app\admin\model\xshop\Nav
     */
    protected $model = null;
    protected $searchFields = ['title', 'description'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\xshop\Nav;
        $config = get_addon_config('xshop');
        $url = $config['__DOMAIN__'] . $config['__H5_BASE_PATH__'];
        if ($config['__H5_ROUTE_MODE__'] == 'hash') {
            $url .= '#/';
        }
        $nav_types = [
            0 => '轮播图',
            1 => '导航',
            2 => '广告',
            3 => '分类展示',
            4 => '通告'
        ];
        $nav_type = $this->request->get('nav_type', -1);
        $this->view->assign('nav_type', $nav_type);
        $this->view->assign('nav_types', $nav_types);
        $this->view->assign('base_url', $url);
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    
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
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
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
        $target_type = intval($row['type']);
        $target_name = null;
        switch ($target_type) {
            case 1 :
                $product = \app\admin\model\xshop\Product::get($row['target']);
                $target_name = empty($product) ? null : $product['title'];
            break;
            case 2 :
                $category = \app\admin\model\xshop\Category::get($row['target']);
                $target_name = empty($category) ? null : $category['name'];
            break;
            case 5 :
                $article = \app\admin\model\xshop\Article::get($row['target']);
                $target_name = empty($article) ? null : $article['title'];
            break;
            default :
                $target_name = $row['target'];
            break;
        }
        $this->view->assign('target_name', $target_name);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

}
