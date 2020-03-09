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
 * Initialize the database
 * 
 * @category Database
 * @author Neowei
 * @link http://codeigniter.com/user_guide/database/
 * @param string
 * @param bool	Determines if active record should be used or not
 */
function &DB($params = '', $active_record_override = NULL) {
    $CI = &get_instance();
    $db = $CI->config->item('db');
    if ($params == '') {
        $params = $db['write'];
    } else {
        $params = $db[$params];
    }
    require_once (DEFAULT_SYSTEM_PATH . 'database/DB_driver.php');
    // $fix = $params['db_timing'];
    // if (!isset($fix) or $fix == TRUE) {
    require_once (DEFAULT_SYSTEM_PATH . 'database/DB_query_builder.php');
    require_once (DEFAULT_SYSTEM_PATH . 'database/DB_timing.php');
    if (!class_exists('CI_DB')) {
        class CI_DB extends CI_DB_timing {
        }
    }
    require_once (DEFAULT_SYSTEM_PATH . 'database/drivers/' . $params['dbdriver'] . '/' . $params['dbdriver'] . '_driver.php');
    
    // Instantiate the DB adapter
    $driver = 'CI_DB_' . $params['dbdriver'] . '_driver';
    $DB = new $driver($params);
    
    if (isset($params['stricton']) && $params['stricton'] == TRUE) {
        $DB->query('SET SESSION sql_mode="STRICT_ALL_TABLES"');
    }
    
    $DB->initialize();
    return $DB;
}



/* End of file DB.php */
/* Location: ./system/database/DB.php */