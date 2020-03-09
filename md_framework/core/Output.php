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

/**
 * Output Class
 * Responsible for sending final output to browser
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Output
 * @author Neowei
 * @link http://codeigniter.com/user_guide/libraries/output.html
 */
class CI_Output {
    
    /**
     * Current output string
     * 
     * @var string
     * @access protected
     */
    public $final_output;
    /**
     * Cache expiration time
     * 
     * @var int
     * @access protected
     */
    public $cache_expiration = 0;
    /**
     * List of server headers
     * 
     * @var array
     * @access protected
     */
    public $headers = array();
    /**
     * List of mime types
     * 
     * @var array
     * @access protected
     */
    public $mimes = array();
    protected $mime_type = 'text/html';
    /**
     * Determines wether profiler is enabled
     * 
     * @var book
     * @access protected
     */
    public $enable_profiler = FALSE;
    /**
     * Determines if output compression is enabled
     * 
     * @var bool
     * @access protected
     */
    protected $_zlib_oc = FALSE;
    /**
     * List of profiler sections
     * 
     * @var array
     * @access protected
     */
    protected $_compress_output = FALSE;
    protected $_profiler_sections = array();
    /**
     * Whether or not to parse variables like {elapsed_time} and {memory_usage}
     * 
     * @var bool
     * @access protected
     */
    protected $parse_exec_vars = TRUE;

