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
 * Input Class
 * Pre-processes global input data for security
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Input
 * @author Neowei
 * @link http://codeigniter.com/user_guide/libraries/input.html
 */
class CI_Input {
    
    /**
     * IP address of the current user
     * 
     * @var string
     */
    protected $ip_address = FALSE;
    /**
     * If FALSE, then $_GET will be set to an empty array
     * 
     * @var bool
     */
    protected $_allow_get_array = TRUE;
    /**
     * If TRUE, then newlines are standardized
     * 
     * @var bool
     */
    protected $_standardize_newlines = TRUE;
    /**
     * Determines whether the XSS filter is always active when GET, POST or COOKIE data is encountered
     * Set automatically based on config setting
     * 
     * @var bool
     */
    protected $_enable_xss = FALSE;
    /**
     * Enables a CSRF cookie token to be set.
     * Set automatically based on config setting
     * 
     * @var bool
     */
    protected $_enable_csrf = FALSE;
    /**
     * List of all HTTP request headers
     * 
     * @var array
     */
    protected $headers = array();
    protected $_raw_input_stream;
    /**
     * Parsed input stream data
     * Parsed from php://input at runtime
     * 
     * @see CI_Input::input_stream()
     * @var array
     */
    protected $_input_stream;
    protected $security;
    protected $uni;
    public $log;

