<?php 
header("Content-type:text/html;charset=utf-8");
ini_set('date.timezone','Asia/Shanghai');
//error_reporting(E_ERROR);
require_once "../lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

//打印输出数组信息
function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
        echo "string";
    }
}

//①、获取用户openid
$tools = new JsApiPay();
$openId = $tools->GetOpenid();

//②、统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody("电动车安全出行解决方案");
$input->SetAttach("dianche");
$input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
$input->SetTotal_fee("1");
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("test");
$input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);
echo '<font color="#f00"><b>统一下单支付单信息12122221</b></font><br/>';
printf_info($order);


//获取共享收货地址js函数参数
//$editAddress = $tools->GetEditAddressParameters();

//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
//链接数据

	$con = mysql_connect("localhost","root","SX114qxg");
	echo "qqqqqqqqqqqqq";
	if (!$con)
	  {
	  die('Could not connect: ' . mysql_error());
	  }
	$sel = mysql_select_db("shouxi", $con);
	if (!$sel)
	  {
	  die('Could not connect: ' . mysql_error());
	  }
	 // echo "77";echo $sel;echo "888";exit();
/*  	$success_url = U("paysuccess", array("tradeno"=>$out_trade_no));
	$failure_url = U("payfailure", array("tradeno"=>$out_trade_no));*/
	//必须过滤这里的 wxpay/ 以确保返回的是主项目
	//$success_url = str_replace("wxpay/", "", $success_url);
	//将数据保存到数据库
	$out_trade_no = WxPayConfig::MCHID.date("YmdHis");
	$data = array();
	$data["openid"] = $openId;
	$data['userid'] = 123456;
	$data["source"] = 'http://'.$_SERVER['HTTP_HOST'];//来源，如来自某个网站或平台
	$data["total_fee"] = 1;
	//$data["total_fee"] = $order['money'];
	$data["out_trade_no"] =  $out_trade_no;
	$data["trade_type"] = "JSAPI";
	$data['status'] = 0;
	$data["pay_memo"] = "电动车安全出行解决方案";
	$data["time_start"] = time();
	$data["time_expire"] = time() + 600;
	$data["time_end"] = 0;
	$data["memo01"] = '';
	$data["memo02"] = '';
	$data["memo03"] = "orderid";
	//流程更改，订单号重新生成
	$order_sn = @date("YmdHis", time()) . rand(1000, 9999);

	$data["memo04"] =$order_sn;
	//$data["memo04"] = $order['order'];
	$data["create_time"] = time();
	$data["update_time"] = time();
/*	echo "<pre>";
	print_r($data);
	exit();*/

	echo "<script>
	alert(111);
	</script>";
	echo "fffffffffffffffff";
	exit();
	/*
	VALUES ($data["openid"], $data['userid'],$data["source"],$data["total_fee"],$data["out_trade_no"],$data["trade_type"],$data['status'],$data["pay_memo"],$data["time_start"],$data["time_expire"],$data["time_end"],$data["memo01"],$data["memo02"],$data["memo03"],$data["memo04"],$data["create_time"],$data["update_time"])
	*/
/*	$res = mysql_query("
	INSERT INTO sx_payment_weixin (`userid`, `openid`, `status`, `source`, `total_fee`, `out_trade_no`, `trade_type`, `pay_memo`, `time_start`, `time_expire`, `time_end`, `return_code`, `return_msg`, `return_xml`, `memo01`, `memo02`, `memo03`, `memo04`, `create_time`, `update_time`) VALUES ('26', '$data["openid"]', '1', 'http://croalarm.com', '29900', '136245800220170322153618', 'JSAPI', '电动车安全出行解决方案', '1490168178', '1490168778', '0', '', '', '', '', '', 'orderid', '201703221536157577', '1490168178', '1490168178');
		");*/
/*		M("payment_weixin")->add($data);*/ 
/*		$this->assign('failure_url', $failure_url);
		$this->assign('success_url', $success_url);
		$this->display();*/
		$jsApiParameters = $tools->GetJsApiParameters($order);
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
    <title>微信支付样例-支付example</title>
    <script type="text/javascript">
	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){
/*				WeixinJSBridge.log(res.err_msg);
				alert(res.err_code+res.err_desc+res.err_msg);*/
				WeixinJSBridge.log(res.err_msg);
			
				//alert(res.err_code+res.err_desc+res.err_msg);
				if(res.err_msg == "get_brand_wcpay_request:ok"){
				//if(res.err_code == "SUCCESS"){
				//alert("success")				
					location.href = "paysuccess.php";
				}
				else{
					alert("failure")
					location.href = "{$failure_url}";
				}
			
			}
		);
	}

	function callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
			alert(000);
		    if( document.addEventListener ){
		    	alert(111);
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		    	alert(222);
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
			alert(333);
		    jsApiCall();
		}
	}
	</script>
	<script type="text/javascript">
	//获取共享地址
	function editAddress()
	{
		WeixinJSBridge.invoke(
			'editAddress',
			<?php echo $editAddress; ?>,
			function(res){
				var value1 = res.proviceFirstStageName;
				var value2 = res.addressCitySecondStageName;
				var value3 = res.addressCountiesThirdStageName;
				var value4 = res.addressDetailInfo;
				var tel = res.telNumber;
				
				alert(value1 + value2 + value3 + value4 + ":" + tel);
				alert(222);
			}
		);
	}
	
	window.onload = function(){
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', editAddress, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', editAddress); 
		        document.attachEvent('onWeixinJSBridgeReady', editAddress);
		    }
		}else{
			editAddress();
		}
	};
	
	</script>
</head>
<body>
    <br/>
    <font color="#9ACD32"><b>该笔订单支付金额为<span style="color:#f00;font-size:50px">1分</span>钱</b></font><br/><br/>
	<div align="center">
		<button style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >立即支付</button>
	</div>
</body>
</html>