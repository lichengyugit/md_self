<?php
class CI_Common_util {
    private $CI;
    private $log;

    public function __construct() {
        $this->log = & get_log();
        $this->CI = & get_instance();
        $this->log->log_trace('Common_util class be initialized');
    }

    public function getCurrentUrl() {
        $url = PROTOCOL . '://' . $_SERVER['HTTP_HOST'];
        return $url . $this->CI->uri->uri_string();
    }
}