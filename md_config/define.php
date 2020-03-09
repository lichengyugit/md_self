<?php
class DefineConfig {
    private $abName;

    public function __construct($abName) {
        error_reporting(E_ALL);
        $this->abName = $abName;
        define('IS_MOBLIE', $abName == 'm');
    }

    public function bootstrap() {
        $this->setEnvironmentAndDomain();
        $this->setVersion();
        $this->setNames();
        $this->setDefaultPath();
        $this->setFileModes();
        $this->setClientIp();
        $this->setOthers();
    }

    private function setEnvironmentAndDomain() {
        if (php_sapi_name() === 'cli' or defined('STDIN')) {
            $hostArray[0] = 'dev';
        } else {
            define('IS_HTTPS', isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off');
            define('PROTOCOL', IS_HTTPS ? 'https' : 'http');
            if (!isset($_SERVER["HTTP_HOST"])) {
                exit('host错误');
            }
            $hostArray = explode('.', $_SERVER["HTTP_HOST"]);
            if (count($hostArray) < 2) {
                exit('host错误');
            }
        }
        
        if ($hostArray[0] == $this->abName) {
            define('ENVIRONMENT', 'dev');
            
            define('DOMAIN', $this->abName . '.' . DOMAIN_HOST);
            define('RESOURCE_DOMAIN', 'r.' . DOMAIN_HOST);
            
            define('BASE_URL', PROTOCOL . '://' . $this->abName . '.' . DOMAIN_HOST);

            define('RESOURCE_URL', PROTOCOL . '://r.' . DOMAIN_HOST);
            define('PIC_URL', PROTOCOL . '://pic.' . DOMAIN_HOST);
            define('SCAN_URL', PROTOCOL . '://scan.' . DOMAIN_HOST . ':8080');
            
            error_reporting(0);
        } else {
            define('ENVIRONMENT', $hostArray[0]);
            define('ML_URL', PROTOCOL.'://47.100.19.81/TESTDogWood_api/wechat/wechat/'.DOMAIN_HOST);
            define('DOMAIN', ENVIRONMENT . '.' . $this->abName . '.' . DOMAIN_HOST);
            define('RESOURCE_DOMAIN', ENVIRONMENT . '.r.' . DOMAIN_HOST);
            
            define('BASE_URL', PROTOCOL . '://' . DOMAIN);
            define('RESOURCE_URL', PROTOCOL . '://' . RESOURCE_DOMAIN);
            define('PIC_URL', 'http://pic.gzmod.com.cn');
//            define('PIC_URL', PROTOCOL . '://' . ENVIRONMENT . '.pic.' . DOMAIN_HOST);
            define('SCAN_URL', PROTOCOL . '://' . ENVIRONMENT . '.scan.' . DOMAIN_HOST . ':8080');
            
            error_reporting(E_ALL);
        }
        if (ENVIRONMENT == 'dev') {
            define('IS_MIN_RESOURCE', '');
        } else {
            define('IS_MIN_RESOURCE', '.min');
        }
        define('COOKIE_DOMAIN', '.' . DOMAIN_HOST);
        define('MYSQL_READ_HOST', ENVIRONMENT . '.read.db.' . DOMAIN_HOST);
        define('MYSQL_WRITE_HOST', ENVIRONMENT . '.write.db.' . DOMAIN_HOST);
        define('REDIS_HOST', ENVIRONMENT . '.redis.' . DOMAIN_HOST);
        define('INSURE_HOST', 'insure.' . DOMAIN_HOST);
    }

    private function setVersion() {
        define('VERSION', '0.1.1');
        define('CI_VERSION_PATH', '1.0.0-neolite');
        define('CIUnit_Version', '1.0.0-neounit');
    }

    private function setNames() {
        define('APP_CONFIG_NAME', 'md_config');
        define('APP_AB_NAME', $this->abName);
        define('APP_VIEW_NAME', 'md_view');
        define('APP_CACHE_NAME', 'md_cache');
        define('APP_LANG_NAME', 'md_language');
        define('APP_SYSTEM_NAME', 'md_framework');
        define('APP_LOG_NAME', 'md_log');
        define('APP_DAO_NAME', 'md_dao');
    }

    private function setDefaultPath() {
        define('DS', DIRECTORY_SEPARATOR);
        define('APPPATH', ROOTPATH . APP_NAME . DS);
        define('DEFAULT_SYSTEM_PATH', ROOTPATH . APP_SYSTEM_NAME . DS);
        define('BASEPATH', DEFAULT_SYSTEM_PATH . 'core' . DS);
        define('DEFAULT_DAO_PATH', ROOTPATH . APP_DAO_NAME . DS);
        define('DEFAULT_CONFIG_PATH', ROOTPATH . APP_CONFIG_NAME . DS);
        define('DEFAULT_LANG_PATH', ROOTPATH . APP_LANG_NAME . DS);
        define('DEFAULT_CACHE_PATH', ROOTPATH . APP_CACHE_NAME . DS);
        define('DEFAULT_VIEW_PATH', ROOTPATH . APP_VIEW_NAME . DS);
        define('DEFAULT_LOG_PATH', ROOTPATH . APP_LOG_NAME . DS);
        define('WRITE_LOG_PATH', DEFAULT_LOG_PATH . APP_NAME . DS);
        
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') { // windows系统(开发用)
            define('UPLOADS_PATH', '\\\\192.168.1.77\\');
        } else if (strtoupper(substr(PHP_OS, 0, 6)) == 'DARWIN') {
            define('UPLOADS_PATH', '/Users/neowei');
        } else {
            define('UPLOADS_PATH', '/data/');
        }
        define('XML_PATH', UPLOADS_PATH . DS . 'xmlpath' . DS);
        define('VIDEOS_PATH', UPLOADS_PATH . 'videos' . DS);
        define('FILES_PATH', UPLOADS_PATH . 'files' . DS);
        define('PIC_SRC_PATH', UPLOADS_PATH . 'picture_src' . DS);
        define('MAIL_SRC_PATH', ROOTPATH . APP_SYSTEM_NAME . DS . 'libraries' . DS);
        define('MAIL_RESULT_PATH', DOMAIN . '/' . 'basicInformation' . '/' . 'basic' . '/' . 'emailResetUrl');
        define('MAIL_PASS_RESULT_PATH', DOMAIN . '/' . 'acount' . '/' . 'users' . '/' . 'checkUserEmailVerify');
        define('MOBILE_MAIL_PASS_RESULT_PATH', DOMAIN . '/' . 'basicInformation' . '/' . 'basic' . '/' . 'checkUserEmailVerify');
        define('CRO_IMG', "http://pic.cro.top");//cro图片url
        define('IMG_URL', "http://pic.gzmod.com.cn");
        define('MEMCACHE_IP', "127.0.0.1");
        define('MEMCACHE_PORT', "11211");

    }

