<?php

namespace addons\xshop\library;

class ImageHandle
{
    protected $mimes = [
        'image/bmp' => 'bmp',
        'image/gif' => 'gif', 
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/x-icon' => 'ico'
    ];

    protected $image_url = '';

    public function save($image_url = '', $dir = 'images')
    {
        $this->image_url = $image_url;
        if (empty($this->image_url)) return false;
        if (($headers = get_headers($this->image_url, 1)) !== false) {
            $type = $headers['Content-Type'];
            if (isset($this->mimes[$type])) {
                $dir = trim($dir);
                $file_ext = $this->mimes[$type];
                $file_url = DS . $dir . DS . date('Ym') . DS . date('d');
                $file_dir = ROOT_PATH . 'public' . $file_url;
                if (!is_dir($file_dir)) mkdir($file_dir, 0777, true);
                $file_name = \fast\Random::alnum(32);
                $file_path = $file_dir . DS . $file_name . "." . $file_ext;
                $content = file_get_contents($this->image_url);
                if (file_put_contents($file_path, $content)) {
                    //return $file_url . DS . $file_name . '.' . $file_ext;
                    return str_replace(DS, '/', $file_url . DS . $file_name . '.' . $file_ext);
                }
                return false;
            }
            return false;
        }
        return false;

    }

    public function setMimes(Array $mimes)
    {
        $this->mimes = array_merge($this->mimes, $mimes);
    }
}