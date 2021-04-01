<?php

namespace app\admin\controller\xshop;

use app\common\controller\Backend;
use think\Db;
use app\admin\model\xshop\Order as OrderModel;
use app\admin\model\xshop\OrderProducts as OrderProductsModel;
use think\Config;
use fast\Random;
use app\admin\library\xshop\Form;

/**
 * Dashboard
 *
 * @icon fa fa-circle-o
 */
class Index extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 控制台
     */
    public function index()
    {
        $start_time = empty(request()->get('start_time')) ? strtotime(date('Y-m-d')) - 7 * 24 * 60 *60 : strtotime(request()->get('start_time'));
        $end_time = empty(request()->get('end_time')) ? strtotime(date('Y-m-d')) + (24 * 60 * 60 - 1) : strtotime(request()->get('end_time'));
        $type = empty(request()->get('type')) ? 0 : request()->get('type');
        
        $query = function($q) use ($start_time, $end_time) {
            $q->where('create_time', 'between time', [$start_time, $end_time]);
        };
        $list = OrderModel::where($query)->field('sum(order_price) as order_price,count(*) as count,is_pay,from_unixtime(create_time, "%Y-%m-%d") as dt')->group('dt,is_pay')->select();
        
        $orderData = [];
        $total = []; // 订单总数
        $paied = []; // 已支付 
        $orderData['column'] = array_values(array_unique(array_column($list, 'dt')));

        $field = $type == 0 ? 'count' : 'order_price';

        foreach ($list as $row) {
            $total[$row['dt']] = (empty($total[$row['dt']]) ? 0 : $total[$row['dt']]) + $row[$field];
            if ($row['is_pay'] == 1) {
                $paied[$row['dt']] = (empty($paied[$row['dt']]) ? 0 : $paied[$row['dt']]) + $row[$field];
            } else {
                $paied[$row['dt']] = (empty($paied[$row['dt']]) ? 0 : $paied[$row['dt']]);
            }
        }
        $orderData['series'] = [
            [
                "name" => "全部订单",
                "type" => "line",
                "data" => array_values($total)
            ],
            [
                "name" => "已付款订单",
                "type" => 'line',
                'data' => array_values($paied)
            ]
        ];
        $today_start = strtotime(date('Y-m-d'), time());
        // 已支付
        $order_paied = OrderModel::where('status', '>', 0)->where('is_pay', 1)->where($query)->field('count(*) as count,sum(order_price) as order_price')->find();
        //未支付
        $order_wait_pay = OrderModel::where('status', 0)->where($query)->field('count(*) as count,sum(order_price) as order_price')->find();
        // 已发货
        $order_shipped = OrderModel::where('status', '>', -1)->where('is_delivery', 1)->where($query)->field('count(*) as count,sum(order_price) as order_price')->find();
        // 待发货
        $order_wait_ship = OrderModel::where('status', '>', -1)->where('is_pay', 1)->where('is_delivery', 0)->where($query)->field('count(*) as count,sum(order_price) as order_price')->find();
        
        $totalInfo = [
            'order_paied' => $order_paied,
            'order_wait_pay' => $order_wait_pay,
            'order_shipped' => $order_shipped,
            'order_wait_ship' => $order_wait_ship
        ];

        $limit = 15;

        // 商品销售额排行
        $productsMoney = Db::table('__XSHOP_ORDER_PRODUCT__')->alias('a')->join('__XSHOP_ORDER__ b', 'a.order_id=b.id')
            ->where('b.is_pay', 1)
            ->where('a.create_time', 'between time', [$start_time, $end_time])
            ->field('a.title,sum(a.product_price) as product_price,attributes')->group('a.product_id,a.title,a.attributes')->order('product_price DESC')->limit($limit)->select();
        
        // 商品销售件数排行
        $productsNumber = Db::table('__XSHOP_ORDER_PRODUCT__')->alias('a')->join('__XSHOP_ORDER__ b', 'a.order_id=b.id')
            ->where('b.is_pay', 1)
            ->where('a.create_time', 'between time', [$start_time, $end_time])
            ->field('a.title,sum(a.quantity) as quantity,a.attributes')->group('a.product_id,a.title,a.attributes')->order('quantity DESC')->limit($limit)->select();
        
        if ($this->request->isAjax()) {
            $data = [
                'totalInfo' => $totalInfo,
                'orderData' => $orderData,
                'productsMoney' => $productsMoney,
                'productsNumber' => $productsNumber
            ];
            return $this->success('', null, $data);
        }
        $this->view->assign('start_time', date('Y-m-d H:i', $start_time));
        $this->view->assign('end_time', date('Y-m-d H:i', $end_time));
        $this->view->assign('productsMoney', $productsMoney);
        $this->view->assign('productsNumber', $productsNumber);
        $this->view->assign('totalInfo', $totalInfo);
        $this->view->assign('orderData', $orderData);
        return $this->view->fetch();
    }

    /**
     * 选择城市
     */
    public function citySelector()
    {
        if ($this->request->isAjax()) {
            $list = \app\admin\model\xshop\Area::getTreeArray([1, 2]);
            $list = [['id' => 0, 'name' => '全国', 'childlist' => $list]];
            return $this->success('', null, $list);
        }
        return $this->view->fetch();
    }

    /**
     * 上传文件
     */
    public function upload()
    {
        Config::set('default_return_type', 'json');
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();
        $extparam = $this->request->post();

        $upload = Config::get('upload');
        
        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);

        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('Uploaded file format is limited'));
        }
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{hour}'     => date("H"),
            '{min}'      => date("i"),
            '{sec}'      => date("s"),
            '{random}'   => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
            '{filemd5}'  => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);
        //
        $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH . '/public' . $uploadDir, $fileName);
        if ($splInfo) {
            $imagewidth = $imageheight = 0;
            if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf', 'apk'])) {
                $imgInfo = getimagesize($splInfo->getPathname());
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
            }
            $params = array(
                'admin_id'    => (int)$this->auth->id,
                'user_id'     => 0,
                'filesize'    => $fileInfo['size'],
                'imagewidth'  => $imagewidth,
                'imageheight' => $imageheight,
                'imagetype'   => $suffix,
                'imageframes' => 0,
                'mimetype'    => $fileInfo['type'],
                'url'         => $uploadDir . $splInfo->getSaveName(),
                'uploadtime'  => time(),
                'storage'     => 'local',
                'sha1'        => $sha1,
                'extparam'    => json_encode($extparam),
            );
            $attachment = model("attachment");
            $attachment->data(array_filter($params));
            $attachment->save();
            \think\Hook::listen("upload_after", $attachment);
            $this->success(__('Upload successful'), null, [
                'url' => $uploadDir . $splInfo->getSaveName()
            ]);
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

    /**
     * 下载前端文件
     */
    public function down_frontend()
    {
        $fullconfig = get_addon_fullconfig('xshop');
        $config = get_addon_config('xshop');
        $app_params = \addons\xshop\model\ConfigModel::getByCodes([
            'xshop_h5_appid', 'xshop_wx_mp_appid', 'xshop_tt_mp_appid',
            'xshop_wx_app_appid'
        ]);
        
        $params['__WECHAT_OPEN_APPID__'] = $app_params['xshop_wx_app_appid'] ?? '';
        $params['__MP_WECHAT_APPID__'] = $app_params['xshop_wx_mp_appid'] ?? '';
        $params['__MP_TOUTIAO_APPID__'] = $app_params['xshop_tt_mp_appid'] ?? '';
        
        $pages = explode(',', $config['template_pages']);
        $cache_tpl_path = CACHE_PATH . 'xshop' . DS . 'template';
        if (is_dir($cache_tpl_path)) rmdirs($cache_tpl_path);
        copydirs(ADDON_PATH . 'xshop'. DS . 'template', $cache_tpl_path);
        foreach ($pages as $page) {
            $file = str_replace('/', DS, $cache_tpl_path . DS . $page);
            $content = file_get_contents($file);
            foreach ($fullconfig as $v) {
                if (!empty($v['is_replace'])) {
                    $content = str_replace($v['name'], $v['value'], $content);
                }
            }
            foreach ($params as $k => $v) {
                $content = str_replace($k, $v, $content);
            }
            file_put_contents($file, $content);
        }

        

        $zipFile = $cache_tpl_path . '.zip';
        if (is_file($zipFile)) rmdirs($zipFile);
        $cache_tpl_path  = $cache_tpl_path . DS;
        $zip = new \ZipArchive;
        $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($cache_tpl_path), \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = str_replace(DS, '/', substr($filePath, strlen($cache_tpl_path)));
                if (!in_array($file->getFilename(), ['.git', '.DS_Store', 'Thumbs.db'])) {
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
        $zip->close();

        $file = fopen($zipFile, 'rb');
        $file_dir = './down/';  
        Header("Content-type: application/octet-stream" ); 
        Header("Accept-Ranges: bytes" );  
        Header("Accept-Length: " . filesize($zipFile));  
        Header("Content-Disposition: attachment; filename=xshop-uni-app.zip");    

        echo fread($file, filesize($zipFile));    
        fclose($file);
        exit;
    }

    /** 忽略SQL报错 */
    public function ignore()
    {
        $referer = request()->header('referer');
        $params = $this->request->get();
        $data = \think\Db::name('xshop_upgrade_sql')->where($params)->find();
        if (empty($data)) {
            $params['create_time'] = time();
            \think\Db::name('xshop_upgrade_sql')->insert($params);
        }
        $this->removeInfoAtErrSql($params);
        header("Location:" . $referer);
    }

    /**
     * 移除缓存的SQL出错信息
     */
    private function removeInfoAtErrSql($data)
    {
        $list = cache('xshop_install_sql_err');
        $res = [];
        foreach ($list as $item) {
            if ($data['addon'] == $item['addon'] && $data['name'] == $item['name'] && $data['dir'] == $item['dir']) continue;
            $res[] = $item;
        }
        cache('xshop_install_sql_err', $res);
    }
}
