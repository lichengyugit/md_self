<?php
/**
 * CodeIgniter
 * 
 * @package CodeIgniter
 * @author Neowei
 * @copyright Copyright (c) 2006 - 2014 EllisLab, Inc.
 * @license http://codeigniter.com/user_guide/license.html
 * @link http://codeigniter.com
 * @since Version 2.0
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Memcached Caching Class
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Core
 * @author ExpressionEngine Dev Team
 */
class CI_Cache_file extends CI_Driver {
    protected $_cache_path;

    /**
     * Constructor
     */
    public function __construct() {
        $CI = & get_instance();
        $CI->load->helper('file');
        
        $path = $CI->config->item('file_cache_path');
        $this->_cache_path = ($path == '') ? APPPATH . 'cache/' : $path;
    }
    
    // ------------------------------------------------------------------------
    private function read_file($file) {
        if (!file_exists($file)) {
            return FALSE;
        }
        
        if (function_exists('file_get_contents')) {
            return file_get_contents($file);
        }
        
        if (!$fp = @fopen($file, FOPEN_READ)) {
            return FALSE;
        }
        
        flock($fp, LOCK_SH);
        
        $data = '';
        if (filesize($file) > 0) {
            $data = & fread($fp, filesize($file));
        }
        
        flock($fp, LOCK_UN);
        fclose($fp);
        
        return $data;
    }

    /**
     * Fetch from cache
     * 
     * @param mixed		unique key id
     * @return mixed on success/false on failure
     */
    public function get($id) {
        if (!file_exists($this->_cache_path . $id)) {
            return FALSE;
        }
        
        $data = $this->read_file($this->_cache_path . $id);
        $data = unserialize($data);
        
        if (time() > $data['time'] + $data['ttl']) {
            unlink($this->_cache_path . $id);
            return FALSE;
        }
        
        return $data['data'];
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Save into cache
     * 
     * @param string		unique key
     * @param mixed		data to store
     * @param int		length of time (in seconds) the cache is valid
     * - Default is 60 seconds
     * @return boolean on success/false on failure
     */
    public function save($id, $data, $ttl = 60) {
        $contents = array(
                'time' => time(),
                'ttl' => $ttl,
                'data' => $data 
        );
        if ($this->write_file($this->_cache_path . $id, serialize($contents))) {
            @chmod($this->_cache_path . $id, 0777);
            return TRUE;
        }
        
        return FALSE;
    }

    private function write_file($path, $data, $mode = FOPEN_WRITE_CREATE_DESTRUCTIVE) {
        if (!$fp = @fopen($path, $mode)) {
            return FALSE;
        }
        
        flock($fp, LOCK_EX);
        fwrite($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);
        
        return TRUE;
    }
    // ------------------------------------------------------------------------
    
    /**
     * Delete from Cache
     * 
     * @param mixed		unique identifier of item in cache
     * @return boolean on success/false on failure
     */
    public function delete($id) {
        $mask = $this->_cache_path . $id . '*';
        return array_map("unlink", glob($mask));
    }
    
    // ------------------------------------------------------------------------
    private function delete_files($path, $del_dir = FALSE, $level = 0) {
        // Trim the trailing slash
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        
        if (!$current_dir = @opendir($path)) {
            return FALSE;
        }
        
        while(FALSE !== ($filename = @readdir($current_dir))) {
            if ($filename != "." and $filename != "..") {
                if (is_dir($path . DIRECTORY_SEPARATOR . $filename)) {
                    // Ignore empty folders
                    if (substr($filename, 0, 1) != '.') {
                        delete_files($path . DIRECTORY_SEPARATOR . $filename, $del_dir, $level + 1);
                    }
                } else {
                    unlink($path . DIRECTORY_SEPARATOR . $filename);
                }
            }
        }
        @closedir($current_dir);
        
        if ($del_dir == TRUE and $level > 0) {
            return @rmdir($path);
        }
        
        return TRUE;
    }

    /**
     * Clean the Cache
     * 
     * @return boolean on failure/true on success
     */
    public function clean() {
        return $this->delete_files($this->_cache_path);
    }
    
    // ------------------------------------------------------------------------
    private function get_dir_file_info($source_dir, $top_level_only = TRUE, $_recursion = FALSE) {
        static $_filedata = array();
        $relative_path = $source_dir;
        
        if ($fp = @opendir($source_dir)) {
            // reset the array and make sure $source_dir has a trailing slash on the initial call
            if ($_recursion === FALSE) {
                $_filedata = array();
                $source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }
            
            // Used to be foreach (scandir($source_dir, 1) as $file), but scandir() is simply not as fast
            while(FALSE !== ($file = readdir($fp))) {
                if (@is_dir($source_dir . $file) and strncmp($file, '.', 1) !== 0 and $top_level_only === FALSE) {
                    get_dir_file_info($source_dir . $file . DIRECTORY_SEPARATOR, $top_level_only, TRUE);
                } elseif (strncmp($file, '.', 1) !== 0) {
                    $_filedata[$file] = get_file_info($source_dir . $file);
                    $_filedata[$file]['relative_path'] = $relative_path;
                }
            }
            
            return $_filedata;
        } else {
            return FALSE;
        }
    }

    /**
     * Cache Info
     * Not supported by file-based caching
     * 
     * @param string	user/filehits
     * @return mixed FALSE
     */
    public function cache_info($type = NULL) {
        return $this->get_dir_file_info($this->_cache_path);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Get Cache Metadata
     * 
     * @param mixed		key to get cache metadata on
     * @return mixed on failure, array on success.
     */
    public function get_metadata($id) {
        if (!file_exists($this->_cache_path . $id)) {
            return FALSE;
        }
        
        $data = $this->read_file($this->_cache_path . $id);
        $data = unserialize($data);
        
        if (is_array($data)) {
            $mtime = filemtime($this->_cache_path . $id);
            
            if (!isset($data['ttl'])) {
                return FALSE;
            }
            
            return array(
                    'expire' => $mtime + $data['ttl'],
                    'mtime' => $mtime 
            );
        }
        
        return FALSE;
    }
    
    // ------------------------------------------------------------------------

    /**
     * Is supported
     * In the file driver, check to see that the cache directory is indeed writable
     * 
     * @return boolean
     */
    public function is_supported() {
        return is_really_writable($this->_cache_path);
    }
}

/* End of file Cache_file.php */
/* Location: ./system/libraries/Cache/drivers/Cache_file.php */