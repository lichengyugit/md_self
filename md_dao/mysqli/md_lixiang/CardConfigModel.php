<?php
class CardConfigModel extends DB_Model {
    protected $tables = array(

    );

    public function __construct() { 
        parent::__construct($this->dbname,'md_card_config'); 
        $this->log->log_debug('CardConfigModel  model be initialized'); 
    }
    
    /**
     * 根据条件获取所有配置
     */
    public function getAllCardConfig($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND status < 2  ".$where;
        return $this->getCacheResultArray($sql);
    }
    /**
     * 新增月卡配置
     */
    public function addCardConfig($data){
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d,H:i:s',$data['create_time']);
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    /**
     * 获取除逻辑删除外的所有月卡配置
     */
    public function getAlls(){
        $sql = " SELECT * FROM ".$this->tablename." WHERE status = 0 ";
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 根据ID修改月卡配置
     */
    public function updateCard($data){
        $wheres=array('id'=>$data['id']);
        unset($data['id']);
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
    
    /**
     * 根据条件查询充值配置
     */
    public function getCardConfigByAttr($data){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }
    
    /**
     * 根据条件获取月卡配置数量
     */
    public function getCardConfigNumByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }
    
    /**
     * 根据条件获得所有月卡配置列表
     */
    public function getAllCardConfigs($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }

    /**
     * 修改配置信息
     */
    public function updateCardByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return true;
        }
        else{
            return false;
        }
    }

}
