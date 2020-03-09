<?php
/**
 * CodeIgniter
 * An open source application development framework for PHP 5.1.6 or newer
 * 
 * @package CodeIgniter
 * @author Neowei
 * @copyright Copyright (c) 2013 - 2015, integle, Inc.
 * @license http://codeigniter.com/user_guide/license.html
 * @link http://www.integle.com
 * @since Version 1.0
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Config Class
 * This class contains functions that enable config files to be managed
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Libraries
 * @author Neowei
 * @link http://codeigniter.com/user_guide/libraries/config.html
 */
class CI_Config {
    
    /**
     * List of all loaded config values
     * 
     * @var array
     */
    public $config = array();
    /**
     * List of all loaded config files
     * 
     * @var array
     */
    public $is_loaded = array();
    /**
     * List of paths to search when trying to load a config file
     * 
     * @var array
     */
    public $_config_paths = array(
            APPPATH,
            DEFAULT_CONFIG_PATH 
    );

    /**
     * Constructor
     * Sets the $config data from the primary config.php file as a class variable
     * 
     * @access public
     * @param string	the config file name
     * @param boolean if configuration values should be loaded into their own section
     * @param boolean true if errors should just return false, false if an error message should be displayed
     * @return boolean if the file was successfully loaded or not
     */
    public function __construct() {
        $this->config = & get_config();
        $this->log = &  get_log();
        $this->log->log_trace("配置类实例化 Config Class Initialized");
        
        // Set the base_url automatically if none was provided
        // if ($this->config['base_url'] == '') {
        if (isset($_SERVER['HTTP_HOST'])) {
            $base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $base_url .= '://' . $_SERVER['HTTP_HOST'];
            $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        } else {
            $base_url = 'http://localhost/';
        }
        
        $this->set_item('base_url', $base_url);
        // }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Load Config File
     * 
     * @access public
     * @param string	the config file name
     * @param boolean if configuration values should be loaded into their own section
     * @param boolean true if errors should just return false, false if an error message should be displayed
     * @return boolean the file was loaded correctly
     */
    public function load($file = '', $use_sections = FALSE, $fail_gracefully = FALSE) {
        $file = ($file == '') ? 'config' : str_replace('.php', '', $file);
        $loaded = FALSE;
        
        foreach ($this->_config_paths as $path) {
            $file_path = $path . 'config/' . $file . '.php';
            
            if (in_array($file_path, $this->is_loaded, TRUE)) {
                $loaded = TRUE;
            }
            
            $config = include ($file_path);
            
            if (!isset($config) or !is_array($config)) {
                if ($fail_gracefully === TRUE) {
                    return FALSE;
                }
                show_error('你的配置文件不是一个有效的配置数组 Your ' . $file_path . ' file does not appear to contain a valid configuration array. ');
            }
            
            if ($use_sections === TRUE) {
                if (isset($this->config[$file])) {
                    $this->config[$file] = array_merge($this->config[$file], $config);
                } else {
                    $this->config[$file] = $config;
                }
            } else {
                $this->config = array_merge($this->config, $config);
            }
            
            $this->is_loaded[] = $file_path;
            unset($config);
            
            $loaded = TRUE;
            $this->log->log_trace('配置文件加载 Config file loaded: ' . $file_path);
            break;
        }
        
        if ($loaded === FALSE) {
            if ($fail_gracefully === TRUE) {
                return FALSE;
            }
            
            show_error('配置文件不存在 The configuration file ' . $file . '.php does not exist.');
        }
        
        return TRUE;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Fetch a config file item
     * 
     * @access public
     * @param string	the config item name
     * @param string	the index name
     * @param bool
     * @return string
     */
    public function item($item, $index = '') {
        if ($index == '') {
            return isset($this->config[$item]) ? $this->config[$item] : NULL;
        }
        return isset($this->config[$index], $this->config[$index][$item]) ? $this->config[$index][$item] : NULL;
    }
    // --------------------------------------------------------------------
    
    /**
     * Fetch a config file item - adds slash after item (if item is not empty)
     * 
     * @access public
     * @param string	the config item name
     * @param bool
     * @return string
     */
    public function slash_item($item) {
        if (!isset($this->config[$item])) {
            return NULL;
        } elseif (trim($this->config[$item]) === '') {
            return '';
        }
        
        return rtrim($this->config[$item], DS) . DS;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Site URL
     * Returns base_url .
     * index_page [. uri_string]
     * 
     * @access public
     * @param string	the URI string
     * @return string
     */
    public function site_url($uri = '', $protocol = NULL) {
        $base_url = $this->slash_item('base_url');
        
        if (isset($protocol)) {
            $base_url = $protocol . substr($base_url, strpos($base_url, '://'));
        }
        
        if (empty($uri)) {
            return $base_url . $this->item('index_page');
        }
        
        $uri = $this->_uri_string($uri);
        
        if ($this->item('enable_query_strings') === FALSE) {
            $suffix = isset($this->config['url_suffix']) ? $this->config['url_suffix'] : '';
            
            if ($suffix !== '') {
                if (($offset = strpos($uri, '?')) !== FALSE) {
                    $uri = substr($uri, 0, $offset) . $suffix . substr($uri, $offset);
                } else {
                    $uri .= $suffix;
                }
            }
            
            return $base_url . $this->slash_item('index_page') . $uri;
        } elseif (strpos($uri, '?') === FALSE) {
            $uri = '?' . $uri;
        }
        
        return $base_url . $this->item('index_page') . $uri;
    }
    
    // -------------------------------------------------------------
    
    /**
     * Base URL
     * Returns base_url [.
     * uri_string]
     * 
     * @access public
     * @param string $uri
     * @return string
     */
    public function base_url($uri = '', $protocol = NULL) {
        $base_url = $this->slash_item('base_url');
        
        if (isset($protocol)) {
            $base_url = $protocol . substr($base_url, strpos($base_url, '://'));
        }
        return $base_url . ltrim($this->_uri_string($uri), '/');
    }
    
    // -------------------------------------------------------------
    
    /**
     * Build URI string for use in Config::site_url() and Config::base_url()
     * 
     * @access protected
     * @param $uri
     * @return string
     */
    protected function _uri_string($uri) {
        if ($this->item('enable_query_strings') === FALSE) {
            if (is_array($uri)) {
                $uri = implode('/', $uri);
            }
            return trim($uri, '/');
        } elseif (is_array($uri)) {
            return http_build_query($uri);
        }
        
        return $uri;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * System URL
     * 
     * @deprecated 3.0.0	Encourages insecure practices
     * @return string
     */
    public function system_url() {
        $x = explode('/', preg_replace('|/*(.+?)/*$|', '\\1', BASEPATH));
        return $this->slash_item('base_url') . end($x) . '/';
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set a config file item
     * 
     * @access public
     * @param string	the config item key
     * @param string	the config item value
     * @return void
     */
    public function set_item($item, $value) {
        $this->config[$item] = $value;
    }
}

// END CI_Config class

/* End of file Config.php */
/* Location: ./system/core/Config.php */
