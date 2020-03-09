<?php
class CI_Common_Pdf {
    var $CI;
    var $log;

    public function __construct() {
        $this->CI = & get_instance();
        $this->log = & get_log();
        $this->log->log_debug('Common_Pdf class be initialized');
    }

    public function wkhtmlToPdf($html, $pdf) {
        $cmd = 'wkhtmltopdf ' . $html . ' ' . $pdf;
        system($cmd);
    }
}