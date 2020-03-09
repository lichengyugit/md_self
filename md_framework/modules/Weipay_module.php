<?php
/**
 * 微信支付-JS
 *
 */
require_once DEFAULT_SYSTEM_PATH.'libraries/weipay/lib/WxPay.Api.php';
require_once DEFAULT_SYSTEM_PATH.'libraries/weipay/lib/WxPay.Data.php';
require_once DEFAULT_SYSTEM_PATH.'libraries/weipay/example/WxPay.JsApiPay.php';

class Weipay_module
{
    //jsapi支付
    public function jsApiPay($body="",$out_trade_no,$money='1',$notify,$attach='',$openId)
    {
        //①、获取用户openid
        $tools = new JsApiPay();
//      $openId = $tools->GetOpenid();

        
        //$openId = '12121od5PN0vd3thQP0GHchYGIxA-OgJM';
        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody($body);
        if (!empty($attach)) {
            $input->SetAttach($attach);
        }
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($money);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetNotify_url($notify);
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
//         $parames['remark']=json_encode($order);
//         $insert=M_Mysqli_Class('md_lixiang', 'PayTestModel')->addLog($parames);

        $jsApiParameters = $tools->GetJsApiParameters($order);
        $data['jsApiParameters']=$jsApiParameters;
        return $data;
    }

    public function getOpenId($url=""){
        $tools = new JsApiPay();
        $openId = $tools->GetOpenid($url);
        return $openId;
    }
    
    
    //统一下单: H5支付
    public function unifiedorder($body, $out_trade_no, $total_fee,$notify_url='',$attach='',$trade_type='APP'){
        $HAH = "hehe";
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $notify_url = !empty($notify_url) ? $notify_url : $this->notify_url;

        $onoce_str = $this->getRandChar(32);

        $data["appid"] = $this->appid;
        $data["body"] = $body;
        $data["mch_id"] = $this->mch_id;
        $data["nonce_str"] = $onoce_str;
        $data["notify_url"] = $notify_url;
        $data["out_trade_no"] = $out_trade_no;
        $data["spbill_create_ip"] = $this->get_client_ip();
        $data["total_fee"] = $total_fee;
        $data["trade_type"] = $trade_type;
        if (!empty($attach)) {
           $data["attach"] = $attach;
        }
        if($trade_type=='MWEB'){
            $scene_info = array('h5_info'=>'h5_info',array('type'=>'Wap','wap_url'=>'dev.xiubike.com','wap_name'=>'修铺'));
            $data['scene_info'] = json_encode($scene_info);
        }
        $s = $this->getSign($data, false);
        $data["sign"] = $s;
        $xml = $this->arrayToXml($data);
        // $file = fopen("log.txt","w");
        $response = $this->postXmlCurl($xml, $url);
        //将微信返回的结果xml转成数组
        return $this->xmlstr_to_array($response);
    }

}