<?php
class CapitalPoolModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_capital_pool');
        $this->log->log_debug('CapitalPoolModel  model be initialized');
    }
    public function saveCapitalpool($data){
        $data['creat_time']=date("Y-m-d H:i:s",time());
        return $this->insert($data);
    }
    public function get(){
        $sql = " SELECT * FROM ".$this->tablename;
        return $this->getCacheRowArray($sql);
    }
}