<?php

/**
 * CodeIgniter
 * 
 * @package CodeIgniter
 * @author Neowei
 * @copyright Copyright (c) 2015 - 2025, aqdog, Inc.
 * @license http://codeigniter.com/user_guide/license.html
 * @link http://www.aqdog.com
 * @since Version 1.0
 */

// ------------------------------------------------------------------------

/**
 * Common Functions
 * Loads the base classes and executes the request.
 * 
 * @package CodeIgniter
 * @subpackage codeigniter
 * @category Common Functions
 * @author Neowei
 * @link http://codeigniter.com/user_guide/
 */

// ------------------------------------------------------------------------

/**
 * Determines if the current version of PHP is greater then the supplied value
 * Since there are a few places where we conditionally test for PHP > 5
 * we'll set a static variable.
 * 
 * @access public
 * @param string
 * @return bool if the current version is $version or higher
 */
function is_php($version = '5.0.0') {
    static $_is_php;
    $version = (string)$version;
    
    if (!isset($_is_php[$version])) {
        $_is_php[$version] = (version_compare(PHP_VERSION, $version) < 0) ? FALSE : TRUE;
    }
    
    return $_is_php[$version];
}

// ------------------------------------------------------------------------

/**
 * Tests for file writability
 * is_writable() returns TRUE on Windows servers when you really can't write to
 * the file, based on the read-only attribute.
 * is_writable() is also unreliable
 * on Unix servers if safe_mode is on.
 * 
 * @access private
 * @return void
 */
function is_really_writable($file) {
    // If we're on a Unix server with safe_mode off we call is_writable
    if (DIRECTORY_SEPARATOR == '/' and @ini_get("safe_mode") == FALSE) {
        return is_writable($file);
    }
    
    // For windows servers and safe_mode "on" installations we'll actually
    // write a file then read it. Bah...
    if (is_dir($file)) {
        $file = rtrim($file, '/') . '/' . md5(mt_rand(1, 100) . mt_rand(1, 100));
        
        if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE) {
            return FALSE;
        }
        
        fclose($fp);
        @chmod($file, DIR_WRITE_MODE);
        @unlink($file);
        return TRUE;
    } elseif (!is_file($file) or ($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE) {
        return FALSE;
    }
    
    fclose($fp);
    return TRUE;
}

// ------------------------------------------------------------------------

/**
 * Class registry
 * This function acts as a singleton.
 * If the requested class does not
 * exist it is instantiated and set to a static variable. If it has
 * previously been instantiated the variable is returned.
 * 
 * @access public
 * @param string the class name being requested
 * @param string the directory where the class should be found
 * @param string the class name prefix
 * @return object
 *
 */
function &load_class($class, $directory = 'libraries', $prefix = 'CI_') {
    static $_classes = array();
    
    // Does the class exist? If so, we're done...
    if (isset($_classes[$class])) {
        return $_classes[$class];
    }
    
    $name = FALSE;
    if ($class == 'Log') {
        if (file_exists(DEFAULT_LOG_PATH . $class . '.php')) {
            $name = $class;
            if (class_exists($name) === FALSE) {
                require (DEFAULT_LOG_PATH . DS . $class . '.php');
            }
        }
    } else {
        // Look for the class first in the local application/libraries folder
        // then in the native system/libraries folder
        foreach (array(
                BASEPATH,
                DEFAULT_SYSTEM_PATH,
                APPPATH 
        ) as $path) {
            if (file_exists($path . $directory . DS . $class . '.php')) {
                $name = $prefix . $class;
                if (class_exists($name) === FALSE) {
                    require ($path . $directory . DS . $class . '.php');
                }
                break;
            }
        }
        // Is the request a class extension? If so we load it too
        if (file_exists(APPPATH . $directory . '/' . config_item('subclass_prefix') . $class . '.php')) {
            $name = config_item('subclass_prefix') . $class;
            
            if (class_exists($name) === FALSE) {
                require (APPPATH . $directory . '/' . config_item('subclass_prefix') . $class . '.php');
            }
        }
    }
    // Did we find the class?
    if ($name === FALSE) {
        // Note: We use exit() rather then show_error() in order to avoid a
        // self-referencing loop with the Excptions class
        exit('无法加载指定的类  Unable to locate the specified class: ' . $class . '.php');
    }
    
    // Keep track of what we just loaded
    is_loaded($class);
    
    $_classes[$class] = new $name();
    return $_classes[$class];
}
// --------------------------------------------------------------------

