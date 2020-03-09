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
 * Language Class
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Language
 * @author Neowei
 * @link http://codeigniter.com/user_guide/libraries/language.html
 */
class CI_Lang {
    
    /**
     * List of translations
     * 
     * @var array
     */
    public $language = array();
    /**
     * List of loaded language files
     * 
     * @var array
     */
    public $is_loaded = array();

    /**
     * Constructor
     * 
     * @access public
     */
    public function __construct() {
        $this->log = &get_log();
        $this->log->log_trace('语言类实例化 Language Class Initialized ');
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Load a language file
     * 
     * @access public
     * @param mixed	the name of the language file to be loaded. Can be an array
     * @param string	the language (english, etc.)
     * @param bool	return loaded array of translations
     * @param bool	add suffix to $langfile
     * @param string	alternative path to look for language file
     * @return mixed
     */
    public function load($langfile = '', $idiom = '', $return = FALSE, $add_suffix = TRUE, $alt_path = '') {
        $langfile = str_replace('.php', '', $langfile);
        
        if ($add_suffix == TRUE) {
            $langfile = str_replace('_lang.', '', $langfile) . '_lang';
        }
        
        $langfile .= '.php';
        $langfile = str_replace('/', DS, $langfile);
        $langfile = str_replace('\\', DS, $langfile);
        
        if (in_array($langfile, $this->is_loaded, TRUE)) {
            return;
        }
        
        $config = & get_config();
        
        if ($idiom == '') {
            $deft_lang = (!isset($config['language'])) ? 'english' : $config['language'];
            $idiom = ($deft_lang == '') ? 'english' : $deft_lang;
        }
        
        // Determine where the language file is and load it
        if (file_exists($filepath = DEFAULT_LANG_PATH . $idiom . DS . 'framework' . DS . $langfile)) {
            $lang = include ($filepath);
        } else {
            $filepath = DEFAULT_LANG_PATH . $idiom . DS . APP_NAME . DS . $langfile;
            if (file_exists($filepath)) {
                $lang = include ($filepath);
            } else {
                show_error('没有加载到所需要的语言文件 Unable to load the requested language file: ' . $filepath);
            }
        }
        
        if (!isset($lang)) {
            $this->log->log_error('语言文件没有数据 Language file contains no data: language/' . $idiom . '/' . $langfile);
            return;
        }
        
        if ($return == TRUE) {
            return $lang;
        }
        
        $this->is_loaded[] = $langfile;
        $this->language = array_merge($this->language, $lang);
        unset($lang);
        
        $this->log->log_trace('语言文件加载 Language file loaded: language/' . $idiom . '/' . $langfile);
        return TRUE;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Fetch a single line of text from the language array
     * 
     * @access public
     * @param string $line line
     * @return string
     */
    public function line($line = '', $log_errors = TRUE) {
        $value = isset($this->language[$line]) ? $this->language[$line] : FALSE;
        // Because killer robots like unicorns!
        if ($value === FALSE && $log_errors === TRUE) {
            $this->log->log_error('不能找到语言指定行  Could not find the language line "' . $line . '"');
        }
        return $value;
    }
}
// END Language Class

/* End of file Lang.php */
/* Location: ./system/core/Lang.php */
