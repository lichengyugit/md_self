<?php
require_once("tenpay_config.php");

$APP_ID = $tenpay_config['app_id'];
$MCH_ID = $tenpay_config['mch_id'];
$API_KEY = $tenpay_config['api_key'];
$NOTIFY_URL = $tenpay_config['notify_url'];
$orderBody = $_GET['body'];
$tade_no = "lysd" . time().rand(100,999);
$total_fee = $_GET['total_fee'];
$result = getPrePayOrder($orderBody, $tade_no, $total_fee);
$x = getOrder($result['prepay_id'],$tade_no);
echo json_encode($x);

function getPrePayOrder($body, $out_trade_no, $total_fee){
    global $APP_ID,$MCH_ID,$NOTIFY_URL,$HAH;
    $HAH = "hehe";
    $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    $notify_url = $NOTIFY_URL;

    $onoce_str = getRandChar(32);

    $data["appid"] = $APP_ID;
   
    $data["body"] = $body;
    $data["mch_id"] = $MCH_ID;
    $data["nonce_str"] = $onoce_str;
   
    $data["notify_url"] = $notify_url;
    $data["out_trade_no"] = $out_trade_no;
    $data["spbill_create_ip"] = get_client_ip();
    $data["total_fee"] = $total_fee;
    $data["trade_type"] = "APP";
    $s = getSign($data, false);
    $data["sign"] = $s;

    $xml = arrayToXml($data);
    $file = fopen("log.txt","w");
 
    $response = postXmlCurl($xml, $url);
 
     

    //将微信返回的结果xml转成数组
    return xmlstr_to_array($response);
}

function getOrder($prepayId,$out_trade_no){
    global $APP_ID,$MCH_ID;
    $data["appid"] = $APP_ID;
    $data["noncestr"] = getRandChar(32);;
    $data["package"] = "Sign=WXPay";
    $data["partnerid"] = $MCH_ID;
    $data["prepayid"] = $prepayId;
    $data["timestamp"] = time();
    $s = getSign($data, false);
    $data["out_trade_no"] = $out_trade_no;
    $data["sign"] = $s;

    return $data;
}
function getSign($Obj)
{
    global $API_KEY;
    foreach ($Obj as $k => $v)
    {
        $Parameters[strtolower($k)] = $v;
    }
    //签名步骤一：按字典序排序参数
    ksort($Parameters);
    $String = formatBizQueryParaMap($Parameters, false);
    //echo "【string】 =".$String."</br>";
    //签名步骤二：在string后加入KEY
    $String = $String."&key=".$API_KEY;
    //签名步骤三：MD5加密
    $result_ = strtoupper(md5($String));
    return $result_;
}

//获取指定长度的随机字符串
function getRandChar($length){
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol)-1;

    for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
}

//数组转xml
function arrayToXml($arr)
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
function postXmlCurl($xml,$url,$second=30)
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

/*
 获取当前服务器的IP
 */
function get_client_ip()
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

//将数组转成uri字符串
function formatBizQueryParaMap($paraMap, $urlencode)
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
function xmlstr_to_array($xmlstr) {
    $doc = new DOMDocument();
    $doc->loadXML($xmlstr);
    return domnode_to_array($doc->documentElement);
}
function domnode_to_array($node) {
    $output = array();
    switch ($node->nodeType) {
        case XML_CDATA_SECTION_NODE:
        case XML_TEXT_NODE:
            $output = trim($node->textContent);
            break;
        case XML_ELEMENT_NODE:
            for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
                $child = $node->childNodes->item($i);
                $v = domnode_to_array($child);
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
?>