/**
 * Keeps track of which libraries have been loaded.
 * This function is
 * called by the load_class() function above
 * 
 * @access public
 * @return array
 *
 */
function &is_loaded($class = '') {
    static $_is_loaded = array();
    
    if ($class != '') {
        $_is_loaded[strtolower($class)] = $class;
    }
    
    return $_is_loaded;
}

/**
 * Is CLI?
 * Test to see if a request was made from the command line.
 * 
 * @return bool
 */
function is_cli() {
    return (PHP_SAPI === 'cli' or defined('STDIN'));
}
// ------------------------------------------------------------------------

/**
 * Loads the main config.php file
 * This function lets us grab the config file even if the Config class
 * hasn't been instantiated yet
 * 
 * @access private
 * @return array
 *
 */
function &get_config() {
    static $_config;
    
    if (isset($_config)) {
        return $_config[0];
    }
    $file_path = DEFAULT_CONFIG_PATH . '/config.php';
    
    // Fetch the config file
    if (!file_exists($file_path)) {
        exit('配置文件不存在 The configuration file does not exist.');
    }
    
    $config = include ($file_path);
    
    // Does the $config array exist in the file?
    if (!isset($config) or !is_array($config)) {
        exit('你的配置文件没有内容 Your config file does not appear to be formatted correctly.');
    }
    
    $_config[0] = & $config;
    return $_config[0];
}

function &get_log() {
    static $_log;
    if (isset($_log)) {
        return $_log[0];
    }
    
    $_log[0] = & load_class('Log');
    return $_log[0];
}

// ------------------------------------------------------------------------

/**
 * Returns the specified config item
 * 
 * @access public
 * @return mixed
 *
 */
function config_item($item) {
    static $_config;
    
    if (empty($_config)) {
        // references cannot be directly assigned to static variables, so we use an array
        $_config[0] = & get_config();
    }
    return isset($_config[0][$item]) ? $_config[0][$item] : NULL;
}

function &get_mimes() {
    static $_mimes;
    if (empty($_mimes)) {
        if (file_exists(DEFAULT_CONFIG_PATH . 'mimes.php')) {
            $_mimes = include (DEFAULT_CONFIG_PATH . 'mimes.php');
        }
    }
    return $_mimes;
}
// ------------------------------------------------------------------------

/**
 * Error Handler
 * This function lets us invoke the exception class and
 * display errors using the standard error template located
 * in application/errors/errors.php
 * This function will send the error page directly to the
 * browser and exit.
 * 
 * @access public
 * @return void
 *
 */
function show_error($message, $status_code = 500, $heading = 'An Error Was Encountered') {
    $status_code = abs($status_code);
    if ($status_code < 100) {
        $exit_status = $status_code + 9; // 9 is EXIT__AUTO_MIN
        if ($exit_status > 125) // 125 is EXIT__AUTO_MAX
{
            $exit_status = 1; // EXIT_ERROR
        }
        
        $status_code = 500;
    } else {
        $exit_status = 1; // EXIT_ERROR
    }
    
    $_error = & load_class('Exceptions', 'core');
    echo $_error->show_error($heading, $message, 'error_general', $status_code);
    exit($exit_status);
}
// ------------------------------------------------------------------------

/**
 * 404 Page Handler
 * This function is similar to the show_error() function above
 * However, instead of the standard error template it displays
 * 404 errors.
 * 
 * @access public
 * @return void
 *
 */
