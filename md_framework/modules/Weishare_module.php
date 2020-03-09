<?php
/**
 * 微信分享-JS
 *
 */
require_once DEFAULT_SYSTEM_PATH.'libraries/WXjssdk/jssdk.php';

class Weishare_module
{

    //微信分享
    public function share()
    {
        $jssdk = new JSSDK();
        $signPackage = $jssdk->GetSignPackage();
        return $signPackage;
    }

}