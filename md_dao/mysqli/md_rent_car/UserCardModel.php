<?php
class UserCardModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_user_card');
        $this->log->log_debug('UserCardModel  model be initialized');
    }


    /**
     * 新增
     */
    public function insertUserCard($data){
        $time=time();
        $data['create_date']=date("Y-m-d H:i:s",$time);
        $data['create_time']=$time;
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    /**
     * 更改
     */
    public function updateUserCard($parames){
        $where['id']=$parames['id'];
        unset($parames['id']);
        $update=$this->update($parames, $where);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
    
    /**
     * 查询
     */
    public function getUserCardByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }

    /**
     * 更改
     */
    public function updateUserCardData($data,$where){
        $update=$this->update($data, $where);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
















    





}












