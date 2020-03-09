<?php
/**
 * 支付宝
 *
 */
class AliPay_module
{
    public $config;
    public function __construct()
    {
        require_once(DEFAULT_SYSTEM_PATH.'libraries/alipay/wappay/service/AlipayTradeService.php');
        require_once(DEFAULT_SYSTEM_PATH.'libraries/alipay/config.php');
        require_once(DEFAULT_SYSTEM_PATH.'libraries/alipay/aop/AopClient.php');
         // global $config;
        $this->config=$config;
        $this->client=new AopClient();
        $this->client->gatewayUrl = $this->config['gatewayUrl'];
        $this->client->appId = $this->config['app_id'];
        $this->client->rsaPrivateKey =  $this->config['merchant_private_key'];
        $this->client->alipayrsaPublicKey = $this->config['alipay_public_key'];
        $this->client->apiVersion ="1.0";
        $this->client->postCharset = $this->config['charset'];
        $this->client->format= 'JSON';
        $this->client->signType=$this->config['sign_type'];
    }

    /**
     * wap支付接口
     */    
    public function doPay($orderData)
    {
        if(is_array($orderData)){
            foreach($orderData as $k=>$v){
                $orderData[$k]=trim($v);
            }
        }
        $timeout_express="1m";
        require_once(DEFAULT_SYSTEM_PATH.'libraries/alipay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php');
        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($orderData['body']);
        $payRequestBuilder->setSubject($orderData['subject']);
        $payRequestBuilder->setOutTradeNo($orderData['out_trade_no']);
        $payRequestBuilder->setTotalAmount($orderData['total_amount']);
        $payRequestBuilder->setTimeExpress($timeout_express);
        if(isset($orderData['passback_params']) && !empty($orderData['passback_params']))
        {
            $payRequestBuilder->setPassbackParams($orderData['passback_params']);
        }
        $payResponse = new AlipayTradeService($this->config);
        $result=$payResponse->wapPay($payRequestBuilder,$this->config['return_url'],$this->config['notify_url']);
        return ;
    }

    /**
     * 支付查询接口
     * $out_trade_no 订单号
     */
    public function doQuery($orderDate)
    {
        if(is_array($orderDate)){
            foreach($orderDate as $k=>$v){
                $orderDate[$k]=trim($v);
            }
        }
        require_once(DEFAULT_SYSTEM_PATH.'libraries/alipay/wappay/buildermodel/AlipayTradeQueryContentBuilder.php');
                $RequestBuilder = new AlipayTradeQueryContentBuilder();
                $RequestBuilder->setTradeNo($orderDate['trade_no']);
                $RequestBuilder->setOutTradeNo($orderDate['out_trade_no']);
                $Response = new AlipayTradeService($this->config);
                return $Response->Query($RequestBuilder);
    }
    
    /**
     * 
     * 退款
     * @param string $out_trade_no 商户订单号
     * @param string $trade_no 支付宝订单号
     * @param string $refund_amount 总金额
     * @param string $refund_reason 退款说明
     * @return openid
     */
    public function doRefund($orderDate)
    {
        
        if(is_array($orderData)){
            foreach($orderData as $k=>$v){
                $orderData[$k]=trim($v);
            }
        }
        require_once(DEFAULT_SYSTEM_PATH.'libraries/alipay/wappay/buildermodel/AlipayTradeRefundContentBuilder.php');
        $RequestBuilder = new AlipayTradeRefundContentBuilder();
        $RequestBuilder->setTradeNo($orderDate['trade_no']);//支付宝交易号，和商户订单号二选一
        $RequestBuilder->setOutTradeNo($orderDate['out_trade_no']);//支付宝交易号，和商户订单号二选一
        $RequestBuilder->setRefundAmount($orderDate['refund_amount']);//退款金额，不能大于订单总金额
        $RequestBuilder->setRefundReason($orderDate['refund_reason']);//退款的原因说明
        $RequestBuilder->setOutRequestNo($orderDate['out_request_no']);//标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传。
        $Response = new AlipayTradeService($this->config);
        $result=$Response->Refund($RequestBuilder);
        return ;
    }

    /**
      * 支付宝app支付
      *@param   array param 业务参数
      * @return   string  
      */
    public function orderString($param)
    {
        require_once(DEFAULT_SYSTEM_PATH.'libraries/alipay/aop/request/AlipayTradeAppPayRequest.php');
        $request = new AlipayTradeAppPayRequest();
        $bizcontent = $param['biz_content'];
        $request->setNotifyUrl($this->config['notify_url']);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $this->client->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        return htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。

    }
}