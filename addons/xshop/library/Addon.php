<?php

namespace addons\xshop\library;

class Addon
{

    public $name;

    public function __construct($addon_name)
    {
        $this->name = $addon_name;
    }

    public function config()
    {
        return get_addon_config($this->name);
    }

    /**
     * 获取H5基础路径
     */
    public function getH5BasePath()
    {
        $config = $this->config();
        $url = $config['__DOMAIN__'] . $config['__H5_BASE_PATH__'];
        if ($config['__H5_ROUTE_MODE__'] == 'hash') {
            $url .= '#/';
        }
        return $url;
    }

    public static function getPackageVersion($addon_name)
    {
        $file = ADDON_PATH . $addon_name . DS . 'package_version';
        $content = "";
        if (file_exists($file)) {
            $content = trim(file_get_contents($file));
        }
        $result = [];
        foreach (explode(',', $content) as $v) {
            $vo = trim($v);
            if (!empty($vo)) $result[] = $vo;
        }
        return $result;
    }

    public static function setPackageVersion($addon_name, $version)
    {
        $file = ADDON_PATH . $addon_name . DS . 'package_version';
        if (file_exists($file)) {
            $handle = fopen($file, 'a');
        } else  $handle = fopen($file, 'w');
        fwrite($handle, ',' . $version);
        fclose($handle);
    }
}