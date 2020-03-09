<?php
/**
 * CodeIgniter
 * 
 * @package CodeIgniter
 * @author Neowei
 * @copyright Copyright (c) 2013 - 2015, integle, Inc.
 * @license http://codeigniter.com/user_guide/license.html
 * @link http://www.integle.com
 * @since Version 1.0
 */

/**
 * Router Class
 * Parses URIs and determines routing
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Libraries
 * @author Neowei
 * @link http://codeigniter.com/user_guide/general/routing.html
 */
class CI_Router {
    
    /**
     * Config class
     * 
     * @var object
     * @access public
     */
    public $config;
    /**
     * List of routes
     * 
     * @var array
     * @access public
     */
    public $routes = array();
    /**
     * Current class name
     * 
     * @var string
     * @access public
     */
    public $class = '';
    /**
     * Current method name
     * 
     * @var string
     * @access public
     */
    public $method = 'index';
    /**
     * Sub-directory that contains the requested controller class
     * 
     * @var string
     * @access public
     */
    public $directory = '';
    /**
     * Default controller (and method if specific)
     * 
     * @var string
     * @access public
     */
    public $default_controller;
    /**
     * Translate URI dashes
     * Determines whether dashes in controller & method segments
     * should be automatically replaced by underscores.
     * 
     * @var bool
     */
    public $translate_uri_dashes = FALSE;
    
    /**
     * Enable query strings flag
     * Determines wether to use GET parameters or segment URIs
     * 
     * @var bool
     */
    public $enable_query_strings = FALSE;

