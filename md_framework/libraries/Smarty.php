<?php
/**
 * Smarty Class
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Smarty
 * @author Kepler Gelotte
 * @link http://www.coolphptools.com/codeigniter-smarty
 */
require_once (DEFAULT_SYSTEM_PATH . 'vendor/autoload.php');
class CI_Smarty extends Smarty {
    private $log;
    // function CI_Smarty() {
    // parent::Smarty();
    // $this->doSmartyConfig();
    // $this->doSmartyConstants();
    
    // $this->log->log_trace('模板smarty类实例化 Smarty Class Initialized');
    // }
    public function __construct() {
        parent::__construct();
        $this->log = & get_log();
        
        $this->doSmartyConfig();
        $this->doSmartyConstants();
        
        // Assign CodeIgniter object by reference to CI
        if (method_exists($this, 'assignByRef')) {
            $ci = & get_instance();
            $this->assignByRef("ci", $ci);
        }
        
        $this->log->log_trace('模板smarty类实例化 Smarty Class Initialized');
    }

    private function doSmartyConstants() {
        $constants = get_defined_constants(TRUE);
        foreach ($constants as $key=>$val) {
            if (is_array($val)) {
                foreach ($val as $k=>$v) {
                    $this->assign($k, $v);
                }
            } else {
                $this->assign($key, $val);
            }
        }
    }

    private function doSmartyConfig() {
        $CI = & get_instance();
        $this->caching = $CI->config->item('smarty_caching');
        $this->cache_lifetime = $CI->config->item('smarty_cache_lifetime');
        $this->cache_dir = DEFAULT_CACHE_PATH . 'smarty_cache';
        $this->compile_dir = DEFAULT_CACHE_PATH . 'smarty_complie';
        $this->template_dir = DEFAULT_VIEW_PATH . APP_NAME . (IS_MOBLIE ? '_m' . DS : DS);
        $this->left_delimiter = $CI->config->item('smarty_left_delimiter');
        $this->right_delimiter = $CI->config->item('smarty_right_delimiter');
        // $this->setConfigDir(DEFAULT_VIEW_PATH . 'config');
        // $this->addPluginsDir(DEFAULT_SYSTEM_PATH . 'third_party/Smarty/libs/plugin');
    }

    /**
     * Parse a template using the Smarty engine
     * This is a convenience method that combines assign() and
     * display() into one step.
     * Values to assign are passed in an associative array of
     * name => value pairs.
     * If the output is to be returned as a string to the caller
     * instead of being output, pass true as the third parameter.
     * 
     * @access public
     * @param string
     * @param array
     * @param bool
     * @return string
     */
    public function view($template, $data = array(), $return = FALSE) {
        foreach ($data as $key=>$val) {
            $this->assign($key, $val);
        }
        
        if ($return == FALSE) {
            $CI = & get_instance();
            if (method_exists($CI->output, 'set_output')) {
                $CI->output->set_output($this->fetch($template));
            } else {
                $CI->output->final_output = $this->fetch($template);
            }
            return;
        } else {
            return $this->fetch($template);
        }
    }
}
// END Smarty Class
