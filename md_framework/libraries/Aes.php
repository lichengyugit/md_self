<?php
class CI_AES {

    function aes_encode($encode, $key) {
        $iv = 'KELISHI';
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, md5($key), $encode, MCRYPT_MODE_ECB, $iv));
    }

    function aes_decode($decode, $key) {
        $iv = 'KELISHI';
        $decode = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, md5($key), base64_decode($decode), MCRYPT_MODE_ECB, $iv);
        return trim($decode);
    }
}