    /**
     * Constructor
     * Runs the route mapping function.
     */
    public function __construct($routing = NULL) {
        $this->log = &get_log();
        $this->config = & load_class('Config', 'core');
        $this->uri = & load_class('URI', 'core');
        
        $this->enable_query_strings = (!is_cli() && $this->config->item('enable_query_strings') === TRUE);
        $this->_set_routing();
        // Set any routing overrides that may exist in the main index file
        if (is_array($routing)) {
            if (isset($routing['directory'])) {
                $this->set_directory($routing['directory']);
            }
            if (!empty($routing['controller'])) {
                $this->set_class($routing['controller']);
            }
            if (!empty($routing['function'])) {
                $this->set_method($routing['function']);
            }
        }
        $this->log->log_trace('路由类实例化 Router Class Initialized');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set the route mapping
     * This function determines what should be served based on the URI request,
     * as well as any "routes" that have been set in the routing config file.
     * 
     * @access private
     * @return void
     */
    protected function _set_routing() {
        // Are query strings enabled in the config file? Normally CI doesn't utilize query strings
        // since URI segments are more search-engine friendly, but they can optionally be used.
        // If this feature is enabled, we will gather the directory/class/method a little differently
        if ($this->enable_query_strings) {
            $_d = $this->config->item('directory_trigger');
            $_d = isset($_GET[$_d]) ? trim($_GET[$_d], " \t\n\r\0\x0B/") : '';
            if ($_d !== '') {
                $this->uri->filter_uri($_d);
                $this->set_directory($_d);
            }
            
            $_c = trim($this->config->item('controller_trigger'));
            if (!empty($_GET[$_c])) {
                $this->uri->filter_uri($_GET[$_c]);
                $this->set_class($_GET[$_c]);
                
                $_f = trim($this->config->item('function_trigger'));
                if (!empty($_GET[$_f])) {
                    $this->uri->filter_uri($_GET[$_f]);
                    $this->set_method($_GET[$_f]);
                }
                
                $this->uri->rsegments = array(
                        1 => $this->class,
                        2 => $this->method 
                );
            } else {
                $this->_set_default_controller();
            }
            
            // Routing rules don't apply to query strings and we don't need to detect
            // directories, so we're done here
            return;
        }
        
        // Load the routes.php file.
        if (file_exists(APPPATH . 'config/routes.php')) {
            $route = include (APPPATH . 'config/routes.php');
        }
        
        // Validate & get reserved routes
        if (isset($route) && is_array($route)) {
            isset($route['default_controller']) && $this->default_controller = $route['default_controller'];
            isset($route['translate_uri_dashes']) && $this->translate_uri_dashes = $route['translate_uri_dashes'];
            unset($route['default_controller'], $route['translate_uri_dashes']);
            $this->routes = $route;
        }
        
        // Is there anything to parse?
        if ($this->uri->uri_string !== '') {
            $this->_parse_routes();
        } else {
            $this->_set_default_controller();
        }
    }

    protected function _set_request($segments = array()) {
        $segments = $this->_validate_request($segments);
        // If we don't have any segments left - try the default controller;
        // WARNING: Directories get shifted out of the segments array!
        if (empty($segments)) {
            $this->_set_default_controller();
            return;
        }
        
        if ($this->translate_uri_dashes === TRUE) {
            $segments[0] = str_replace('-', '_', $segments[0]);
            if (isset($segments[1])) {
                $segments[1] = str_replace('-', '_', $segments[1]);
            }
        }
        
        $this->set_class($segments[0]);
        if (isset($segments[1])) {
            $this->set_method($segments[1]);
        } else {
            $segments[1] = 'index';
        }
        
        array_unshift($segments, NULL);
        unset($segments[0]);
        $this->uri->rsegments = $segments;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set the default controller
     * 
     * @access private
     * @return void
     */
    protected function _set_default_controller() {
        if ($this->default_controller === FALSE) {
            show_error("无法确定什么应该被显示 默认路由没有在路由中指定的文件 Unable to determine what should be displayed. A default route has not been specified in the routing file.");
        }
        // Is the method being specified?
        if (sscanf($this->default_controller, '%[^/]/%s', $class, $method) !== 2) {
            $method = 'index';
        }
        
        if (!file_exists(APPPATH . 'controllers/' . $this->directory . $class . '.php')) {
            // This will trigger 404 later
            return;
        }
        
        $this->set_class($class);
        $this->set_method($method);
        
        // Assign routed segments, index starting from 1
        $this->uri->rsegments = array(
                1 => $class,
                2 => $method 
        );
        $this->log->log_trace('没有uri 使用默认controller No URI present. Default controller set.');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Validates the supplied segments.
     * Attempts to determine the path to
     * the controller.
     * 
     * @access private
     * @param array
     * @return array
     */
    protected function _validate_request($segments) {
        $c = count($segments);
        // Loop through our segments and return as soon as a controller
        // is found or when such a directory doesn't exist
        while($c-- > 0) {
            $test = $this->directory . ucfirst($this->translate_uri_dashes === TRUE ? str_replace('-', '_', $segments[0]) : $segments[0]);
            
            if (!file_exists(APPPATH . 'controllers/' . $test . '.php') && is_dir(APPPATH . 'controllers/' . $this->directory . $segments[0])) {
                $this->set_directory(array_shift($segments), TRUE);
                continue;
            }
            return $segments;
        }
        // This means that all segments were actually directories
        return $segments;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Parse Routes
     * This function matches any routes that may exist in
     * the config/routes.php file against the URI to
     * determine if the class/method need to be remapped.
     * 
     * @access private
     * @return void
     */
    protected function _parse_routes() {
        // Turn the segment array into a URI string
        $uri = implode('/', $this->uri->segments);
        
        // Get HTTP verb
        $http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';
        
        // Is there a literal match? If so we're done
        if (isset($this->routes[$uri])) {
            // Check default routes format
            if (is_string($this->routes[$uri])) {
                $this->_set_request(explode('/', $this->routes[$uri]));
                return;
            }             // Is there a matching http verb?
            elseif (is_array($this->routes[$uri]) && isset($this->routes[$uri][$http_verb])) {
                $this->_set_request(explode('/', $this->routes[$uri][$http_verb]));
                return;
            }
        }
        
        // Loop through the route array looking for wildcards
        foreach ($this->routes as $key=>$val) {
            // Check if route format is using http verb
            if (is_array($val)) {
                if (isset($val[$http_verb])) {
                    $val = $val[$http_verb];
                } else {
                    continue;
                }
            }
            
            // Convert wildcards to RegEx
            $key = str_replace(array(
                    ':any',
                    ':num' 
            ), array(
                    '[^/]+',
                    '[0-9]+' 
            ), $key);
            
            // Does the RegEx match?
            if (preg_match('#^' . $key . '$#', $uri, $matches)) {
                // Are we using callbacks to process back-references?
                if (!is_string($val) && is_callable($val)) {
                    // Remove the original string from the matches array.
                    array_shift($matches);
                    
                    // Execute the callback using the values in matches as its parameters.
                    $val = call_user_func_array($val, $matches);
                }                 // Are we using the default routing method for back-references?
                elseif (strpos($val, '$') !== FALSE && strpos($key, '(') !== FALSE) {
                    $val = preg_replace('#^' . $key . '$#', $val, $uri);
                }
                
                $this->_set_request(explode('/', $val));
                return;
            }
        }
        
        // If we got this far it means we didn't encounter a
        // matching route so we'll set the site default route
        $this->_set_request(array_values($this->uri->segments));
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set the class name
     * 
     * @access public
     * @param string
     * @return void
     */
    public function set_class($class) {
        $this->class = str_replace(array(
                '/',
                '.' 
        ), '', $class);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Fetch the current class
     * 
     * @access public
     * @return string
     */
    public function fetch_class() {
        return $this->class;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set the method name
     * 
     * @access public
     * @param string
     * @return void
     */
    public function set_method($method) {
        $this->method = $method;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Fetch the current method
     * 
     * @access public
     * @return string
     */
    public function fetch_method() {
        return $this->method;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set the directory name
     * 
     * @access public
     * @param string
     * @return void
     */
    public function set_directory($dir, $append = FALSE) {
        if ($append !== TRUE or empty($this->directory)) {
            $this->directory = str_replace('.', '', trim($dir, '/')) . '/';
        } else {
            $this->directory .= str_replace('.', '', trim($dir, '/')) . '/';
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Fetch the sub-directory (if any) that contains the requested controller class
     * 
     * @access public
     * @return string
     */
    public function fetch_directory() {
        return $this->directory;
    }
}
// END Router Class

/* End of file Router.php */
/* Location: ./system/core/Router.php */