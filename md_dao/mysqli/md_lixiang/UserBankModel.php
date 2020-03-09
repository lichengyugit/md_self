<?php
class UserBankModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_user_bank');
        $this->log->log_debug('UserBankModel  model be initialized');
    }
    public function saveUserBank($data){
        $data['creat_time']=date("Y-m-d H:i:s",time());
        return $this->insert($data);
    }

    //单条
    public function getUserBank($bank_number){
        $sql = " SELECT * FROM ".$this->tablename." WHERE bank_number = ".$bank_number;
        return $this->getCacheRowArray($sql);
    }
    public function getUserBankById($user_id){
        $sql = " SELECT * FROM ".$this->tablename." WHERE user_id = ".$user_id;
        return $this->getCacheRowArray($sql);
    }
}