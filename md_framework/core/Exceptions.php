<?php
/**
 * CodeIgniter
 * An open source application development framework for PHP 5.1.6 or newer
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
 * Exceptions Class
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Exceptions
 * @author Neowei
 * @link http://codeigniter.com/user_guide/libraries/exceptions.html
 */
class CI_Exceptions {
    /**
     * Nesting level of the output buffering mechanism
     * 
     * @var int
     * @access public
     */
    public $ob_level;
    
    /**
     * List if available error levels
     * 
     * @var array
     * @access public
     */
    public $levels = array(
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parsing Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Runtime Notice' 
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->ob_level = ob_get_level();
        $this->log = &get_log();
        // Note: Do not log messages from this constructor.
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Exception Logger
     * This function logs PHP generated error messages
     * 
     * @access private
     * @param string	the error severity
     * @param string	the error string
     * @param string	the error filepath
     * @param string	the error line number
     * @return string
     */
    public function log_exception($severity, $message, $filepath, $line) {
        $msg = ((!isset($this->levels[$severity])) ? $severity : $this->levels[$severity]);
        switch ($severity) {
        case E_ERROR :
        case E_CORE_ERROR :
        case E_COMPILE_ERROR :
        case E_USER_ERROR :
            $this->log->log_error('严重: ' . $msg . '  --> ' . $message . ' ' . $filepath . ' ' . $line, TRUE);
            break;
        case E_PARSE :
        case E_NOTICE :
        case E_USER_NOTICE :
        case E_STRICT :
            $this->log->log_notice('注意: ' . $msg . '  --> ' . $message . ' ' . $filepath . ' ' . $line, TRUE);
            break;
        case E_WARNING :
        case E_CORE_WARNING :
        case E_COMPILE_WARNING :
        case E_USER_WARNING :
            $this->log->log_warn('警告: ' . $msg . '  --> ' . $message . ' ' . $filepath . ' ' . $line, TRUE);
            break;
        default :
            $this->log->log_error('未知: ' . $msg . '  --> ' . $message . ' ' . $filepath . ' ' . $line, TRUE);
            break;
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * 404 Page Not Found Handler
     * 
     * @access private
     * @param string	the page
     * @param bool	log error yes/no
     * @return string
     */
    public function show_404($page = '', $log_error = TRUE) {
        if (is_cli()) {
            $heading = 'Not Found';
            $message = 'The controller/method pair you requested was not found.';
        } else {
            $heading = "404 页面找不到了";
            $message = "访问请求的页面找不到.";
        }
        
        // By default we log this, but allow a dev to skip it
        if ($log_error) {
            $this->log->log_error('404 页面找不到了 --> ' . $page);
        }
        
        echo $this->show_error($heading, $message, 'error_404', 404);
        exit(4);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * General Error Page
     * This function takes an error message as input
     * (either as a string or an array) and displays
     * it using the specified template.
     * 
     * @access private
     * @param string	the heading
     * @param string	the message
     * @param string	the template name
     * @param int		the status code
     * @return string
     */
    public function show_error($heading, $message, $template = 'error_general', $status_code = 500) {
        set_status_header($status_code);
        
        $message = '<p>' . (is_array($message) ? implode('</p><p>', $message) : $message) . '</p>';
        
        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }
        ob_start();
        include (DEFAULT_SYSTEM_PATH . 'errors/' . $template . '.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
    
    // --------------------------------------------------------------------
    public function show_exception(Exception $exception) {
        $message = $exception->getMessage();
        if (empty($message)) {
            $message = '(null)';
        }
        
        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }
        
        ob_start();
        include (DEFAULT_SYSTEM_PATH . 'errors/error_exception.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }

    /**
     * Native PHP error handler
     * 
     * @access private
     * @param string	the error severity
     * @param string	the error string
     * @param string	the error filepath
     * @param string	the error line number
     * @return string
     */
    public function show_php_error($severity, $message, $filepath, $line) {
        $severity = (!isset($this->levels[$severity])) ? $severity : $this->levels[$severity];
        
        $filepath = str_replace("\\", "/", $filepath);
        
        // For safety reasons we do not show the full file path
        if (FALSE !== strpos($filepath, '/')) {
            $x = explode('/', $filepath);
            $filepath = $x[count($x) - 2] . '/' . end($x);
        }
        
        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }
        ob_start();
        include (DEFAULT_SYSTEM_PATH . 'errors/error_php.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }
}
// END Exceptions Class

/* End of file Exceptions.php */
/* Location: ./system/core/Exceptions.php */