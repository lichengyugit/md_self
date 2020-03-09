<?php
class CI_Common_unrepeat {
    // 方便在自定义类库中使用CI功能 同$this，是一个super object
    var $CI;
    var $log;
    // 存储token的session key名
    public $key = 'form_token';

    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->config->load('config');
        $this->log = & get_log();
        $this->log->log_debug('Common_unrepeat class be initialized');
    }

    public function createToken($inputModel = FALSE, $tag = 'form') {
        // 生成Token
        $encryptionKey = $this->CI->config->item('encryption_key');
        $token = md5(uniqid($encryptionKey));
        
        $tokens = $this->CI->session->userdata($this->key);
        if (!$tokens || !is_array($tokens)) {
            $tokens = array();
        }
        $tokens[$tag] = $token;
        
        // 存入session
        $this->CI->session->set_userdata($this->key, $tokens);
        return $inputModel ? '<input type="hidden" name="' . $this->key . '" value="' . $tag . '-' . $token . '">' : $token;
    }

    public function isRepeat($token) {
        $token = explode('-', $token);
        $_tokens = $this->CI->session->userdata($this->key);
        
        if ($_tokens === FALSE || !isset($_tokens[$token[0]]) || $_tokens[$token[0]] != $token[1]) { 
            // 重复提交
            return FALSE;
        } else { 
            // 非重复提交
            if (isset($_POST[$this->key])) {
                unset($_POST[$this->key]);
            } else if (isset($_GET[$this->key])) {
                unset($_GET[$this->key]);
            }
            unset($_tokens[$token[0]]);
            
            $this->CI->session->unset_userdata($this->key);
            $this->CI->session->userdata($this->key, $_tokens);
            return TRUE;
        }
    }
}