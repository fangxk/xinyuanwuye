<?php
/**
 * invoice.php
 * Create By Company JUN HE
 * User XF
 * @date 2020-11-20 14:12
 */
namespace app\api\controller;
use app\common\controller\Api;
use app\admin\controller\LeSoft;
use think\Config;
use think\Validate;

class Invoice extends Api{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';


    //申请发票
    const ApplyInVoice_URL = 'http://117.158.24.187:8001/LsInterfaceServer/electronic/addelectronicinvoice';
    //开票历史
    const InvoiceHistory_URL = "http://117.158.24.187:8001/LsInterfaceServer/electronic/queryinvoicehis";
    /*//已缴 发票详情备份
    const InvoiceINFO_URL = "http://117.158.24.187:8001/LsInterfaceServer/electronic/querytitlebypkgathering";*/
    //已缴 发票详情
    const InvoiceINFO_URL = "http://117.158.24.187:8001/LsInterfaceServer/electronic/queryinvoicedetails";
    //发票抬头列表
    const InvoiceList_URL = 'http://117.158.24.187:8001/LsInterfaceServer/electronic/querytitleaction';
    //添加发票抬头
    const AddInVoice_URL   = 'http://117.158.24.187:8001/LsInterfaceServer/electronic/addinvoicetitle';
    //删除发票抬头
    const DeleteInVoice_URL = 'http://117.158.24.187:8001/LsInterfaceServer/electronic/deleteinvoicetitle';
    //房屋
    const HOUSE_URL = 'http://117.158.24.187:8001/LsInterfaceServer/phoneServer/wxClientHouse';


    //申请开票
    public function applyinvoice(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
        }
        $member = $this->auth->mc_lecheck(array("phone"=>$mobile));
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $param = array("pk_invoiceType"=>$this->request->param("pk_invoiceType"),
            "pk_receipttitle"=>$this->request->param("pk_receipttitle"),
            "mail"=>$this->request->param("mail"),
            "invoiceType"=>'01'
        );
        $result = json_decode((new LeSoft())->GetSoftUrl(self::ApplyInVoice_URL,$param),true);
        $this->success('申请成功');
    }

    //开票历史
    public  function invoicehistory(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
        }
        $member = $this->auth->mc_lecheck(array("phone"=>$mobile));
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        /*添加*/
        $params = ['phone' => $mobile];
        $resps = (new LeSoft())->GetSoftUrl(self::HOUSE_URL,$params );
        $results = json_decode($resps, true);

        if (isset($results['Result'])) {
            $pk_client = array();
            foreach ($results['Result'] as $v) {
                if ($v["client_type"] == 0 || $v["client_type"] == 1){
                    $pk_client[] = $v["pk_client"];
                }
                /* 权限 if ($v["client_type"] == 0){
                    $pk_client[] = $v["pk_client"];
                }*/
            }
        }
        /*添加*/
        //$pk_client = $this->auth->pk_client;
        //$pk_client = "0007CB555F00BCC85641";
        $page      = $this->request->param("page");
        $param     = array("pkClient"=>implode(',',$pk_client),
            "pageOprator"=>'bgoto',"gotopage"=>$page,"pageSize"=>10);
        $result = json_decode((new LeSoft())->GetSoftUrl(self::InvoiceHistory_URL,$param),true)["Result"];
        $this->success('获取成功', isset($result) ? $result : []);
    }

    //获取发票抬头列表
    public function getinvoicelist(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
        }
        $member = $this->auth->mc_lecheck(array("phone"=>$mobile));
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $pk_client = $this->auth->pk_client;
        $result = json_decode((new LeSoft())->GetSoftUrl(self::InvoiceList_URL,array("pkClient"=>$pk_client)),true)["Result"];
        $this->success('获取成功', isset($result) ? $result : []);
    }

    //发票详情
    public function invoiceinfo(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
        }
        $member = $this->auth->mc_lecheck(array("phone"=>$mobile));
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        //$pkGathering = $this->request->param("pkGathering");
        $pkInvoicerecord = $this->request->param("pkInvoicerecord");
        $result = json_decode((new LeSoft())->GetSoftUrl(self::InvoiceINFO_URL,array("pkInvoicerecord"=>$pkInvoicerecord)),true);
        //print_r($result);die;
        //$result = json_decode((new LeSoft())->GetSoftUrl(self::InvoiceINFO_URL,array("pkGathering"=>$pkGathering)),true);
        $this->success('获取成功', isset($result["Result"]) ? $result["Result"] : []);
    }

    //添加发票抬头
    public function addinvoice(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
        }
        $member = $this->auth->mc_lecheck(array("phone"=>$mobile));
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $pk_client = $this->auth->pk_client;
        $param["titleinfo"] = json_encode(array(
            "pkReceipttitle"=>(new LeSoft())->SjStr(20),
            "code"=>$this->request->param("code"),
            "name"=>$this->request->param("name"),
            "address"=>$this->request->param("address"),
            "cellphone"=>$this->request->param("cellphone"),
            "bank"=>$this->request->param("bank"),
            "accountid"=>$this->request->param("accountid"),
            "email"=>$this->request->param("email"),
            "defaulttitle"=>!empty($this->request->param("defaulttitle"))?true:false,
            "titletype"=>$this->request->param("titletype")
            ));
        $param["pk_client"] = $pk_client;
        $result = json_decode((new LeSoft())->GetSoftUrl(self::AddInVoice_URL,$param),true);
        if($result['StateCode']){
            $this->error('添加失败！', $result["ErrorMsg"]);
        }else{
            $this->success('添加成功',  $result);
        }
    }

    //删除发票抬头
    public function deletinvoice(){
        $mobile = $this->auth->mobile;
        if (empty($mobile)) {
            $this->error('请先认证');
        }
        $member = $this->auth->mc_lecheck(array("phone"=>$mobile));
        if(empty($member)){
            $this->error('您未是认证业主无权访问！');
        }
        $pkReceiptTitle = $this->request->param("pkReceiptTitle");
        $result = json_decode((new LeSoft())->GetSoftUrl(self::DeleteInVoice_URL,array("pkReceiptTitle"=>$pkReceiptTitle)),true);
        if($result['StateCode']){
            $this->error('删除失败！', $result["ErrorMsg"]);
        }else{
            $this->success('删除成功');
        }
    }

}