    private function setFileModes() {
        define('FILE_READ_MODE', 0644);
        define('FILE_WRITE_MODE', 0666);
        define('DIR_READ_MODE', 0755);
        define('DIR_WRITE_MODE', 0777);
        define('FOPEN_READ', 'rb');
        define('FOPEN_READ_WRITE', 'r+b');
        define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb');
        define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b');
        define('FOPEN_WRITE_CREATE', 'ab');
        define('FOPEN_READ_WRITE_CREATE', 'a+b');
        define('FOPEN_WRITE_CREATE_STRICT', 'xb');
        define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');
    }

    private function setClientIp() {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
            $ip = getenv("REMOTE_ADDR");
        } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "unknown";
        }
        define('CLIENTIP', $ip);
    }

    private function setOthers() {
        if (ENVIRONMENT == 'dev') {
            define('LOG_LEVEL', 8);
        } else if (ENVIRONMENT == 'test') {
            define('LOG_LEVEL', 6);
        } else {
            define('LOG_LEVEL', 8);
        }
        define('OPEN_LOCK', FALSE);
        define('EXT', '.php');
        define('UN_REPEAT', TRUE); // 防重复提交
        define('T1', microtime(TRUE));
        define('USE_CACHE', FALSE);
        define('CACHE_TYPE', 'redis');
        define('ADAMIN_LOGIN_TIMEOUT', 3600);
        define('IS_POST', $_SERVER['REQUEST_METHOD'] == "POST" ? TRUE : FALSE);
        define('IS_GET', $_SERVER['REQUEST_METHOD'] == "GET" ? TRUE : FALSE);
        define('IS_AJAX', (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? TRUE : FALSE);
        define("FIXCOMMIS", "600");//修哥提成（分为单位）
        define("AGENTCOMMIS", "290");//代理商提成 （分为单位）
        define("ISSIGN", FALSE);
    }
}
