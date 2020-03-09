<?php

// ---------------------------------------------------------
// ---------------------------------------------------------
require ("classes/ResponseHandler.class.php");
require ("classes/RequestHandler.class.php");
require ("classes/client/TenpayHttpClient.class.php");
require ("./classes/function.php");
require_once ("./tenpay_config.php");

$PARTNER = "*";
$PARTNER_KEY = $tenpay_config['api_key'];
$APP_ID = "*";
$APP_SECRET = "*";
$APP_KEY = "*";
$input = file_get_contents("php://input");
$xml = simplexml_load_string($input);
$money = (string) $xml->total_fee;
$return_code = (string) $xml->return_code; // 回调成功
$result_code = (string) $xml->result_code; // 支付结果
$attach = (string) $xml->attach;

// log_result($input);
// log_result("111111" . $money);
// log_result($return_code);
// log_result($attach);
// log_result($result_code);

if ($return_code == "SUCCESS") {
    if ($result_code == "SUCCESS") {
        log_result("支付成功");
    } else {
        log_result("支付失败，不需要发道具");
    }
    echo "Success";
} else {
    echo "Fail";
}
?>