function redirect($uri = '', $method = 'location', $http_response_code = 302) {
    if (!preg_match('#^https?://#i', $uri)) {
        $uri = BASEURL . '/' . $uri;
    }
    switch ($method) {
    case 'refresh' :
        header("Refresh:0;url=" . $uri);
        break;
    default :
        header("Location: " . $uri, TRUE, $http_response_code);
        break;
    }
    if (php_sapi_name() !== 'cli' or !defined('STDIN')) {
        exit();
    }
}

function show_404($page = '', $log_error = TRUE) {
    get_log()->log_error('404 页面找不到了 --> ' . $page);
    redirect(BASE_URL . '/error404');
    // $_error = & load_class('Exceptions', 'core');
    // $_error->show_404($page, $log_error);
    // exit();
}

// ------------------------------------------------------------------------

/**
 * Set HTTP Status Header
 * 
 * @access public
 * @param int the status code
 * @param string
 * @return void
 */
function set_status_header($code = 200, $text = '') {
    if (is_cli()) {
        return;
    }
    
    if (empty($code) or !is_numeric($code)) {
        show_error('Status codes must be numeric', 500);
    }
    
    if (empty($text)) {
        is_int($code) or $code = (int)$code;
        $stati = array(
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                307 => 'Temporary Redirect',
                
                400 => 'Bad Request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                422 => 'Unprocessable Entity',
                
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported' 
        );
        
        if (isset($stati[$code])) {
            $text = $stati[$code];
        } else {
            show_error('没有可用的状态文本 请检查您的状态码或提供自己的消息文本 No status text available.  Please check your status code number or supply your own message text.', 500);
        }
    }
    
    if (strpos(PHP_SAPI, 'cgi') === 0) {
        header('Status: ' . $code . ' ' . $text, TRUE);
    } else {
        $server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        header($server_protocol . ' ' . $code . ' ' . $text, TRUE, $code);
    }
}
// --------------------------------------------------------------------
function _error_handler($severity, $message, $filepath, $line) {
    $is_error = (((E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);
    // When an error occurred, set the status header to '500 Internal Server Error'
    // to indicate to the client something went wrong.
    // This can't be done within the $_error->show_php_error method because
    // it is only called when the display_errors flag is set (which isn't usually
    // the case in a production environment) or when errors are ignored because
    // they are above the error_reporting threshold.
    if ($is_error) {
        set_status_header(500);
    }
    
    // Should we ignore the error? We'll get the current error_reporting
    // level and add its bits with the severity bits to find out.
    if (($severity & error_reporting()) !== $severity) {
        return;
    }
    
    $_error = & load_class('Exceptions', 'core');
    $_error->log_exception($severity, $message, $filepath, $line);
    
    // Should we display the error?
    if (str_ireplace(array(
            'off',
            'none',
            'no',
            'false',
            'null' 
    ), '', ini_get('display_errors'))) {
        $_error->show_php_error($severity, $message, $filepath, $line);
    }
    // If the error is fatal, the execution of the script should be stopped because
    // errors can't be recovered from. Halting the script conforms with PHP's
    // default error handling. See http://www.php.net/manual/en/errorfunc.constants.php
    if ($is_error) {
        exit(1); // EXIT_ERROR
    }
}

function _shutdown_handler() {
    $last_error = error_get_last();
    if (isset($last_error) && ($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))) {
        _error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
    }
}

/**
 * Exception Handler
 * This is the custom exception handler that is declaired at the top
 * of Codeigniter.php.
 * The main reason we use this is to permit
 * PHP errors to be logged in our own log files since the user may
 * not have access to server logs. Since this function
 * effectively intercepts PHP errors, however, we also need
 * to display errors based on the current error_reporting level.
 * We do that with the use of a PHP error template.
 * 
 * @access private
 * @return void
 *
 */
function _exception_handler($exception) {
    $_error = & load_class('Exceptions', 'core');
    $_error->log_exception('error', 'Exception: ' . $exception->getMessage(), $exception->getFile(), $exception->getLine());
    
    // Should we display the error?
    if (str_ireplace(array(
            'off',
            'none',
            'no',
            'false',
            'null' 
    ), '', ini_get('display_errors'))) {
        $_error->show_exception($exception);
    }
    exit(1); // EXIT_ERROR
}

/**
 * Remove Invisible Characters
 * This prevents sandwiching null characters
 * between ascii characters, like Java\0script.
 * 
 * @access public
 * @param string
 * @return string
 */
function remove_invisible_characters($str, $url_encoded = TRUE) {
    $non_displayables = array();
    
    // every control character except newline (dec 10),
    // carriage return (dec 13) and horizontal tab (dec 09)
    if ($url_encoded) {
        $non_displayables[] = '/%0[0-8bcef]/'; // url encoded 00-08, 11, 12, 14, 15
        $non_displayables[] = '/%1[0-9a-f]/'; // url encoded 16-31
    }
    
    $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'; // 00-08, 11, 12, 14-31, 127
    
    do {
        $str = preg_replace($non_displayables, '', $str, -1, $count);
    } while($count);
    
    return $str;
}

/**
 * Returns HTML escaped variable
 * 
 * @access public
 * @param mixed
 * @return mixed
 *
 */
function html_escape($var, $double_encode = TRUE) {
    if (empty($var)) {
        return $var;
    }
    if (is_array($var)) {
        return array_map('html_escape', $var, array_fill(0, count($var), $double_encode));
    }
    return htmlspecialchars($var, ENT_QUOTES, config_item('charset'), $double_encode);
}

function M_Mysqli() {
    return M('mysqli');
}

function M_Mysqli_Class($path, $class) {
    get_log()->log_debug('通过M_Class方法获取了模型实例' . $path . '/' . $class);
    return M_Mysqli()->getModelByClass($path, $class);
}

function M($dbDriver = 'mysqli') {
    static $_models;
    if (isset($_models[$dbDriver])) {
        return $_models[$dbDriver];
    }
    
    // if (!defined('Models_Type') && !$isRemote) {
    require_once DEFAULT_DAO_PATH . 'Db/Db_Model.php';
    require_once DEFAULT_DAO_PATH . 'Db/Models.php';
    $_models[$dbDriver] = Models::getInstance();
    Models::$db_driver = $dbDriver;
    get_log()->log_trace('装载 Models 成功');
    // } else {
    // require_once DEFAULT_DAO_PATH . 'Db/Api_Models.php';
    // $_models[$dbDriver] = &Api_Models::getInstance($dbDriver);
    // get_log()->log_trace('装载 Api_Models 成功');
    // }
    return $_models[$dbDriver];
}

function F() {
    static $_factory;
    if (isset($_factory)) {
        return $_factory[0];
    }
    require_once DEFAULT_SYSTEM_PATH . 'modules/Factory.php';
    $_factory[0] = &Factory::$f;
    get_log()->log_trace('装载 Module Factory 成功');
    return $_factory[0];
}

function xss_clean($str) {
    return get_instance()->security->xss_clean($str);
}

function function_usable($function_name) {
    static $_suhosin_func_blacklist;
    if (function_exists($function_name)) {
        if (!isset($_suhosin_func_blacklist)) {
            if (extension_loaded('suhosin')) {
                $_suhosin_func_blacklist = explode(',', trim(ini_get('suhosin.executor.func.blacklist')));
                
                if (!in_array('eval', $_suhosin_func_blacklist, TRUE) && ini_get('suhosin.executor.disable_eval')) {
                    $_suhosin_func_blacklist[] = 'eval';
                }
            } else {
                $_suhosin_func_blacklist = array();
            }
        }
        
        return !in_array($function_name, $_suhosin_func_blacklist, TRUE);
    }
    return FALSE;
}
/* End of file Common.php */
/* Location: ./system/core/Common.php */