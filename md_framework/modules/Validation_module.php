<?php
class Validation_module {
    private $CI;
    private $log;

    public function __construct() {
        $this->CI = & get_instance();
        $this->log = & get_config();
    }

    public function checkRepeat() {
        if (!UN_REPEAT) {
            return TRUE;
        }
        
        $this->CI->load->library('Common_unrepeat');
        
        $hasToken = $this->input->post($this->CI->common_unrepeat->key);
        
        if ($hasToken) {
            return $this->CI->common_unrepeat->isRepeat($hasToken);
        }
        return FALSE;
    }
}