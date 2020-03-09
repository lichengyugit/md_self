<?php
class CI_Common_restful {
    var $CI;
    var $log;

    public function __construct() {
        $this->CI = & get_instance();
        $this->log = & get_log();
        $this->log->log_debug('Common_restful class be initialized');
    }

    public function sendPost($url, $params = '') {
       
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(
                'Content-type: text/json' 
        ));
        
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query($params));
        $file_contents = curl_exec($curlHandle);
        curl_close($curlHandle);
        return $file_contents;
    }

    public function sendGet($url, $param = '') {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(
                'Content-type: text/json' 
        ));
        curl_setopt($curlHandle, CURLOPT_HTTPGET, true);
        $file_contents = curl_exec($curlHandle);
        curl_close($curlHandle);
        return $file_contents;
    }

    public function sendPut($url, $params = '') {

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(
                'Content-type: text/json' 
        ));
        
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query($params));
        $file_contents = curl_exec($curlHandle);
        curl_close($curlHandle);
        return $file_contents;
    }

    public function sendDelete($url, $params) {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(
                'Content-type: text/json' 
        ));
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $params);
        $file_contents = curl_exec($curlHandle);
        curl_close($curlHandle);
        return $file_contents;
    }
}
