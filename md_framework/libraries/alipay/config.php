<?php
$config = array (
    //应用ID,您的APPID。
    'app_id' => "2018092861509754",

    //商户私钥，您的原始格式RSA私钥
    'merchant_private_key' => "MIIEpgIBAAKCAQEAu+4uwe3yvQ/nwwdvj7XLx5f2WgXRkl1Zpssu1m/PCrJQzV2SqGbQdqs0yVDSKAxAxD9cAuKP/1Dz6xkqQ6U+b+foX+tYgC7JmmjtN1e/qBuJ9tvh+mbO+Qjbjijr2u8hr+/4ggkbaN7XQRQUmlv/dDCwMrcJJRIAziCiZPxOGwHtB0YjbgztH6+x69J9JI2MKydEjNbaidgGCgN6bcFdooRND+Q0s1v7h2j+uhZN26Sn07UjVKtlPhAO4iOxrUo9Ou0oCJjvJrKlcWPAs8TtE62bIQAxN6cW3I/LDG+IkS2h76xbNmOYEUUINyyvmZuo1xknNoXz8nUPIHCCVkMjyQIDAQABAoIBAQCfwzKYxtKPC5B5aj7bKy+b9IIHsd6h0R4//4dH4TFf5ItwqtXct9sMpytu4Xhnd1MwArUT9st3v7g86t3VOAtGUx5nPMm13xCgZgcT/1L9dLvq0q7fkrrtVyxfREaNjrFSwmy/D0Lv+lGAD7QcLY2TlziTRKtPimiI05F6zEsSBTHYkMloHDFHXSvq5hYAmzu3wi4Ynb7H0rK5YwiRzEaKCMOXOZXiOyMzuJmGpPE/O1kOR+jury3aJKtlA7SsvOBIV2UByPfksG1PqPYZ4SHgQYCT7jnFoDbkzYj3Rl5Qak3Ql4fxfLjIDDXdyyQyL8VIEIZE6yDrY+oi0FQ3IJ2JAoGBAOTa6fjhAR6yAp0hsyxzjmHLxIJL2Qo7bn/dDxz8pGLsANzRyp/2ui27IrGS3YJaEinWCqvUf49jV4qaSzKkyKA+r224xAyV/VeQFSWV6T2XYPuJuzGyj4dUT6P18e4saiUcVZCiW7b3LqdSOsGOP78tJ1NiYJuBbCTHCW1FZMwzAoGBANI4m2tvmpdIU6PpOHapEchU08HT1fqNwoP6lh+4Ki77qR5Jv0brJWUmXPB1kmVaBEKsKD3shLK77r0VjdlW2fDYg0BlCFS48I0BHvCIKPRR5Csc511d4RckK/tuwKBKM8kpOHseXu7WTIjJhKBky1Kx2UetgW2MUMNtV6Xc3BQTAoGBAOMfg471NxhQkBmDxX87G7Zg9SBeyALIosyRBhu9fCOUlvKP3mlNAs655x+WYvGoFFiizSplFUenzsyGflS/h0DJlW7uLNtUy/3nx6Tql3UB1EUvFrGmxZ0IBpXxU24NdDZqXVzSVPVLcWirShan5woDdQDjJH+QPVRumCNy+CtTAoGBAIcjoLXWDtkFw87xFD0jvqy7600E8t+Y1dyl8G//og6F9VflLLNYYre9i62Ax1WkIm0B4vS74SpNKdIf0wpOjNgJN4bj7BEaunjKqasSvNEi+7zDXfBlc1e/Bw8hSW9BhDzi5M4w3fJHjPe6JCo/4X0Nm5I6daOIujqHRfr4GfpFAoGBANulUzdDUZal6KfcRLwxxRxJe2ibzh2bN4ShV8lvpUW93cVcX2jy4sIZL5CDTlkUaUqcx8/IGMw5fVwUk7SH9px080ghqn9YrzIJ5G9eVElISHZtr+Z34upse8oAtu2WoVlB6EOnmS7RHnJhkHMANe0fenwQyJnDUCW04f5lOW/I",

    //异步通知地址
    'notify_url' => "http://pay.gzmod.com.cn/alinotify",

    //同步跳转
    'return_url' => "http://lixiang.gzmod.com.cn/Alihomepage",

    //编码格式
    'charset' => "UTF-8",

    //签名方式
    'sign_type'=>"RSA2",

    //支付宝网关
    'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAu+4uwe3yvQ/nwwdvj7XL
x5f2WgXRkl1Zpssu1m/PCrJQzV2SqGbQdqs0yVDSKAxAxD9cAuKP/1Dz6xkqQ6U+
b+foX+tYgC7JmmjtN1e/qBuJ9tvh+mbO+Qjbjijr2u8hr+/4ggkbaN7XQRQUmlv/
dDCwMrcJJRIAziCiZPxOGwHtB0YjbgztH6+x69J9JI2MKydEjNbaidgGCgN6bcFd
ooRND+Q0s1v7h2j+uhZN26Sn07UjVKtlPhAO4iOxrUo9Ou0oCJjvJrKlcWPAs8Tt
E62bIQAxN6cW3I/LDG+IkS2h76xbNmOYEUUINyyvmZuo1xknNoXz8nUPIHCCVkMj
yQIDAQAB",

);
