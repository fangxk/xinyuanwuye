<?php

namespace app\common\library;

use think\Config;
use think\Db;

/**
 * 请求乐软接口的类库
 * Create By Company JUN HE
 * @author XF
 * @date 2020-09-22 9:26
 */
class LeSoft
{
    protected $appId = "2019010117";
    protected $appSecret = "EE58DCB20F64036C349D6BEEC800A344";
    protected $accountCode = "xywy";

    /**
     *
     * @param $url string 请求地址
     * @param $param array 请求参数
     * @return string
     * @author XF
     * @date 2020-09-22 9:30
     */
    public function GetSoftUrl($url,$param=array()){
        $ch = curl_init();
        $param["accountCode"] = $this->accountCode;
        $param["appId"]       = $this->appId;
        $param["sign"]        = $this->GetSign($param);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 乐软签名算法生成
     * @return string
     * @author XF
     * @date 2020-09-22 9:29
     */
    public function GetSign($data){
        ksort($data);
        $param = http_build_query($data);
        $params = $param."&key=".$this->appSecret;
        //生成sign签名
        $res = strtoupper(md5($params));
        return $res;
    }


    /**
     * [std_class_object_to_array 将对象转成数组]
     * @param [stdclass] $stdclassobject [对象]
     * @return [array] [数组]
     */
    public function object_to_array($stdclassobject)
    {
        $_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;
        foreach ($_array as $key => $value) {
            $value = (is_array($value) || is_object($value)) ? object_to_array($value) : $value;
            $array[$key] = $value;
        }
        return $array;
    }


    /**
     * 签名请求携带的随机字符串可以不用接口
     * @param $length
     * @return string
     * @author XF
     * @date 2020-09-22 9:27
     */
    protected function SjStr($length){
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $newstr = str_shuffle($str);
        $res = substr($newstr,0,$length);
        return $res;
    }
}
?>