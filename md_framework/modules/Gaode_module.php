<?php
class Gaode_module{
    private $CI;
    private $log;
    private $key="23503aaec8a9d7ddf1519402d10ddd66";

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->log = & get_config();

    }

    public function locationChange($data){
        $url="http://restapi.amap.com/v3/assistant/coordinate/convert?locations=".$data."&coordsys=baidu&output=json&key=".$this->key;
        $res=$this->curl($url);
        $return=json_decode($res,true);
        return $return;

    }

    public function getAddress($data,$type='all'){//type all获取所有位置信息  base 获取基本位置信息
        $url="http://restapi.amap.com/v3/geocode/regeo?output=json&location=".$data."&key=".$this->key."&radius=1000&extensions=".$type;
        $res=$this->curl($url);
        $return=json_decode($res,true);
        return $return;
    }

    public function addTransformCoordinate($data){
        $url="http://restapi.amap.com/v3/geocode/geo?address=".$data."&output=json&key=".$this->key;
        $res=$this->curl($url);
        $return=json_decode($res,true);
        return $return;
    }
    private function curl($url,$type='get',$post_data=''){
        //1初始curl 对象
        $curl=curl_init();
        //2设置curl参数
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);//设置为0、1控制是否返回请求头信息
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if($type=='post'){
            curl_setopt($curl,CURLOPT_POST,1); // 设置提交方式为post
            curl_setopt($curl,CURLOPT_POSTFIELDS,$post_data);
        }
        //3执行
        $res=curl_exec($curl);
        //4关闭
        curl_close($curl);
        return $res;
    }
}
