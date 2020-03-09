<?php
class Ses_module {

    public function __construct() {
        $this->log = & get_log();
        $this->CI = & get_instance();
    }
     
    function send_email($toEmail,$subject,$message){
       /*require_once (MAIL_SRC_PATH.'class.mail.php');
       $smtpserver = "smtp.jw-assoc.com";
       $smtpserverport = 25;
       $smtpusermail = "aqdog@jw-assoc.com";
       $smtpmailto = $email;
       $smtpuser = "aqdog@jw-assoc.com";
       $smtppass = "jw-assoc2016";
       $mailtype = "HTML";
       $smtp = new smtp($smtpserver, $smtpserverport,true,$smtpuser,$smtppass);
       $smtp->debug = false;
       return $smtp->sendmail($smtpmailto, $smtpusermail, $subject,$body,$mailtype);*/
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
       // echo $this->CI->email->print_debugger();        //返回包含邮件内容的字符串，包括EMAIL头和EMAIL正文。用于调试。
    }
}
