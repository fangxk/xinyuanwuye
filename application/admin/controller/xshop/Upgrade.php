<?php

namespace app\admin\controller\xshop;

use addons\xshop\library\Addon;

/**
 * 在线升级 
 * 
 */
class Upgrade extends Base
{
    public function index()
    {
        if ($this->request->isAjax()) {
            $addon_name = $this->request->get('addon_name', 'xshop');
            $info = get_addon_info($addon_name);
            $api_url = get_addon_info('xshop')['api_url'];
            $data = \fast\Http::post($api_url . 'addons/xshopplugin/package', [
                'addon_name' => $addon_name,
                'version' => $info['version'],
                'package_version' => implode(',', Addon::getPackageVersion($addon_name))
            ]);
            $data = (array)json_decode($data, true);
            if ($data['code'] == 1) {
                $result = ['rows' => $data['data']];
            } else {
                $result = [];
            }
            return json($result);
        }
        return $this->view->fetch();
    }

    public function install()
    {
        $force = (int)$this->request->post("force");
        $package_version = $this->request->post("package_version");
        $addon_name = $this->request->post('addon_name');
        try {
            \addons\xshop\library\PackageService::install($addon_name, $force, [
                'version' => get_addon_info($addon_name)['version'],
                'package_version' => $package_version
            ]);
            Addon::setPackageVersion($addon_name, $package_version);
            $this->success();
        } catch (\think\addons\AddonException $e) {
            $this->error($e->getMessage());
        } catch (\think\Exception $e) {
            $this->error($e->getMessage());
        }
    }
    
}