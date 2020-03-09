<?php
/**
 * CodeIgniter
 * An open source application development framework for PHP 5.2.4 or newer
 * NOTICE OF LICENSE
 * Licensed under the Open Software License version 3.0
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst. It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 * 
 * @package CodeIgniter
 * @author Neowei
 * @copyright Copyright (c) 2008 - 2015, integle, Inc. (http://ellislab.com/)
 * @license http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link http://codeigniter.com
 * @since Version 3.0
 */

/**
 * CodeIgniter Redis Caching Class
 * 
 * @package CodeIgniter
 * @subpackage Libraries
 * @category Core
 * @author Neowei
 */
class CI_Cache_redis extends CI_Driver {

    /**
     *
     * @static Default config
     * @var array
     */
    // protected static $_default_config;
    public function __construct() {
        $CI = &get_instance();
        // $this->_default_config = $CI->config->item('redis');
    }
    /**
     * Redis connection
     * 
     * @var Redis
     */
    protected $_redis;
    
    // ------------------------------------------------------------------------
    
    /**
     * Get cache
     * 
     * @param string Cache key identifier
     * @return mixed
     *
     */
    public function get($key) {
        return $this->_redis->get($key);
    }

    public function keys($key) {
        return $this->_redis->keys($key);
    }

    public function mget($keys) {
        return $this->_redis->mget($keys);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Save cache
     * 
     * @param string Cache key identifier
     * @param mixed Data to save
     * @param int Time to live
     * @return bool
     *
     */
    public function save($key, $value, $ttl = NULL) {
        return ($ttl) ? $this->_redis->setex($key, $ttl, $value) : $this->_redis->set($key, $value);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Delete from cache
     * 
     * @param string Cache key
     * @return bool
     *
     */
    public function delete($key) {
        return $this->_redis->delete($this->_redis->keys($key . '*')) === 1;
        // return ($this->_redis->delete($key) === 1);
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Clean cache
     * 
     * @return bool
     * @see Redis::flushDB()
     *
     */
    public function clean() {
        return $this->_redis->flushDB();
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Get cache driver info
     * 
     * @param string Not supported in Redis.
     * Only included in order to offer a
     * consistent cache API.
     * @return array
     * @see Redis::info()
     *
     */
    public function cache_info($type = NULL) {
        return $this->_redis->info();
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Get cache metadata
     * 
     * @param string Cache key
     * @return array
     *
     */
    public function get_metadata($key) {
        $value = $this->get($key);
        
        if ($value) {
            return array(
                    'expire' => time() + $this->_redis->ttl($key),
                    'data' => $value 
            );
        }
        
        return FALSE;
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Check if Redis driver is supported
     * 
     * @return bool
     *
     */
    public function is_supported() {
        if (extension_loaded('redis')) {
            $this->_setup_redis();
            return TRUE;
        } else {
            $this->log->log_error('使用扩展必须加载Redis扩展 The Redis extension must be loaded to use Redis cache.');
            return FALSE;
        }
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Setup Redis config and connection
     * Loads Redis config file if present.
     * Will halt execution
     * if a Redis connection can't be established.
     * 
     * @return bool
     * @see Redis::connect()
     *
     */
    protected function _setup_redis() {
        $config = array();
        $CI = & get_instance();
        
        // if ($CI->config->load('redis', TRUE, TRUE)) {
        $config = $CI->config->item('redis');
        // }
        // $config = array_merge(self::$_default_config, $config);
        // var_dump($config);
        $this->_redis = new Redis();
        
        try {
            $this->_redis->pconnect($config['host'], $config['port'], $config['timeout']);
            $this->_redis->select(1);
        } catch (RedisException $e) {
            show_error('Redis connection refused. ' . $e->getMessage());
        }
        
        if (isset($config['password'])) {
            $this->_redis->auth($config['password']);
        }
    }
    
    // ------------------------------------------------------------------------
    
    /**
     * Class destructor
     * Closes the connection to Redis if present.
     * 
     * @return void
     *
     */
    public function __destruct() {
        if ($this->_redis) {
            $this->_redis->close();
        }
    }
}