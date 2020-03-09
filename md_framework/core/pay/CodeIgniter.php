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
 * System Initialization File
 * Loads the base classes and executes the request.
 * 
 * @package CodeIgniter
 * @subpackage codeigniter
 * @category Front-controller
 * @author Neowei
 * @link http://codeigniter.com/user_guide/
 */

/*
 * ------------------------------------------------------ Load the global functions ------------------------------------------------------
 */
require BASEPATH . 'Common.php';
/*
 * ------------------------------------------------------ Start the timer... tick tock tick tock... ------------------------------------------------------
 */
$BM = & load_class('Benchmark', 'core');
$BM->mark('total_execution_time_start');
$BM->mark('system_load_start');
// $BM->mark('loading_time:_base_classes_start');
/*
 * ------------------------------------------------------ Load the framework constants ------------------------------------------------------
 */
// require DEFAULT_CONFIG_PATH . 'constants.php';
/*
 * ------------------------------------------------------ Define a custom error handler so we can log PHP errors ------------------------------------------------------
 */
set_error_handler('_error_handler');
set_exception_handler('_exception_handler');
register_shutdown_function('_shutdown_handler');

// if (!is_php('5.3')) {
// @set_magic_quotes_runtime(0); // Kill magic quotes
// }

/*
 * ------------------------------------------------------ Set a liberal script execution time limit ------------------------------------------------------
 */
// if (function_exists("set_time_limit") == TRUE and @ini_get("safe_mode") == 0) {
// @set_time_limit(300);
// }
/*
 * ------------------------------------------------------ Instantiate the hooks class ------------------------------------------------------
 */
// $EXT = & load_class('Hooks', 'core');

/*
 * ------------------------------------------------------ Is there a "pre_system" hook? ------------------------------------------------------
 */
// $EXT->_call_hook('pre_system');

/*
 * ------------------------------------------------------ Instantiate the config class ------------------------------------------------------
 */
$CFG = & load_class('Config', 'core');
$charset = strtoupper(config_item('charset'));
ini_set('default_charset', $charset);

if (extension_loaded('mbstring')) {
    define('MB_ENABLED', TRUE);
    // mbstring.internal_encoding is deprecated starting with PHP 5.6
    // and it's usage triggers E_DEPRECATED messages.
    @ini_set('mbstring.internal_encoding', $charset);
    // This is required for mb_convert_encoding() to strip invalid characters.
    // That's utilized by CI_Utf8, but it's also done for consistency with iconv.
    mb_substitute_character('none');
} else {
    define('MB_ENABLED', FALSE);
}

// There's an ICONV_IMPL constant, but the PHP manual says that using
// iconv's predefined constants is "strongly discouraged".
if (extension_loaded('iconv')) {
    define('ICONV_ENABLED', TRUE);
    // iconv.internal_encoding is deprecated starting with PHP 5.6
    // and it's usage triggers E_DEPRECATED messages.
    @ini_set('iconv.internal_encoding', $charset);
} else {
    define('ICONV_ENABLED', FALSE);
}

if (is_php('5.6')) {
    ini_set('php.internal_encoding', $charset);
}

/*
 * ------------------------------------------------------ Instantiate the UTF-8 class ------------------------------------------------------ Note: Order here is rather important as the UTF-8 class needs to be used very early on, but it cannot properly determine if UTf-8 can be supported until after the Config class is instantiated.
 */

$UNI = & load_class('Utf8', 'core');

/*
 * ------------------------------------------------------ Instantiate the URI class ------------------------------------------------------
 */
$URI = & load_class('URI', 'core');

/*
 * ------------------------------------------------------ Instantiate the routing class and set the routing ------------------------------------------------------
 */
$RTR = & load_class('Router', 'core');

/*
 * ------------------------------------------------------ Instantiate the output class ------------------------------------------------------
 */
$OUT = & load_class('Output', 'core');

/*
 * ------------------------------------------------------ Is there a valid cache file? If so, we're done... ------------------------------------------------------
 */

/*
 * ----------------------------------------------------- Load the security class for xss and csrf support -----------------------------------------------------
 */
$SEC = & load_class('Security', 'core');

/*
 * ------------------------------------------------------ Load the Input class and sanitize globals ------------------------------------------------------
 */
$IN = & load_class('Input', 'core');

/*
 * ------------------------------------------------------ Load the Language class ------------------------------------------------------
 */
$LANG = & load_class('Lang', 'core');

/*
 * ------------------------------------------------------ Load the app controller and local controller ------------------------------------------------------
 */
// Load the base controller class
$BM->mark('system_load_end');
get_log()->log_trace('<基准测试> #### system加载执行时长:' . $BM->elapsed_time('system_load_start', 'system_load_end') * 1000 . '毫秒');

$BM->mark('my_controller_start');
require DEFAULT_SYSTEM_PATH . 'core/Controller.php';

function &get_instance() {
    return CI_Controller::get_instance();
}

