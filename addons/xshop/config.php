<?php

return array (
  0 => 
  array (
    'name' => 'template_pages',
    'type' => 'hidden',
    'value' => 'manifest.json,pages.json,common/request/config.js,common/wxApi.js,pages/user/user.vue',
  ),
  1 => 
  array (
    'name' => '__APP_NAME__',
    'is_replace' => 1,
    'title' => '应用名称',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => '某某商城',
    'rule' => '',
    'tip' => '例如：某某商城',
    'extend' => '',
  ),
  3 => 
  array (
    'name' => '__H5_ROUTE_MODE__',
    'title' => 'H5路由模式',
    'type' => 'select',
    'frontend' => 1,
    'is_replace' => 1,
    'content' => 
    array (
      'hash' => 'hash',
      'history' => 'history',
    ),
    'value' => 'hash',
    'rule' => '',
    'tip' => '',
    'extend' => '',
  ),
  4 => 
  array (
    'name' => '__H5_BASE_PATH__',
    'title' => 'H5运行的基础路径',
    'type' => 'string',
    'is_replace' => 1,
    'content' => 
    array (
    ),
    'value' => '/h5/',
    'rule' => '',
    'tip' => '例如/h5/,代表在域名的/h5/目录下部署运行',
    'extend' => '',
  ),
  5 => 
  array (
    'name' => '__DOMAIN__',
    'title' => 'H5部署域名',
    'type' => 'string',
    'is_replace' => 1,
    'content' => 
    array (
    ),
    'value' =>  request()->domain(),
    'rule' => '',
    'tip' => '例如：https://www.site.com',
    'extend' => '',
  ),
  6 => 
  array (
    'name' => '__COPYRIGHT__',
    'title' => '版权信息',
    'type' => 'string',
    'is_replace' => 1,
    'content' => 
    array (
    ),
    'value' => '某某公司版权所有',
    'rule' => '',
    'tip' => '',
    'extend' => '',
  ),
  7 => 
  array (
    'title' => '分类模式',
    'name' => 'cat_mode',
    'type' => 'select',
    'frontend' => 1,
    'content' => 
    array (
      1 => '二级分类',
      2 => '三级分类',
    ),
    'value' => '2',
    'rule' => '',
    'tip' => '',
    'extend' => '',
  ),
  8 => 
  array (
    'title' => '物流查询',
    'name' => 'express_open',
    'type' => 'radio',
    'frontend' => 1,
    'content' => 
    array (
      0 => '关闭',
      1 => '开启',
    ),
    'value' => '1',
    'rule' => '',
    'tip' => '',
    'extend' => '',
  ),
  9 => 
  array (
    'title' => '物流接口',
    'name' => 'express_api',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => '\\addons\\kdniao\\library\\Kd',
    'rule' => '',
    'tip' => '',
    'extend' => '',
  ),
  10 => 
  array (
    'title' => 'CDN地址',
    'name' => 'cdnurl',
    'type' => 'string',
    'frontend' => 1,
    'content' => 
    array (
    ),
    'value' => request()->domain(),
    'rule' => '',
    'tip' => '',
    'extend' => '',
  ),
);