    /**
     * Constructor
     */
    public function __construct() {
        $this->log = &get_log();
        $this->_zlib_oc = (bool)ini_get('zlib.output_compression');
        $this->_compress_output = ($this->_zlib_oc === FALSE && config_item('compress_output') === TRUE && extension_loaded('zlib'));
        $this->mimes = & get_mimes();
        $this->log->log_trace('输出类实例化 Output Class Initialized');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get Output
     * Returns the current output string
     * 
     * @access public
     * @return string
     */
    public function get_output() {
        return $this->final_output;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Output
     * Sets the output string
     * 
     * @access public
     * @param string
     * @return void
     */
    public function set_output($output) {
        $this->final_output = $output;
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Append Output
     * Appends data onto the output string
     * 
     * @access public
     * @param string
     * @return void
     */
    public function append_output($output) {
        $this->final_output .= $output;
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Header
     * Lets you set a server header which will be outputted with the final display.
     * Note: If a file is cached, headers will not be sent. We need to figure out
     * how to permit header data to be saved with the cache data...
     * 
     * @access public
     * @param string
     * @param bool
     * @return void
     */
    public function set_header($header, $replace = TRUE) {
        // If zlib.output_compression is enabled it will compress the output,
        // but it will not modify the content-length header to compensate for
        // the reduction, causing the browser to hang waiting for more data.
        // We'll just skip content-length in those cases.
        if ($this->_zlib_oc && strncasecmp($header, 'content-length', 14) === 0) {
            return $this;
        }
        
        $this->headers[] = array(
                $header,
                $replace 
        );
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Content Type Header
     * 
     * @access public
     * @param string	extension of the file we're outputting
     * @return void
     */
    public function set_content_type($mime_type, $charset = NULL) {
        if (strpos($mime_type, '/') === FALSE) {
            $extension = ltrim($mime_type, '.');
            
            // Is this extension supported?
            if (isset($this->mimes[$extension])) {
                $mime_type = & $this->mimes[$extension];
                
                if (is_array($mime_type)) {
                    $mime_type = current($mime_type);
                }
            }
        }
        
        $this->mime_type = $mime_type;
        
        if (empty($charset)) {
            $charset = config_item('charset');
        }
        
        $header = 'Content-Type: ' . $mime_type . (empty($charset) ? '' : '; charset=' . $charset);
        
        $this->headers[] = array(
                $header,
                TRUE 
        );
        return $this;
    }

    public function get_content_type() {
        for ($i = 0, $c = count($this->headers); $i < $c; $i++) {
            if (sscanf($this->headers[$i][0], 'Content-Type: %[^;]', $content_type) === 1) {
                return $content_type;
            }
        }
        return 'text/html';
    }
    // --------------------------------------------------------------------
    /**
     * Get Header
     * 
     * @param string $header_name
     * @return string
     */
    public function get_header($header) {
        // Combine headers already sent with our batched headers
        $headers = array_merge(
                // We only need [x][0] from our multi-dimensional array
                array_map('array_shift', $this->headers), headers_list());
        
        if (empty($headers) or empty($header)) {
            return NULL;
        }
        
        for ($i = 0, $c = count($headers); $i < $c; $i++) {
            if (strncasecmp($header, $headers[$i], $l = strlen($header)) === 0) {
                return trim(substr($headers[$i], $l + 1));
            }
        }
        
        return NULL;
    }
    // --------------------------------------------------------------------
    /**
     * Set HTTP Status Header
     * As of version 1.7.2, this is an alias for common function
     * set_status_header().
     * 
     * @param int $code (default: 200)
     * @param string $text
     * @return CI_Output
     */
    public function set_status_header($code = 200, $text = '') {
        set_status_header($code, $text);
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Enable/disable Profiler
     * 
     * @access public
     * @param bool
     * @return void
     */
    public function enable_profiler($val = TRUE) {
        $this->enable_profiler = is_bool($val) ? $val : TRUE;
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Profiler Sections
     * Allows override of default / config settings for Profiler section display
     * 
     * @access public
     * @param array
     * @return void
     */
    public function set_profiler_sections($sections) {
        if (isset($sections['query_toggle_count'])) {
            $this->_profiler_sections['query_toggle_count'] = (int)$sections['query_toggle_count'];
            unset($sections['query_toggle_count']);
        }
        
        foreach ($sections as $section=>$enable) {
            $this->_profiler_sections[$section] = ($enable !== FALSE);
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Cache
     * 
     * @access public
     * @param integer
     * @return void
     */
    public function cache($time) {
        $this->cache_expiration = is_numeric($time) ? $time : 0;
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Display Output
     * All "view" data is automatically put into this variable by the controller class:
     * $this->final_output
     * This function sends the finalized output data to the browser along
     * with any server headers and profile data. It also stops the
     * benchmark timer so the page rendering speed and memory usage can be shown.
     * 
     * @access public
     * @param string
     * @return mixed
     */
    public function _display($output = '') {
        // Note: We use globals because we can't use $CI =& get_instance()
        // since this function is sometimes called by the caching mechanism,
        // which happens before the CI super object is available.
        $BM = & load_class('Benchmark', 'core');
        $CFG = & load_class('Config', 'core');
        
        // Grab the super object if we can.
        if (class_exists('CI_Controller', FALSE)) {
            $CI = & get_instance();
        }
        
        // --------------------------------------------------------------------
        
        // Set the output data
        if ($output === '') {
            $output = & $this->final_output;
        }
        
        // --------------------------------------------------------------------
        
        // Do we need to write a cache file? Only if the controller does not have its
        // own _output() method and we are not dealing with a cache file, which we
        // can determine by the existence of the $CI object above
        if ($this->cache_expiration > 0 && isset($CI) && !method_exists($CI, '_output')) {
            $this->_write_cache($output);
        }
        
        // --------------------------------------------------------------------
        
        // Are there any server headers to send?
        if (count($this->headers) > 0) {
            foreach ($this->headers as $header) {
                @header($header[0], $header[1]);
            }
        }
        
        // --------------------------------------------------------------------
        $elapsed = $BM->elapsed_time('total_execution_time_start', 'total_execution_time_end');
        // Does the $CI object exist?
        // If not we know we are dealing with a cache file so we'll
        // simply echo out the data and exit.
        if (!isset($CI)) {
            echo $output;
            $this->log->log_trace('最终输出已到浏览器 总执行时长 Final output sent to browser Total execution time:' . ($elapsed * 1000) . '毫秒');
            return TRUE;
        }
        
        // --------------------------------------------------------------------
        
        // Do we need to generate profile data?
        // If so, load the Profile class and run it.
        if ($this->enable_profiler == TRUE) {
            $CI->load->library('profiler');
            if (!empty($this->_profiler_sections)) {
                $CI->profiler->set_sections($this->_profiler_sections);
            }
            
            // If the output data contains closing </body> and </html> tags
            // we will remove them and add them back after we insert the profile data
            $this->_write_profiler_page($CI->profiler->run());
        }
        
        // --------------------------------------------------------------------
        
        // Does the controller contain a function named _output()?
        // If so send the output there. Otherwise, echo it.
        if (method_exists($CI, '_output')) {
            $CI->_output($output);
        } else {
            echo $output; // Send it to the browser!
        }
        $this->log->log_trace('最终输出已到浏览器 总执行时长 Final output sent to browser Total execution time:' . ($elapsed * 1000) . '毫秒');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Write a Cache File
     * 
     * @access public
     * @param string
     * @return void
     */
    function _write_cache($output) {
        $CI = & get_instance();
        $path = $CI->config->item('cache_path');
        $cache_path = ($path === '') ? DEFAULT_CACHE_PATH : $path;
        
        if (!is_dir($cache_path) or !is_really_writable($cache_path)) {
            $this->log->log_error('无法写入缓存文件 Unable to write cache file: ' . $cache_path);
            return;
        }
        
        $uri = $CI->config->item('base_url') . $CI->config->item('index_page') . $CI->uri->uri_string();
        if ($CI->config->item('cache_query_string') && !empty($_SERVER['QUERY_STRING'])) {
            $uri .= '?' . $_SERVER['QUERY_STRING'];
        }
        
        $cache_path .= md5($uri);
        
        if (!$fp = @fopen($cache_path, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
            $this->log->log_error('无法写入缓存文件 Unable to write cache file: ' . $cache_path);
            return;
        }
        
        $expire = time() + ($this->cache_expiration * 60);
        $cache_info = serialize(array(
                'expire' => $expire,
                'headers' => $this->headers 
        ));
        $output = $cache_info . 'ENDCI--->' . $output;
        
        if (flock($fp, LOCK_EX)) {
            $result = fwrite($fp, $output);
            flock($fp, LOCK_UN);
        } else {
            $this->log->log_error('无法获得文件的安全的文件锁 Unable to secure a file lock for file at: ' . $cache_path);
            return;
        }
        fclose($fp);
        
        if (is_int($result)) {
            chmod($cache_path, 0640);
            $this->log->log_trace('缓存写入 Cache file written: ' . $cache_path);
            
            // Send HTTP cache-control headers to browser to match file cache settings.
            $this->set_cache_header($_SERVER['REQUEST_TIME'], $expire);
        } else {
            @unlink($cache_path);
            $this->log->log_error('不能写入完成的缓存内容是 Unable to write the complete cache content at: ' . $cache_path);
        }
    }

    function _write_profiler_page($output) {
        $CI = & get_instance();
        $tempFilepath = DEFAULT_LOG_PATH . DS . 'profiler_page.html';
        $this->_write_profile_html($tempFilepath, $output);
    }

    private function _write_profile_html($filepath, $output) {
        if (!$fp = @fopen($filepath, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
            return FALSE;
        }
        flock($fp, LOCK_EX);
        fwrite($fp, $output);
        flock($fp, LOCK_UN);
        fclose($fp);
        
        chmod($filepath, FILE_WRITE_MODE);
    }
    // --------------------------------------------------------------------
    
    /**
     * Update/serve a cached file
     * 
     * @access public
     * @param object	config class
     * @param object	uri class
     * @return void
     */
    public function _display_cache(&$CFG, &$URI) {
        $cache_path = ($CFG->item('cache_path') === '') ? APPPATH . 'cache/' : $CFG->item('cache_path');
        
        // Build the file path. The file name is an MD5 hash of the full URI
        $uri = $CFG->item('base_url') . $CFG->item('index_page') . $URI->uri_string;
        
        if ($CFG->item('cache_query_string') && !empty($_SERVER['QUERY_STRING'])) {
            $uri .= '?' . $_SERVER['QUERY_STRING'];
        }
        
        $filepath = $cache_path . md5($uri);
        
        if (!file_exists($filepath) or !$fp = @fopen($filepath, 'rb')) {
            return FALSE;
        }
        
        flock($fp, LOCK_SH);
        
        $cache = (filesize($filepath) > 0) ? fread($fp, filesize($filepath)) : '';
        
        flock($fp, LOCK_UN);
        fclose($fp);
        
        // Strip out the embedded timestamp
        if (!preg_match('/^(.*)ENDCI--->/', $cache, $match)) {
            return FALSE;
        }
        
        $cache_info = unserialize($match[1]);
        $expire = $cache_info['expire'];
        
        $last_modified = filemtime($cache_path);
        
        flock($fp, LOCK_UN);
        
        // Has the file expired?
        if ($_SERVER['REQUEST_TIME'] >= $expire && is_really_writable($cache_path)) {
            // If so we'll delete it.
            @unlink($filepath);
            $this->log->log_trace('缓存页面已过期,缓存文件已被删除 Cache file has expired. File deleted.');
            return FALSE;
        } else {
            // Or else send the HTTP cache control headers.
            $this->set_cache_header($last_modified, $expire);
        }
        
        // Add headers from cache file.
        foreach ($cache_info['headers'] as $header) {
            $this->set_header($header[0], $header[1]);
        }
        
        // Display the cache
        $this->_display(substr($cache, strlen($match[0])));
        $this->log->log_trace('已使用当前缓存,发送它到浏览器 Cache file is current. Sending it to browser.');
        return TRUE;
    }

    public function delete_cache($uri = '') {
        $CI = & get_instance();
        $cache_path = $CI->config->item('cache_path');
        if ($cache_path === '') {
            
            $cache_path = DEFAULT_CACHE_PATH . 'file_cache/';
        }
        
        if (!is_dir($cache_path)) {
            $this->log->log_error('不能找到缓存路径 Unable to find cache path: ' . $cache_path);
            return FALSE;
        }
        
        if (empty($uri)) {
            $uri = $CI->uri->uri_string();
            
            if ($CI->config->item('cache_query_string') && !empty($_SERVER['QUERY_STRING'])) {
                $uri .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
        
        $cache_path .= md5($CI->config->item('base_url') . $CI->config->item('index_page') . $uri);
        
        if (!@unlink($cache_path)) {
            $this->log->log_error('不能删除缓存文件 Unable to delete cache file for ' . $uri);
            return FALSE;
        }
        
        return TRUE;
    }

    public function set_cache_header($last_modified, $expiration) {
        $max_age = $expiration - $_SERVER['REQUEST_TIME'];
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $last_modified <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $this->set_status_header(304);
            exit();
        } else {
            header('Pragma: public');
            header('Cache-Control: max-age=' . $max_age . ', public');
            header('Expires: ' . gmdate('D, d M Y H:i:s', $expiration) . ' GMT');
            header('Last-modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
        }
    }
}
// END Output Class

/* End of file Output.php */
/* Location: ./system/core/Output.php */