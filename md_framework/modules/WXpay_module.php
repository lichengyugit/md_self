<?php
/**
 * 微信 app 支付
 *
 */
class WXpay_module
{
    public $appid;
    public $mch_id;
    public $api_key;
    public $notify_url;

    public function __construct()
    {
        require_once(DEFAULT_SYSTEM_PATH.'libraries/WXpay/tenpay_config.php');
        $this->appid = $tenpay_config['app_id'];
        $this->mch_id = $tenpay_config['mch_id'];
        $this->api_key = $tenpay_config['api_key'];
        $this->notify_url = $tenpay_config['notify_url'];
    }

    /**
     * app微信支付
     * @param body, tade_no 订单号， total_fee 金额
     *@return 
     */
    public function appPay($body,$tade_no,$total_fee,$notify_url,$attach)
    {
        $result = $this->getPrePayOrder($body, $tade_no, $total_fee,$notify_url,$attach);
        $x = $this->getOrder($result['prepay_id'],$tade_no);
        $x['packageValue'] = $x['package'];
        unset($x['package']);
        return json_encode($x);
    }

    //app支付：统一下单
    public function getPrePayOrder($body, $out_trade_no, $total_fee,$notify_url='',$attach=''){
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
        $data["trade_type"] = "APP";
        if (!empty($attach)) {
           $data["attach"] = $attach;
        }
        $s = $this->getSign($data, false);
        $data["sign"] = $s;
        $xml = $this->arrayToXml($data);
        // $file = fopen("log.txt","w");
        $response = $this->postXmlCurl($xml, $url);
        //将微信返回的结果xml转成数组
        return $this->xmlstr_to_array($response);
    }

    //获取app支付参数
    public function getOrder($prepayId,$out_trade_no){
        $data["appid"] = $this->appid;
        $data["noncestr"] = $this->getRandChar(32);
        $data["package"] = "Sign=WXPay";
        $data["partnerid"] = $this->mch_id;
        $data["prepayid"] = $prepayId;
        $data["timestamp"] = time();
        $s = $this->getSign($data, false);
        $data["out_trade_no"] = $out_trade_no;
        $data["sign"] = $s;

        return $data;
    }

    /**
     *  查询订单
     * @param $out_trade_no  商户订单号;   $transaction_id  微信订单号
     */
    public function orderQuery($out_trade_no='',$transaction_id='')
    {
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        if (!$out_trade_no && !$transaction_id) {
            echo 'out_trade_no、transaction_id至少填一个！';exit;
        }
        if (!empty($out_trade_no)) {
            $data["out_trade_no"] = $out_trade_no;
        }
        if (!empty($transaction_id)) {
            $data["transaction_id"] = $transaction_id;
        }
        $data["appid"] = $this->appid;
        $data["mch_id"] = $this->mch_id;
        $data["nonce_str"] = $this->getRandChar(32);
        $s = $this->getSign($data, false);
        $data["sign"] = $s;

        $xml = $this->arrayToXml($data);
        // $file = fopen("log.txt","w");
        $response = $this->postXmlCurl($xml, $url);
        //将微信返回的结果xml转成数组
        return $this->xmlstr_to_array($response);
    }
    //获取指定长度的随机字符串
    public function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;

        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    /*
     获取当前服务器的IP
     */
    public function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }

    //签名
    public function getSign($Obj)
    {
        // global $API_KEY;
        foreach ($Obj as $k => $v)
        {
            $Parameters[strtolower($k)] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo "【string】 =".$String."</br>";
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$this->api_key;
        //签名步骤三：MD5加密
        $result_ = strtoupper(md5($String));
        return $result_;
    }

    //将数组转成uri字符串
    public function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     xml转成数组
     */
    public function xmlstr_to_array($xmlstr) {
        $doc = new DOMDocument();
        $doc->loadXML($xmlstr);
        return $this->domnode_to_array($doc->documentElement);
    }

    public function domnode_to_array($node) {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->domnode_to_array($child);
                    if(isset($child->tagName)) {
                        $t = $child->tagName;
                        if(!isset($output[$t])) {
                            $output[$t] = array();
                        }
                        $output[$t][] = $v;
                    }
                    elseif($v) {
                        $output = (string) $v;
                    }
                }
                if(is_array($output)) {
                    if($node->attributes->length) {
                        $a = array();
                        foreach($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string) $attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if(is_array($v) && count($v)==1 && $t!='@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }
    //数组转xml
    public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";

            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    //post https请求，CURLOPT_POSTFIELDS xml格式
    public function postXmlCurl($xml,$url,$second=30)
    {
        //初始化curl
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        //这里设置代理，如果有的话
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data)
        {
            curl_close($ch);
            return $data;
        }
        else
        {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }
}