if (file_exists(APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php')) {
    require_once APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php';
}

// Load the local application controller
// Note: The Router class automatically validates the controller path using the router->_validate_request().
// If this include fails it means that the default controller in the Routes.php file is not resolving to something valid.
if (!file_exists(APPPATH . 'controllers/' . $RTR->fetch_directory() . $RTR->fetch_class() . '.php')) {
    // show_error(' 不能加载默认controller类 请确保controller配置在Routes.php Unable to load your default controller. Please make sure the controller specified in your Routes.php file is valid.');
    header(BASE_URL . '/httperror/error404', TRUE, 302);
}
// Set a mark point for benchmarking

/*
 * ------------------------------------------------------ Security check ------------------------------------------------------ None of the functions in the app controller or the loader class can be called via the URI, nor can controller functions that begin with an underscore
 */
$e404 = FALSE;
$class = ucfirst($RTR->class);
$method = $RTR->method;

if (empty($class) or !file_exists(APPPATH . 'controllers/' . $RTR->directory . strtolower($class) . '.php')) {
    $e404 = TRUE;
} else {
    require_once (APPPATH . 'controllers/' . $RTR->directory . strtolower($class) . '.php');
    
    if (!class_exists($class, FALSE) or $method[0] === '_' or method_exists('CI_Controller', $method)) {
        $e404 = TRUE;
    } elseif (method_exists($class, '_remap')) {
        $params = array(
                $method,
                array_slice($URI->rsegments, 2) 
        );
        $method = '_remap';
    } // WARNING: It appears that there are issues with is_callable() even in PHP 5.2!
      // Furthermore, there are bug reports and feature/change requests related to it
      // that make it unreliable to use in this context. Please, DO NOT change this
      // work-around until a better alternative is available.
    elseif (!in_array(strtolower($method), array_map('strtolower', get_class_methods($class)), TRUE)) {
        $e404 = TRUE;
    }
}
if ($e404) {
    if (!empty($RTR->routes['404_override'])) {
        if (sscanf($RTR->routes['404_override'], '%[^/]/%s', $error_class, $error_method) !== 2) {
            $error_method = 'index';
        }
        
        $error_class = ucfirst($error_class);
        
        if (!class_exists($error_class, FALSE)) {
            if (file_exists(APPPATH . 'controllers/' . $RTR->directory . $error_class . '.php')) {
                require_once (APPPATH . 'controllers/' . $RTR->directory . $error_class . '.php');
                $e404 = !class_exists($error_class, FALSE);
            } // Were we in a directory? If so, check for a global override
elseif (!empty($RTR->directory) && file_exists(APPPATH . 'controllers/' . $error_class . '.php')) {
                require_once (APPPATH . 'controllers/' . $error_class . '.php');
                if (($e404 = !class_exists($error_class, FALSE)) === FALSE) {
                    $RTR->directory = '';
                }
            }
        } else {
            $e404 = FALSE;
        }
    }
    
    // Did we reset the $e404 flag? If so, set the rsegments, starting from index 1
    if (!$e404) {
        $class = $error_class;
        $method = $error_method;
        
        $URI->rsegments = array(
                1 => $class,
                2 => $method 
        );
    } else {
        show_404($RTR->directory . $class . '/' . $method);
    }
}
if ($method !== '_remap') {
    $params = array_slice($URI->rsegments, 2);
}
/*
 * ------------------------------------------------------ Is there a "pre_controller" hook? ------------------------------------------------------
 */
// $EXT->_call_hook('pre_controller');
/*
 * ------------------------------------------------------ Instantiate the requested controller ------------------------------------------------------
 */
// Mark a start point so we can benchmark the controller
$CI = new $class();
$BM->mark('my_controller_end');
get_log()->log_trace('<基准测试> #### my_controller实例化 总加载执行时长:' . $BM->elapsed_time('my_controller_start', 'my_controller_end') * 1000 . '毫秒');
$BM->mark('controller_execution_time_( ' . $class . ' / ' . $method . ' )_start');
/*
 * ------------------------------------------------------ Is there a "post_controller_constructor" hook? ------------------------------------------------------
 */
// $EXT->_call_hook('post_controller_constructor');

/*
 * ------------------------------------------------------ Call the requested method ------------------------------------------------------
 */
call_user_func_array(array(
        &$CI,
        $method 
), $params);
// Mark a benchmark end point
$BM->mark('controller_execution_time_( ' . $class . ' / ' . $method . ' )_end');
get_log()->log_trace('<基准测试> ####  controller方法体 : ' . $class . '/' . $method . ', 执行时长:' . $BM->elapsed_time('controller_execution_time_( ' . $class . ' / ' . $method . ' )_start', 'controller_execution_time_( ' . $class . ' / ' . $method . ' )_end') * 1000 . '毫秒');
/*
 * ------------------------------------------------------ Is there a "post_controller" hook? ------------------------------------------------------
 */
// $EXT->_call_hook('post_controller');
/*
 * ------------------------------------------------------ Send the final rendered output to the browser ------------------------------------------------------
 */
// if ($EXT->_call_hook('display_override') === FALSE) {

$OUT->enable_profiler(ENVIRONMENT != 'www');
// $OUT->cache(30);
$OUT->_display();

// }

/*
 * ------------------------------------------------------ Is there a "post_system" hook? ------------------------------------------------------
 */
// $EXT->_call_hook('post_system')

;

/*
 * ------------------------------------------------------ Close the DB connection if one exists ------------------------------------------------------
 */
if (isset($CI->mysqli_write_db)) {
    $CI->mysqli_write_db->close();
}
if (isset($CI->mysqli_read_db)) {
    $CI->mysqli_read_db->close();
}
$endTime = microtime(TRUE);
$exectime = round(($endTime - T1) * 1000);
if ($exectime > $CI->config->item('exec_timeout_log')) {
    $memory = (!function_exists('memory_get_usage')) ? '0' : round(memory_get_usage() / 1024 / 1024, 2);
    get_log()->log_warn('<警告> 执行超时 controller方法体:' . $class . ', uri :' . $class . '/' . $method . ', exec_time :' . $exectime . 'MS, 内存memory : ' . $memory . 'MB');
}
/* End of file CodeIgniter.php */
/* Location: ./system/core/CodeIgniter.php */