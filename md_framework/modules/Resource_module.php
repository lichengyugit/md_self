<?php
class Resource_module {
    private $CI;
    private $log;

    public function __construct() {
        $this->log = & get_log();
        $this->CI = & get_instance();
    }

    public function setJsAndCss($jses = array(), $csses = array()) {
        $this->CI->smarty->assign('jses', $jses);
        $this->CI->smarty->assign('csses', $csses);
    }

    public function setTitle($title) {
        $this->CI->smarty->assign('title', $title);
    }

    public function setCkeditorJsAndCss() {
        $jses = array(
                "ckeditor" 
        );
        $csses = array(
                "content" 
        );
        $this->CI->smarty->assign('ckeditorJses', $jses);
        $this->CI->smarty->assign('ckeditorCsses', $csses);
    }

    public function setLocation($location) {
        $this->CI->smarty->assign('location', $location);
    }
}
