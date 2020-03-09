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
 * @since Version 3.0
 */

/**
 * Utf8 Class
 * Provides support for UTF-8 environments
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @category UTF-8
 * @author Neowei
 * @link http://codeigniter.com/user_guide/libraries/utf8.html
 */
class CI_Utf8 {

    /**
     * Constructor
     * Determines if UTF-8 support is to be enabled
     */
    function __construct() {
        $this->log = &get_log();
        $this->log->log_trace('utf8类实例化 Utf8 Class Initialized');
        global $CFG;
        
        if (defined('PREG_BAD_UTF8_ERROR') &&         // PCRE must support UTF-8
        (ICONV_ENABLED === TRUE or MB_ENABLED === TRUE) &&         // iconv or mbstring must be installed
        strtoupper(config_item('charset')) === 'UTF-8')         // Application charset must be UTF-8
        {
            $this->log->log_trace('utf8支持开启 UTF-8 Support Enabled');
            define('UTF8_ENABLED', TRUE);
        } else {
            $this->log->log_trace('UTF-8 Support Disabled utf8支持关闭');
            define('UTF8_ENABLED', FALSE);
        }
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Clean UTF-8 strings
     * Ensures strings are UTF-8
     * 
     * @access public
     * @param string
     * @return string
     */
    public function clean_string($str) {
        if ($this->is_ascii($str) === FALSE) {
            if (MB_ENABLED) {
                $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
            } elseif (ICONV_ENABLED) {
                $str = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
            }
        }
        return $str;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Remove ASCII control characters
     * Removes all ASCII control characters except horizontal tabs,
     * line feeds, and carriage returns, as all others can cause
     * problems in XML.
     * 
     * @param string $str clean
     * @return string
     */
    public function safe_ascii_for_xml($str) {
        return remove_invisible_characters($str, FALSE);
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Convert to UTF-8
     * Attempts to convert a string to UTF-8.
     * 
     * @param string $str
     * @param string $encoding
     * @return string encoded in UTF-8 or FALSE on failure
     */
    public function convert_to_utf8($str, $encoding) {
        if (MB_ENABLED) {
            return mb_convert_encoding($str, 'UTF-8', $encoding);
        } elseif (ICONV_ENABLED) {
            return @iconv($encoding, 'UTF-8', $str);
        }
        return FALSE;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Is ASCII?
     * Tests if a string is standard 7-bit ASCII or not.
     * 
     * @param string $str check
     * @return bool
     */
    public function is_ascii($str) {
        return (preg_match('/[^\x00-\x7F]/S', $str) === 0);
    }
    
    // --------------------------------------------------------------------
}
// End Utf8 Class

/* End of file Utf8.php */
/* Location: ./system/core/Utf8.php */