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
 * Loader Class
 * Loads views and files
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @author Neowei
 * @category Loader
 * @link http://codeigniter.com/user_guide/libraries/loader.html
 */
class CI_Loader {
    
    // All these are set automatically. Don't mess with them.
    /**
     * Nesting level of the output buffering mechanism
     * 
     * @var int
     * @access protected
     */
    protected $_ci_ob_level;
    /**
     * List of paths to load libraries from
     * 
     * @var array
     * @access protected
     */
    protected $_ci_library_paths = array();
    /**
     * List of cached variables
     * 
     * @var array
     * @access protected
     */
    protected $_ci_cached_vars = array();
    /**
     * List of loaded classes
     * 
     * @var array
     * @access protected
     */
    protected $_ci_classes = array();
    /**
     * List of loaded files
     * 
     * @var array
     * @access protected
     */
    protected $_ci_loaded_files = array();
    /**
     * List of loaded helpers
     * 
     * @var array
     * @access protected
     */
    protected $_ci_helpers = array();
    /**
     * List of class name mappings
     * 
     * @var array
     * @access protected
     */
    protected $_ci_varmap = array(
            'unit_test' => 'unit',
            'user_agent' => 'agent' 
    );

    /**
     * Constructor
     * Sets the path to the view files and gets the initial output buffering level
     */
    public function __construct() {
        $this->log = &get_log();
        $this->_ci_classes = & is_loaded();
        $this->_ci_ob_level = ob_get_level();
        $this->_ci_library_paths = array(
                APPPATH,
                DEFAULT_SYSTEM_PATH 
        );
        $this->_ci_helper_paths = array(
                DEFAULT_SYSTEM_PATH,
                APPPATH 
        );
        $this->_ci_model_paths = array();
        $this->_ci_view_paths = array();
        
        $this->log->log_trace('加载类实例化 Loader Class Initialized');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Initialize the Loader
     * This method is called once in CI_Controller.
     * 
     * @param array
     * @return object
     */
    public function initialize() {
        $this->_ci_autoloader();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Is Loaded
     * A utility function to test if a class is in the self::$_ci_classes array.
     * This function returns the object name if the class tested for is loaded,
     * and returns FALSE if it isn't.
     * It is mainly used in the form_helper -> _get_validation_object()
     * 
     * @param string	class being checked for
     * @return mixed object name on the CI SuperObject or FALSE
     */
    public function is_loaded($class) {
        return array_search(ucfirst($class), $this->_ci_classes, TRUE);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Class Loader
     * This function lets users load and instantiate classes.
     * It is designed to be called from a user's app controllers.
     * 
     * @param string	the name of the class
     * @param mixed	the optional parameters
     * @param string	an optional object name
     * @return void
     */
    public function library($library, $params = NULL, $object_name = NULL) {
        if (empty($library)) {
            return $this;
        } elseif (is_array($library)) {
            foreach ($library as $key=>$value) {
                if (is_int($key)) {
                    $this->library($value, $params);
                } else {
                    $this->library($key, $params, $value);
                }
            }
            
            return $this;
        }
        
        if ($params !== NULL && !is_array($params)) {
            $params = NULL;
        }
        
        $this->_ci_load_library($library, $params, $object_name);
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Database Loader
     * 
     * @param string	the DB credentials
     * @param bool	whether to return the DB object
     * @param bool	whether to enable active record (this allows us to override the config setting)
     * @return object
     */
    public function database($params = '', $return = FALSE, $query_builder = NULL) {
        // Grab the super object
        $CI = & get_instance();
        
        // Do we even need to load the database class?
        if ($return === FALSE && $query_builder === NULL && isset($CI->db) && is_object($CI->db) && !empty($CI->db->conn_id)) {
            return FALSE;
        }
        
        require_once (DEFAULT_SYSTEM_PATH . 'database/DB.php');
        
        if ($return === TRUE) {
            return DB($params, $query_builder);
        }
        
        // Initialize the db variable. Needed to prevent
        // reference errors with some configurations
        $CI->db = '';
        
        // Load the DB class
        $CI->db = & DB($params, $query_builder);
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Load the Utilities Class
     * 
     * @return string
     */
    public function dbutil($db = NULL, $return = FALSE) {
        $CI = & get_instance();
        
        if (!is_object($db) or !($db instanceof CI_DB)) {
            class_exists('CI_DB', FALSE) or $this->database();
            $db = & $CI->db;
        }
        
        require_once (DEFAULT_SYSTEM_PATH . 'database/DB_utility.php');
        require_once (DEFAULT_SYSTEM_PATH . 'database/drivers/' . $db->dbdriver . '/' . $db->dbdriver . '_utility.php');
        $class = 'CI_DB_' . $db->dbdriver . '_utility';
        
        if ($return === TRUE) {
            return new $class($db);
        }
        
        $CI->dbutil = new $class($db);
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Load the Database Forge Class
     * 
     * @return string
     */
    public function dbforge($db = NULL, $return = FALSE) {
        $CI = & get_instance();
        if (!is_object($db) or !($db instanceof CI_DB)) {
            class_exists('CI_DB', FALSE) or $this->database();
            $db = & $CI->db;
        }
        
        require_once (DEFAULT_SYSTEM_PATH . 'database/DB_forge.php');
        require_once (DEFAULT_SYSTEM_PATH . 'database/drivers/' . $db->dbdriver . '/' . $db->dbdriver . '_forge.php');
        
        if (!empty($db->subdriver)) {
            $driver_path = DEFAULT_SYSTEM_PATH . 'database/drivers/' . $db->dbdriver . '/subdrivers/' . $db->dbdriver . '_' . $db->subdriver . '_forge.php';
            if (file_exists($driver_path)) {
                require_once ($driver_path);
                $class = 'CI_DB_' . $db->dbdriver . '_' . $db->subdriver . '_forge';
            }
        } else {
            $class = 'CI_DB_' . $db->dbdriver . '_forge';
        }
        
        if ($return === TRUE) {
            return new $class($db);
        }
        
        $CI->dbforge = new $class($db);
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Load File
     * This is a generic file loader
     * 
     * @param string
     * @param bool
     * @return string
     */
    public function file($path, $return = FALSE) {
        return $this->_ci_load(array(
                '_ci_path' => $path,
                '_ci_return' => $return 
        ));
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Set Variables
     * Once variables are set they become available within
     * the controller class and its "view" files.
     * 
     * @param array
     * @param string
     * @return void
     */
    public function vars($vars, $val = '') {
        if (is_string($vars)) {
            $vars = array(
                    $vars => $val 
            );
        }
        
        $vars = $this->_ci_object_to_array($vars);
        
        if (is_array($vars) && count($vars) > 0) {
            foreach ($vars as $key=>$val) {
                $this->_ci_cached_vars[$key] = $val;
            }
        }
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get Variable
     * Check if a variable is set and retrieve it.
     * 
     * @param array
     * @return void
     */
    public function clear_vars() {
        $this->_ci_cached_vars = array();
        return $this;
    }

    public function get_var($key) {
        return isset($this->_ci_cached_vars[$key]) ? $this->_ci_cached_vars[$key] : NULL;
    }

    public function get_vars() {
        return $this->_ci_cached_vars;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Loads a language file
     * 
     * @param array
     * @param string
     * @return void
     */
    public function language($files, $lang = '') {
        get_instance()->lang->load($files, $lang);
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Loads a config file
     * 
     * @param string
     * @param bool
     * @param bool
     * @return void
     */
    public function config($file, $use_sections = FALSE, $fail_gracefully = FALSE) {
        return get_instance()->config->load($file, $use_sections, $fail_gracefully);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Driver
     * Loads a driver library
     * 
     * @param string	the name of the class
     * @param mixed	the optional parameters
     * @param string	an optional object name
     * @return void
     */
    public function driver($library = '', $params = NULL, $object_name = NULL) {
        if (!class_exists('CI_Driver_Library')) {
            // we aren't instantiating an object here, that'll be done by the Library itself
            require DEFAULT_SYSTEM_PATH . 'libraries/Driver.php';
        }
        
        // We can save the loader some time since Drivers will *always* be in a subfolder,
        // and typically identically named to the library
        if (!strpos($library, '/')) {
            $library = ucfirst($library) . '/' . $library;
        }
        
        return $this->library($library, $params, $object_name);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Add Package Path
     * Prepends a parent path to the library, model, helper, and config path arrays
     * 
     * @param string
     * @param boolean
     * @return void
     */
    public function add_package_path($path, $view_cascade = TRUE) {
        $path = rtrim($path, '/') . '/';
        
        array_unshift($this->_ci_library_paths, $path);
        array_unshift($this->_ci_model_paths, $path);
        array_unshift($this->_ci_helper_paths, $path);
        
        $this->_ci_view_paths = array(
                $path . 'views/' => $view_cascade 
        ) + $this->_ci_view_paths;
        
        // Add config file path
        $config = & $this->_ci_get_component('config');
        $config->_config_paths[] = $path;
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get Package Paths
     * Return a list of all package paths, by default it will ignore BASEPATH.
     * 
     * @param string
     * @return void
     */
    public function get_package_paths($include_base = FALSE) {
        return ($include_base === TRUE) ? $this->_ci_library_paths : $this->_ci_model_paths;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Remove Package Path
     * Remove a path from the library, model, and helper path arrays if it exists
     * If no path is provided, the most recently added path is removed.
     * 
     * @param type
     * @param bool
     * @return type
     */
    public function remove_package_path($path = '') {
        $config = & $this->_ci_get_component('config');
        
        if ($path === '') {
            array_shift($this->_ci_library_paths);
            array_shift($this->_ci_model_paths);
            array_shift($this->_ci_helper_paths);
            array_shift($this->_ci_view_paths);
            array_pop($config->_config_paths);
        } else {
            $path = rtrim($path, '/') . '/';
            foreach (array(
                    '_ci_library_paths',
                    '_ci_model_paths',
                    '_ci_helper_paths' 
            ) as $var) {
                if (($key = array_search($path, $this->{$var})) !== FALSE) {
                    unset($this->{$var}[$key]);
                }
            }
            
            if (isset($this->_ci_view_paths[$path . 'views/'])) {
                unset($this->_ci_view_paths[$path . 'views/']);
            }
            
            if (($key = array_search($path, $config->_config_paths)) !== FALSE) {
                unset($config->_config_paths[$key]);
            }
        }
        
        // make sure the application default paths are still in the array
        $this->_ci_library_paths = array_unique(array_merge($this->_ci_library_paths, array(
                APPPATH,
                DEFAULT_SYSTEM_PATH 
        )));
        $config->_config_paths = array_unique(array_merge($config->_config_paths, array(
                APPPATH,
                DEFAULT_CONFIG_PATH 
        )));
        
        return $this;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Loader
     * This function is used to load views and files.
     * Variables are prefixed with _ci_ to avoid symbol collision with
     * variables made available to view files
     * 
     * @param array
     * @return void
     */
    protected function _ci_load($_ci_data) {
        // Set the default data variables
        $_ci_vars = (!isset($_ci_data['_ci_vars'])) ? FALSE : $_ci_data['_ci_vars'];
        $_ci_path = (!isset($_ci_data['_ci_path'])) ? FALSE : $_ci_data['_ci_path'];
        $_ci_return = (!isset($_ci_data['_ci_return'])) ? FALSE : $_ci_data['_ci_return'];
        
        $file_exists = FALSE;
        
        // Set the path to the requested file
        if ($_ci_path != '') {
            $_ci_x = explode('/', $_ci_path);
            $_ci_file = end($_ci_x);
        } 
        
        if (!$file_exists && !file_exists($_ci_path)) {
            show_error('不能加载所需的文件 Unable to load the requested file: ' . $_ci_file);
        }
        
        // This allows anything loaded using $this->load (views, files, etc.)
        // to become accessible from within the Controller and Model functions.
        
        $_ci_CI = & get_instance();
        foreach (get_object_vars($_ci_CI) as $_ci_key=>$_ci_var) {
            if (!isset($this->$_ci_key)) {
                $this->$_ci_key = & $_ci_CI->$_ci_key;
            }
        }
        
        // Extract and cache variables You can either set variables using the dedicated $this->load_vars() function or via the second parameter of this function.
        // We'll merge the two types and cache them so that views that are embedded within other views can have access to these variables.
        if (is_array($_ci_vars)) {
            $this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $_ci_vars);
        }
        extract($this->_ci_cached_vars);
        
        // Buffer the output We buffer the output for two reasons:
        // 1. Speed. You get a significant speed boost.
        // 2. So that the final rendered template can be post-processed by the output class. Why do we need post processing?
        // For one thing, in order to show the elapsed page load time. Unless we can intercept the content right before it's sent to the browser and then stop the timer it won't be accurate.
        //
        ob_start();
        
        // If the PHP installation does not support short tags we'll
        // do a little string replacement, changing the short tags
        // to standard PHP echo statements.
        
        if (!is_php('5.4') && !ini_get('short_open_tag') && config_item('rewrite_short_tags') === TRUE && function_usable('eval')) {
            echo eval('?>' . preg_replace('/;*\s*\?>/', '; ?>', str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
        } else {
            include ($_ci_path); // include() vs include_once() allows for multiple views with the same name
        }
        
        $this->log->log_trace('文件加载 File loaded: ' . $_ci_path);
        
        // Return the file data if requested
        if ($_ci_return === TRUE) {
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }
        
        // Flush the buffer... or buff the flusher? In order to permit views to be nested within other views,
        // we need to flush the content back out whenever we are beyond the first level of output buffering so that it can be seen and included properly by the first included template and any subsequent ones.
        if (ob_get_level() > $this->_ci_ob_level + 1) {
            ob_end_flush();
        } else {
            $_ci_CI->output->append_output(ob_get_contents());
            @ob_end_clean();
        }
        
        return $this;
    }

    protected function _ci_load_library($class, $params = NULL, $object_name = NULL) {
        // Get the class name, and while we're at it trim any slashes.
        // The directory path can be included as part of the class name,
        // but we don't want a leading slash
        $class = str_replace('.php', '', trim($class, '/'));
        
        // Was the path included with the class name?
        // We look for a slash to determine this
        if (($last_slash = strrpos($class, '/')) !== FALSE) {
            // Extract the path
            $subdir = substr($class, 0, ++$last_slash);
            
            // Get the filename from the path
            $class = substr($class, $last_slash);
        } else {
            $subdir = '';
        }
        
        $class = ucfirst($class);
        
        // Is this a stock library? There are a few special conditions if so ...
        if (file_exists(DEFAULT_SYSTEM_PATH . 'libraries/' . $subdir . $class . '.php')) {
            return $this->_ci_load_stock_library($class, $subdir, $params, $object_name);
        }
        
        // Let's search for the requested library file and load it.
        foreach ($this->_ci_library_paths as $path) {
            // BASEPATH has already been checked for
            if ($path === BASEPATH) {
                continue;
            }
            
            $filepath = $path . 'libraries/' . $subdir . $class . '.php';
            
            // Safety: Was the class already loaded by a previous call?
            if (class_exists($class, FALSE)) {
                // Before we deem this to be a duplicate request, let's see
                // if a custom object name is being supplied. If so, we'll
                // return a new instance of the object
                if ($object_name !== NULL) {
                    $CI = & get_instance();
                    if (!isset($CI->$object_name)) {
                        return $this->_ci_init_library($class, '', $params, $object_name);
                    }
                }
                
                $this->log->log_trace($class . ' class already loaded. Second attempt ignored. 已加载此类 忽略第二尝试');
                return;
            }             // Does the file exist? No? Bummer...
            elseif (!file_exists($filepath)) {
                continue;
            }
            
            include_once ($filepath);
            return $this->_ci_init_library($class, '', $params, $object_name);
        }
        
        // One last attempt. Maybe the library is in a subdirectory, but it wasn't specified?
        if ($subdir === '') {
            return $this->_ci_load_library($class . '/' . $class, $params, $object_name);
        }
        
        // If we got this far we were unable to find the requested class.
        $this->log->log_error('无法加载所需要的类 Unable to load the requested class: ' . $class);
        show_error('无法加载所需要的类 Unable to load the requested class: ' . $class);
    }

    protected function _ci_load_stock_library($library_name, $file_path, $params, $object_name) {
        $prefix = 'CI_';
        
        if (class_exists($prefix . $library_name, FALSE)) {
            if (class_exists(config_item('subclass_prefix') . $library_name, FALSE)) {
                $prefix = config_item('subclass_prefix');
            }
            
            // Before we deem this to be a duplicate request, let's see
            // if a custom object name is being supplied. If so, we'll
            // return a new instance of the object
            if ($object_name !== NULL) {
                $CI = & get_instance();
                if (!isset($CI->$object_name)) {
                    return $this->_ci_init_library($library_name, $prefix, $params, $object_name);
                }
            }
            
            $this->log->log_trace($library_name . ' class already loaded. Second attempt ignored. 已加载此类 忽略第二尝试');
            return;
        }
        
        $paths = $this->_ci_library_paths;
        array_pop($paths); // BASEPATH
        array_pop($paths); // APPPATH (needs to be the first path checked)
        array_unshift($paths, APPPATH);
        
        foreach ($paths as $path) {
            if (file_exists($path = $path . 'libraries/' . $file_path . $library_name . '.php')) {
                // Override
                include_once ($path);
                if (class_exists($prefix . $library_name, FALSE)) {
                    return $this->_ci_init_library($library_name, $prefix, $params, $object_name);
                } else {
                    $this->log->log_trace($path . ' exists, but does not declare ' . $prefix . $library_name . '路径存在但没有声明此类');
                }
            }
        }
        
        include_once (DEFAULT_SYSTEM_PATH . 'libraries/' . $file_path . $library_name . '.php');
        
        // Check for extensions
        $subclass = config_item('subclass_prefix') . $library_name;
        foreach ($paths as $path) {
            if (file_exists($path = $path . 'libraries/' . $file_path . $subclass . '.php')) {
                include_once ($path);
                if (class_exists($subclass, FALSE)) {
                    $prefix = config_item('subclass_prefix');
                    break;
                } else {
                    $this->log->log_trace(APPPATH . 'libraries/' . $file_path . $subclass . '.php exists, but does not declare ' . $subclass . '文件已经存在,但不能声明此类');
                }
            }
        }
        
        return $this->_ci_init_library($library_name, $prefix, $params, $object_name);
    }

    protected function _ci_init_library($class, $prefix, $config = FALSE, $object_name = NULL) {
        // Is there an associated config file for this class? Note: these should always be lowercase
        if ($config === NULL) {
            // Fetch the config paths containing any package paths
            $config_component = $this->_ci_get_component('config');
            if (is_array($config_component->_config_paths)) {
                $found = FALSE;
                foreach ($config_component->_config_paths as $path) {
                    
                    // We test for both uppercase and lowercase, for servers that
                    // are case-sensitive with regard to file names. Load global first,
                    // override with environment next
                    if (file_exists($path . 'config/' . strtolower($class) . '.php')) {
                        include ($path . 'config/' . strtolower($class) . '.php');
                        $found = TRUE;
                    } elseif (file_exists($path . 'config/' . ucfirst(strtolower($class)) . '.php')) {
                        include ($path . 'config/' . ucfirst(strtolower($class)) . '.php');
                        $found = TRUE;
                    }
                    
                    if (file_exists($path . 'config/' . ENVIRONMENT . '/' . strtolower($class) . '.php')) {
                        include ($path . 'config/' . ENVIRONMENT . '/' . strtolower($class) . '.php');
                        $found = TRUE;
                    } elseif (file_exists($path . 'config/' . ENVIRONMENT . '/' . ucfirst(strtolower($class)) . '.php')) {
                        include ($path . 'config/' . ENVIRONMENT . '/' . ucfirst(strtolower($class)) . '.php');
                        $found = TRUE;
                    }
                    
                    // Break on the first found configuration, thus package
                    // files are not overridden by default paths
                    if ($found === TRUE) {
                        break;
                    }
                }
            }
        }
        
        $class_name = $prefix . $class;
        
        // Is the class name valid?
        if (!class_exists($class_name, FALSE)) {
            $this->log->log_error('没有存在的类 Non-existent class: ' . $class_name);
            show_error('没有存在的类 Non-existent class: ' . $class);
        }
        
        // Set the variable name we will assign the class to
        // Was a custom class name supplied? If so we'll use it
        if (empty($object_name)) {
            $object_name = strtolower($class);
            if (isset($this->_ci_varmap[$object_name])) {
                $object_name = $this->_ci_varmap[$object_name];
            }
        }
        
        // Don't overwrite existing properties
        $CI = & get_instance();
        if (isset($CI->$object_name)) {
            if ($CI->$object_name instanceof $class_name) {
                $this->log->log_trace($class_name . 'has already been instantiated as "' . $object_name . '". Second attempt aborted. 已加载此类 忽略第二尝试');
                return;
            }
            
            show_error("Resource '" . $object_name . "' already exists and is not a " . $class_name . " instance. 资源已经存在但没有被实例化");
        }
        
        // Save the class name and object name
        $this->_ci_classes[$object_name] = $class;
        
        // Instantiate the class
        $CI->$object_name = isset($config) ? new $class_name($config) : new $class_name();
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Autoloader
     * The config/autoload.php file contains an array that permits sub-systems,
     * libraries, and helpers to be loaded automatically.
     * 
     * @param array
     * @return void
     */
    protected function _ci_autoloader() {
        if (file_exists(APPPATH . 'config/autoload.php')) {
            $autoload = include (APPPATH . 'config/autoload.php');
        }
        
        if (!isset($autoload)) {
            return;
        }
        if (isset($autoload['packages'])) {
            foreach ($autoload['packages'] as $package_path) {
                $this->add_package_path($package_path);
            }
        }
        
        if (count($autoload['config']) > 0) {
            foreach ($autoload['config'] as $val) {
                $this->config($val);
            }
        }
        
        foreach (array(
                'language' 
        ) as $type) {
            if (isset($autoload[$type]) && count($autoload[$type]) > 0) {
                $this->$type($autoload[$type]);
            }
        }
        
        if (isset($autoload['drivers'])) {
            foreach ($autoload['drivers'] as $item) {
                $this->driver($item);
            }
        }
        
        if (isset($autoload['libraries']) && count($autoload['libraries']) > 0) {
            // Load the database driver.
            if (in_array('database', $autoload['libraries'])) {
                $this->database();
                $autoload['libraries'] = array_diff($autoload['libraries'], array(
                        'database' 
                ));
            }
            // Load all other libraries
            foreach ($autoload['libraries'] as $item) {
                $this->library($item);
            }
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Object to Array
     * Takes an object as input and converts the class variables to array key/vals
     * 
     * @param object
     * @return array
     */
    protected function _ci_object_to_array($object) {
        return is_object($object) ? get_object_vars($object) : $object;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Get a reference to a specific library or model
     * 
     * @param string
     * @return bool
     */
    protected function &_ci_get_component($component) {
        $CI = & get_instance();
        return $CI->$component;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Prep filename
     * This function preps the name of various items to make loading them more reliable.
     * 
     * @param mixed
     * @param string
     * @return array
     */
    protected function _ci_prep_filename($filename, $extension) {
        if (!is_array($filename)) {
            return array(
                    strtolower(str_replace(array(
                            $extension,
                            '.php' 
                    ), '', $filename) . $extension) 
            );
        } else {
            foreach ($filename as $key=>$val) {
                $filename[$key] = strtolower(str_replace(array(
                        $extension,
                        '.php' 
                ), '', $val) . $extension);
            }
            
            return $filename;
        }
    }
}

/* End of file Loader.php */
/* Location: ./system/core/Loader.php */