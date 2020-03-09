<?php
class Models {
    private static $_instance = NULL;
    private static $instances = array();
    private static $dbs = array(
            'read' => array(),
            'write' => array() 
    );
    public static $db_driver;

    private function __construct() {
        $this->log = &get_log();
    }

    private function __clone() {
    }

    public function needReadDb($dbDriver = 'mysqli') {
        if (!isset(Models::$instances['read'][$dbDriver])) {
            $CI = &get_instance();
            Models::$instances['read'][$dbDriver] = $CI->load->database($dbDriver . '_read', TRUE);
        }
        return Models::$instances['read'][$dbDriver];
    }

    public function needWriteDb($dbDriver = 'mysqli') {
        $CI = &get_instance();
        if (!isset(Models::$instances['write'][$dbDriver])) {
            Models::$instances['write'][$dbDriver] = $CI->load->database($dbDriver . '_write', TRUE);
        }
        return Models::$instances['write'][$dbDriver];
    }

    public function setDbDriver($dbDriver) {
        self::$_instance->db_driver = $dbDriver;
    }

    public static function getInstance() {
        if (is_null(self::$_instance) || isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getModelByTable($dbname, $table) {
        $key = 'table_' . ($dbname == NULL ? '' : $dbname . '_') . $table . 'Model';
        if (!isset(Models::$instances[$key])) {
            $CI = &get_instance();
            Models::$instances[$key] = new Db_Model($dbname, $table);
            $CI->$key = Models::$instances[$key];
        }
        return Models::$instances[$key];
    }

    public function getModelByClass($path, $class) {
        $key = 'class_' . $path . '_' . $class;
        if (!isset(Models::$instances[$key])) {
            $CI = &get_instance();
            require_once DEFAULT_DAO_PATH . DS . Models::$db_driver . DS . $path . DS . $class . '.php';
            Models::$instances[$key] = new $class();
            $CI->$key = Models::$instances[$key];
            $this->log->log_trace('已实例化model:' . $path . '.' . $class);
        }
        return Models::$instances[$key];
    }
}