    /**
     * Constructor
     * Sets whether to globally enable the XSS processing
     * and whether to allow the $_GET array
     * 
     * @return void
     */
    public function __construct() {
        $this->log = &get_log();
        
        $this->_allow_get_array = (config_item('allow_get_array') === TRUE);
        $this->_enable_xss = (config_item('global_xss_filtering') === TRUE);
        $this->_enable_csrf = (config_item('csrf_protection') === TRUE);
        $this->_standardize_newlines = (bool)config_item('standardize_newlines');
        
        $this->security = & load_class('Security', 'core');
        
        // Do we need the UTF-8 class?
        if (UTF8_ENABLED === TRUE) {
            $this->uni = & load_class('Utf8', 'core');
        }
        
        $this->log->log_trace('输入类实例化 Input Class Initialized');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Fetch from array
     * This is a helper function to retrieve values from global arrays
     * 
     * @access private
     * @param array
     * @param string
     * @param bool
     * @return string
     */
    protected function _fetch_from_array(&$array, $index = NULL, $xss_clean = NULL) {
        is_bool($xss_clean) or $xss_clean = $this->_enable_xss;
        
        // If $index is NULL, it means that the whole $array is requested
        isset($index) or $index = array_keys($array);
        
        // allow fetching multiple keys at once
        if (is_array($index)) {
            $output = array();
            foreach ($index as $key) {
                $output[$key] = $this->_fetch_from_array($array, $key, $xss_clean);
            }
            
            return $output;
        }
        
        if (isset($array[$index])) {
            $value = $array[$index];
        } elseif (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1)         // Does the index contain array notation
        {
            $value = $array;
            for ($i = 0; $i < $count; $i++) {
                $key = trim($matches[0][$i], '[]');
                if ($key === '')                 // Empty notation will return the value as array
                {
                    break;
                }
                
                if (isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    return NULL;
                }
            }
        } else {
            return NULL;
        }
        
        return ($xss_clean === TRUE) ? $this->security->xss_clean($value) : $value;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Fetch an item from the GET array
     * 
     * @access public
     * @param string
     * @param bool
     * @return string
     */
    public function get($index = NULL, $xss_clean = NULL) {
        return $this->_fetch_from_array($_GET, $index, $xss_clean);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Fetch an item from the POST array
     * 
     * @access public
     * @param string
     * @param bool
     * @return string
     */
    public function post($index = NULL, $xss_clean = NULL) {
        return $this->_fetch_from_array($_POST, $index, $xss_clean);
    }
    
    // --------------------------------------------------------------------
    public function post_get($index, $xss_clean = NULL) {
        return isset($_POST[$index]) ? $this->post($index, $xss_clean) : $this->get($index, $xss_clean);
    }

    /**
     * Fetch an item from either the GET array or the POST
     * 
     * @access public
     * @param string	The index key
     * @param bool	XSS cleaning
     * @return string
     */
    public function get_post($index, $xss_clean = NULL) {
        return isset($_GET[$index]) ? $this->get($index, $xss_clean) : $this->post($index, $xss_clean);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Fetch an item from the COOKIE array
     * 
     * @access public
     * @param string
     * @param bool
     * @return string
     */
    public function cookie($index = NULL, $xss_clean = NULL) {
        return $this->_fetch_from_array($_COOKIE, $index, $xss_clean);
    }
    
    // ------------------------------------------------------------------------
    /**
     * Fetch an item from the SERVER array
     * 
     * @access public
     * @param string
     * @param bool
     * @return string
     */
    public function server($index, $xss_clean = NULL) {
        return $this->_fetch_from_array($_SERVER, $index, $xss_clean);
    }
    
    // --------------------------------------------------------------------
    public function input_stream($index = NULL, $xss_clean = NULL) {
        // Prior to PHP 5.6, the input stream can only be read once,
        // so we'll need to check if we have already done that first.
        if (!is_array($this->_input_stream)) {
            // $this->raw_input_stream will trigger __get().
            parse_str($this->raw_input_stream, $this->_input_stream);
            is_array($this->_input_stream) or $this->_input_stream = array();
        }
        
        return $this->_fetch_from_array($this->_input_stream, $index, $xss_clean);
    }

    /**
     * Set cookie
     * Accepts six parameter, or you can submit an associative
     * array in the first parameter containing all the values.
     * 
     * @access public
     * @param mixed
     * @param string	the value of the cookie
     * @param string	the number of seconds until expiration
     * @param string	the cookie domain. Usually: .yourdomain.com
     * @param string	the cookie path
     * @param string	the cookie prefix
     * @param bool	true makes the cookie secure
     * @return void
     */
    public function set_cookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = FALSE, $httponly = FALSE) {
        if (is_array($name)) {
            // always leave 'name' in last place, as the loop will break otherwise, due to $$item
            foreach (array(
                    'value',
                    'expire',
                    'domain',
                    'path',
                    'prefix',
                    'secure',
                    'httponly',
                    'name' 
            ) as $item) {
                if (isset($name[$item])) {
                    $$item = $name[$item];
                }
            }
        }
        
        if ($prefix === '' && config_item('cookie_prefix') !== '') {
            $prefix = config_item('cookie_prefix');
        }
        
        if ($domain == '' && config_item('cookie_domain') != '') {
            $domain = config_item('cookie_domain');
        }
        
        if ($path === '/' && config_item('cookie_path') !== '/') {
            $path = config_item('cookie_path');
        }
        
        if ($secure === FALSE && config_item('cookie_secure') === TRUE) {
            $secure = config_item('cookie_secure');
        }
        
        if ($httponly === FALSE && config_item('cookie_httponly') !== FALSE) {
            $httponly = config_item('cookie_httponly');
        }
        
        if (!is_numeric($expire)) {
            $expire = time() - 86500;
        } else {
            $expire = ($expire > 0) ? time() + $expire : 0;
        }
        
        setcookie($prefix . $name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Fetch the IP Address
     * 
     * @return string
     */
    public function ip_address() {
        if ($this->ip_address !== FALSE) {
            return $this->ip_address;
        }
        
        $proxy_ips = config_item('proxy_ips');
        if (!empty($proxy_ips) && !is_array($proxy_ips)) {
            $proxy_ips = explode(',', str_replace(' ', '', $proxy_ips));
        }
        
        $this->ip_address = $this->server('REMOTE_ADDR');
        
        if ($proxy_ips) {
            foreach (array(
                    'HTTP_X_FORWARDED_FOR',
                    'HTTP_CLIENT_IP',
                    'HTTP_X_CLIENT_IP',
                    'HTTP_X_CLUSTER_CLIENT_IP' 
            ) as $header) {
                if (($spoof = $this->server($header)) !== NULL) {
                    // Some proxies typically list the whole chain of IP
                    // addresses through which the client has reached us.
                    // e.g. client_ip, proxy_ip1, proxy_ip2, etc.
                    sscanf($spoof, '%[^,]', $spoof);
                    
                    if (!$this->valid_ip($spoof)) {
                        $spoof = NULL;
                    } else {
                        break;
                    }
                }
            }
            
            if ($spoof) {
                for ($i = 0, $c = count($proxy_ips); $i < $c; $i++) {
                    // Check if we have an IP address or a subnet
                    if (strpos($proxy_ips[$i], '/') === FALSE) {
                        // An IP address (and not a subnet) is specified.
                        // We can compare right away.
                        if ($proxy_ips[$i] === $this->ip_address) {
                            $this->ip_address = $spoof;
                            break;
                        }
                        
                        continue;
                    }
                    
                    // We have a subnet ... now the heavy lifting begins
                    isset($separator) or $separator = $this->valid_ip($this->ip_address, 'ipv6') ? ':' : '.';
                    
                    // If the proxy entry doesn't match the IP protocol - skip it
                    if (strpos($proxy_ips[$i], $separator) === FALSE) {
                        continue;
                    }
                    
                    // Convert the REMOTE_ADDR IP address to binary, if needed
                    if (!isset($ip, $sprintf)) {
                        if ($separator === ':') {
                            // Make sure we're have the "full" IPv6 format
                            $ip = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($this->ip_address, ':')), $this->ip_address));
                            
                            for ($j = 0; $j < 8; $j++) {
                                $ip[$j] = intval($ip[$j], 16);
                            }
                            
                            $sprintf = '%016b%016b%016b%016b%016b%016b%016b%016b';
                        } else {
                            $ip = explode('.', $this->ip_address);
                            $sprintf = '%08b%08b%08b%08b';
                        }
                        
                        $ip = vsprintf($sprintf, $ip);
                    }
                    
                    // Split the netmask length off the network address
                    sscanf($proxy_ips[$i], '%[^/]/%d', $netaddr, $masklen);
                    
                    // Again, an IPv6 address is most likely in a compressed form
                    if ($separator === ':') {
                        $netaddr = explode(':', str_replace('::', str_repeat(':', 9 - substr_count($netaddr, ':')), $netaddr));
                        for ($i = 0; $i < 8; $i++) {
                            $netaddr[$i] = intval($netaddr[$i], 16);
                        }
                    } else {
                        $netaddr = explode('.', $netaddr);
                    }
                    
                    // Convert to binary and finally compare
                    if (strncmp($ip, vsprintf($sprintf, $netaddr), $masklen) === 0) {
                        $this->ip_address = $spoof;
                        break;
                    }
                }
            }
        }
        
        if (!$this->valid_ip($this->ip_address)) {
            return $this->ip_address = '0.0.0.0';
        }
        
        return $this->ip_address;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Validate IP Address
     * 
     * @access public
     * @param string
     * @param string	ipv4 or ipv6
     * @return bool
     */
    public function valid_ip($ip, $which = '') {
        switch (strtolower($which)) {
        case 'ipv4' :
            $which = FILTER_FLAG_IPV4;
            break;
        case 'ipv6' :
            $which = FILTER_FLAG_IPV6;
            break;
        default :
            $which = NULL;
            break;
        }
        return (bool)filter_var($ip, FILTER_VALIDATE_IP, $which);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * User Agent
     * 
     * @access public
     * @return string
     */
    public function user_agent($xss_clean = NULL) {
        return $this->_fetch_from_array($_SERVER, 'HTTP_USER_AGENT', $xss_clean);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Sanitize Globals
     * This function does the following:
     * Unsets $_GET data (if query strings are not enabled)
     * Unsets all globals if register_globals is enabled
     * Standardizes newline characters to \n
     * 
     * @access private
     * @return void
     */
    protected function _sanitize_globals() {
        // Is $_GET data allowed? If not we'll set the $_GET to an empty array
        if ($this->_allow_get_array === FALSE) {
            $_GET = array();
        } elseif (is_array($_GET) && count($_GET) > 0) {
            foreach ($_GET as $key=>$val) {
                $_GET[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
            }
        }
        
        // Clean $_POST Data
        if (is_array($_POST) && count($_POST) > 0) {
            foreach ($_POST as $key=>$val) {
                $_POST[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
            }
        }
        
        // Clean $_COOKIE Data
        if (is_array($_COOKIE) && count($_COOKIE) > 0) {
            // Also get rid of specially treated cookies that might be set by a server
            // or silly application, that are of no use to a CI application anyway
            // but that when present will trip our 'Disallowed Key Characters' alarm
            // http://www.ietf.org/rfc/rfc2109.txt
            // note that the key names below are single quoted strings, and are not PHP variables
            unset($_COOKIE['$Version'], $_COOKIE['$Path'], $_COOKIE['$Domain']);
            
            foreach ($_COOKIE as $key=>$val) {
                if (($cookie_key = $this->_clean_input_keys($key)) !== FALSE) {
                    $_COOKIE[$cookie_key] = $this->_clean_input_data($val);
                } else {
                    unset($_COOKIE[$key]);
                }
            }
        }
        
        // Sanitize PHP_SELF
        $_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);
        
        // CSRF Protection check
        if ($this->_enable_csrf === TRUE && !is_cli()) {
            $this->security->csrf_verify();
        }
        
        $this->log->log_trace('全局post和cookie数据已审核 Global POST and COOKIE data sanitized');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Clean Input Data
     * This is a helper function.
     * It escapes data and
     * standardizes newline characters to \n
     * 
     * @access private
     * @param string
     * @return string
     */
    protected function _clean_input_data($str) {
        if (is_array($str)) {
            $new_array = array();
            foreach (array_keys($str) as $key) {
                $new_array[$this->_clean_input_keys($key)] = $this->_clean_input_data($str[$key]);
            }
            return $new_array;
        }
        
        /*
         * We strip slashes if magic quotes is on to keep things consistent NOTE: In PHP 5.4 get_magic_quotes_gpc() will always return 0 and it will probably not exist in future versions at all.
         */
        if (!is_php('5.4') && get_magic_quotes_gpc()) {
            $str = stripslashes($str);
        }
        
        // Clean UTF-8 if supported
        if (UTF8_ENABLED === TRUE) {
            $str = $this->uni->clean_string($str);
        }
        
        // Remove control characters
        $str = remove_invisible_characters($str, FALSE);
        
        // Standardize newlines if needed
        if ($this->_standardize_newlines === TRUE) {
            return preg_replace('/(?:\r\n|[\r\n])/', PHP_EOL, $str);
        }
        
        return $str;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Clean Keys
     * This is a helper function.
     * To prevent malicious users
     * from trying to exploit keys we make sure that keys are
     * only named with alpha-numeric text and a few other items.
     * 
     * @access private
     * @param string
     * @return string
     */
    protected function _clean_input_keys($str, $fatal = TRUE) {
        if (!preg_match('/^[a-z0-9:_\/|-]+$/i', $str)) {
            if ($fatal === TRUE) {
                return FALSE;
            } else {
                set_status_header(503);
                echo 'Disallowed Key Characters.';
                exit(7); // EXIT_USER_INPUT
            }
        }
        
        // Clean UTF-8 if supported
        if (UTF8_ENABLED === TRUE) {
            return $this->uni->clean_string($str);
        }
        
        return $str;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Request Headers
     * In Apache, you can simply call apache_request_headers(), however for
     * people running other webservers the function is undefined.
     * 
     * @param bool XSS cleaning
     * @return array
     */
    public function request_headers($xss_clean = FALSE) {
        // If header is already defined, return it immediately
        if (!empty($this->headers)) {
            return $this->headers;
        }
        
        // In Apache, you can simply call apache_request_headers()
        if (function_exists('apache_request_headers')) {
            return $this->headers = apache_request_headers();
        }
        
        $this->headers['Content-Type'] = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : @getenv('CONTENT_TYPE');
        
        foreach ($_SERVER as $key=>$val) {
            if (sscanf($key, 'HTTP_%s', $header) === 1) {
                // take SOME_HEADER and turn it into Some-Header
                $header = str_replace('_', ' ', strtolower($header));
                $header = str_replace(' ', '-', ucwords($header));
                
                $this->headers[$header] = $this->_fetch_from_array($_SERVER, $key, $xss_clean);
            }
        }
        
        return $this->headers;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get Request Header
     * Returns the value of a single member of the headers class member
     * 
     * @param string		array key for $this->headers
     * @param boolean		XSS Clean or not
     * @return mixed on failure, string on success
     */
    public function get_request_header($index, $xss_clean = FALSE) {
        if (empty($this->headers)) {
            $this->request_headers();
        }
        
        if (!isset($this->headers[$index])) {
            return NULL;
        }
        
        return ($xss_clean === TRUE) ? $this->security->xss_clean($this->headers[$index]) : $this->headers[$index];
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Is ajax Request?
     * Test to see if a request contains the HTTP_X_REQUESTED_WITH header
     * 
     * @return boolean
     */
    public function is_ajax_request() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Is cli Request?
     * Test to see if a request was made from the command line
     * 
     * @return bool
     */
    public function is_cli_request() {
        return is_cli();
    }

    public function method($upper = FALSE) {
        return ($upper) ? strtoupper($this->server('REQUEST_METHOD')) : strtolower($this->server('REQUEST_METHOD'));
    }

    public function __get($name) {
        if ($name === 'raw_input_stream') {
            isset($this->_raw_input_stream) or $this->_raw_input_stream = file_get_contents('php://input');
            return $this->_raw_input_stream;
        } elseif ($name === 'ip_address') {
            return $this->ip_address;
        }
    }
}

/* End of file Input.php */
/* Location: ./system/core/Input.php */