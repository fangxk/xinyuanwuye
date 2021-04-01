<?php

namespace app\admin\controller\xshop;

use app\common\controller\Backend;
use app\admin\model\xshop\Jshook;

/**
 * Dashboard
 *
 * @icon fa fa-circle-o
 */
class Base extends Backend
{

    protected $layoutViewPath = APP_PATH . 'admin' . DS . 'view' . DS;

    public $formOptions = [];
    public $useFormBuilder = false;

    public function _initialize()
    {
        parent::_initialize();
        if (!$this->request->isAjax() && cache('xshop_install_sql_err')) {
            $view = new \think\View;
            echo $view->fetch('xshop/public/warn', ['err_files' => cache('xshop_install_sql_err')]);
        }
        $this->hook = \addons\xshop\Hook::instance();
        $this->initHooks(); // 初始化钩子
        $hooks = "";
        try {
            $hooks = $this->initJsHooks();
        } catch(\think\exception\PDOException $e) {
            $view = new \think\View;
            echo $view->fetch('xshop/public/warn1');
        } catch (\think\Exception $e) {
            throw $e;
        }
        $this->assign('hooks', $hooks);
    }

    protected function initHooks()
    {
        $hooks = cache('xshop_hooks');
        $hooks = $hooks === false ? \addons\xshop\Hook::instance()->setListenersCache() : $hooks;
        $this->hook->bind($hooks);
    }

    protected function initJsHooks()
    {
        $controller = strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $this->request->controller()), "_"));
        $action = $this->request->action();
        $name = strtolower(str_replace('.', '_', $controller . '_' . $action));
        $addons = get_addon_list();
        $openAddons = [];
        foreach ($addons as $k => $v) {
            if ($v['state'] == 1) {
                $openAddons[] = $k;
            }
        }
        $hooks = Jshook::where('name', 'like', "$name.%")->where('state', 1)->where('addon_name', 'IN', $openAddons)->field('name,addon_name,action')->select();
        if ($hooks) {
            $new_hooks = [];
            foreach ($hooks as $item) {
                $item['name'] = str_replace($name . '.', '', $item['name']);
                $action = explode('/', 'backend/' . $item['addon_name'] . '/' . $item['action']);
                $method = explode('.', $action[count($action) - 1]);
                if (empty($method[1])) throw new \think\Exception($item['action'] . "没有指定方法");
                $action[count($action) - 1] = $method[0];
                $item['action'] = implode('/', $action);
                $item['method'] = $method[1];
                $new_hooks[$item['action']][] = $item;
            }
            $hooks = '<script> var hooks = ' . json_encode($new_hooks, true) . '</script>';
        } else $hooks = "";
        return $hooks;
    }

}