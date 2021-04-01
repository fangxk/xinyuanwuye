<?php

namespace app\admin\controller\xshop;

use addons\xshop\library\Addon;

class Plugin extends Base
{
    public function index()
    {
        if ($this->request->isAjax()) {
            $api_url = get_addon_info('xshop')['api_url'];
            $data = \fast\Http::post($api_url . '/addons/xshopplugin/resources');
            $data = (array)json_decode($data, true);
            if ($data['code'] == 1) {
                $result = ['rows' => $data['data']];
            } else {
                $result = [];
            }
            return json($result);
        }
        $addon_list = get_addon_list();
        foreach ($addon_list as $k => $v) {
            $addon_list[$k]['package_version'] = Addon::getPackageVersion($k);
        }
        $this->view->assign('addon_list', json_encode($addon_list));
        return $this->view->fetch();
    }

    
}