<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Tree;
use think\Config;
use think\console\command\make\Model;
use think\Db;
use function MongoDB\BSON\fromJSON;

class Test extends Backend
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    public function index()
    {
        /*$data = Db("legroup")->where("parentguid='-1'")->select();*/
        $data = Db("legroup")->select();
        $das = legroup($data);
        $re = db('notice_region')->field("lecommunity")->where("aid = '27'")->find();
        foreach ($das as $k=>$da) {
            if($da["pk_project"] && strpos($re["lecommunity"],$da["pk_project"])!==false){
                $das[$k]["state"] = ["selecte"=>true];
            }else{
                $das[$k]["state"] = ["selected"=>false];
            }
        }
        print_r($das);die;
        /*$res = [];
        print_r($res);die;*/
        /*foreach ($data as $k=>$v){
            $res[]=[
                "id"=>$v["recguid"],
                "parent" => "#",
                "text" => $v["recchn"],
                "type" => "menu",
            ];
            $da1 = Db("legroup")->where("parentguid='{$v['recguid']}'")->select();

            foreach ($da1 as $k1=>$v1){
                $res[]=[
                    "id"=>$v1["recguid"],
                    "parent" => $v['recguid'],
                    "text" => $v1["recchn"],
                    "type" => "menu",
                ];
                $da2 = Db("legroup")->where("parentguid='{$v1['recguid']}'")->select();
                foreach ($da2 as $k2=>$v2){
                    $res[]=[
                        "id"=>$v2["recguid"],
                        "parent" => $v1['recguid'],
                        "text" => $v2["recchn"],
                        "type" => "menu",
                    ];
                    $da3 = Db("legroup")->where("parentguid='{$v2['recguid']}'")->select();
                    foreach ($da3 as $k3=>$v3){
                        $res[]=[
                            "id"=>$v3["recguid"],
                            "parent" => $v2['recguid'],
                            "text" => $v3["recchn"],
                            "type" => "menu"
                        ];
                        $da4 = Db("legroup")->where("parentguid='{$v3['recguid']}'")->select();
                        foreach ($da4 as $k4=>$v4){
                            $res[]=[
                                "id"=>$v4["recguid"],
                                "parent" => $v3['recguid'],
                                "text" => $v4["recchn"],
                                "type" => "menu",
                            ];
                            $ids[]=$v4['id'];
                            $da5 = Db("legroup")->where("parentguid='{$v4['recguid']}'")->select();
                            foreach ($da5 as $k5=>$v5){
                                $res[]=[
                                    "id"=>$v5["recguid"],
                                    "parent" => $v4['recguid'],
                                    "text" => $v5["recchn"],
                                    "type" => "menu",
                                ];
                            }
                        }
                    }
                }
            }
        }*/
    }

    public function diss($arr){

        $res = array();
        foreach ($arr as $v) {
            $data["id"]     = $v["pk_project"];
            $data["parent"] = "#";
            $data["text"]   = $v["recchn"];
            $data["type"]   = 'menu';
            $data["state"]  = [["selected"=>""]];
            $res[] = $data;
            if($v["child"]){
                $this->dis($v["child"]);
                print_r($res);die;
            }

            //$res[] = $data;
           // print_r($v);die;
            //print_r($res);die;
        }
        return $res;
    }

}
?>