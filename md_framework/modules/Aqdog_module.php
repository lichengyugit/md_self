<?php
class Aqdog_module {
    
    public function __construct() {
        $this->log = & get_log();
        $this->CI = & get_instance();
    }
    
    /**
     * 
     * @param string $content 发件人邮件的名称
     * @param string $toEmail 收件人邮箱
     * @param string $subject 邮件的主题
     * @param string $message 邮件正文部分
     */
    public function sendEmail($toEmail, $subject, $message)
    {
        $fromEmail = 'non-reply@aqdog.com';
        //以下设置Email参数
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'smtp.partner.outlook.cn';
        $config['smtp_user'] = $fromEmail;
        $config['smtp_pass'] = 'n0n-R3plY@8Q|)Og';
        $config['smtp_port'] = '25';
        $config['charset'] = 'utf-8';
        $config['smtp_timeout'] = '20';
        $config['mailtype'] = 'text';
        $config['wordwrap'] = TRUE;
        $config['smtp_crypto'] = 'tls';
        $config['crlf'] = PHP_EOL;
        $config['newline'] = PHP_EOL;
        //加载CI的email类
        $this->CI->load->library('email');
        $this->CI->email->initialize($config);
    
        //以下设置Email内容
        $this->CI->email->from($fromEmail);
        $this->CI->email->to($toEmail);
        $this->CI->email->subject($subject);
        $this->CI->email->message($message);
        $this->CI->email->send();
        //echo $this->CI->email->print_debugger();        //返回包含邮件内容的字符串，包括EMAIL头和EMAIL正文。用于调试。
    }
}
