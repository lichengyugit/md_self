<?php
class SmsLogModel extends Db_Model{
    protected $tables = array(

    );
    public function __construct() {
        parent::__construct($this->dbname,'md_sms_log');
        $this->log->log_debug('SmsLogModel  model be initialized');
    }

    /**
     * 写入日志文件
     */
    public function addLog($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
}