<?php
require_once DEFAULT_SYSTEM_PATH.'libraries/weipay/lib/WxPay.Api.php';
require_once DEFAULT_SYSTEM_PATH.'libraries/weipay/lib/WxPay.Data.php';
require_once DEFAULT_SYSTEM_PATH.'libraries/weipay/example/WxPay.JsApiPay.php';

class WXlogin_module{
    private $appId=WxPayConfig::APPID;
    private $appSecret=WxPayConfig::APPSECRET;
    
    
    /**
     * 回调页面
     */
    
    public function getAuthCode($parames){
        $paramesStr=$this->arrayToStr($parames);
        $callbackUrl=urlencode(PAY_URL.'/weilogin?action=actionGetWXUser&'.$paramesStr);
        //授权页面地址
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appId."&redirect_uri=".$callbackUrl."&response_type=code&scope=snsapi_userinfo&state=666#wechat_redirect";
        //echo $url;exit;
        //跳转到授权页面
        header("location:".$url);
    }
    
    //获取access_token和open_id
    public function getAccessToken($parames,$code){
        if(!$code){
            //回调地址
            //$callbackUrl=urlencode('http://'.$_SERVER['HTTP_HOST'].'/weilogin?action=actionGetUserInfo&'.$paramesStr);
            //$callbackUrl=urlencode('http://'.$_SERVER['HTTP_HOST'].'/weilogin?action=test');
            //echo $callbackUrl;
            //通过用户授权获取到code，并跳转的回调页面
            $this->getAuthCode($parames);
            exit;
        }else{
            //$code=$_GET['code'];
            $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appId."&secret=".$this->appSecret."&code=".$code."&grant_type=authorization_code";
            $res=$this->curl($url);
            $arr=json_decode($res,true);
            $data['accessToken']=$arr['access_token'];
            $data['openId']=$arr['openid'];
        }
        return $data;
    }
    
    //获取用户信息
    public function getWechatUserInfo($parames,$code){
        //$paramesStr=$this->arrayToStr($parames);
        $arr=$this->getAccessToken($parames,$code);
        $url="https://api.weixin.qq.com/sns/userinfo?access_token=".$arr['accessToken']."&openid=".$arr['openId']."&lang=zh_CN";
        $res=$this->curl($url);
        $arr=json_decode($res,true);
        return $arr;
    }
    
    /**
     * 万能curl
     */
    public function curl($url,$type='get',$post_data=''){
        //1.初始化curl对象
        $curl=curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);//设置为0、1控制是否返回请求头信息
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        //执行curl请求,获取结果
        $res=curl_exec($curl);
        //关闭curl
        curl_close($curl);
        return $res;
    }
    
    private function arrayToStr($array){
        if(is_array($array)){
            $str='';
            foreach($array as $k=>$v){
                $str.=$k."=".$v."&";
            }
            return $str=substr($str, 0,-1);
        }else{
            return $array;
        }
    }
}