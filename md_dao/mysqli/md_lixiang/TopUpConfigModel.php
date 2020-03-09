<?php
class TopUpConfigModel extends DB_Model {
    protected $tables = array(

    );

    public function __construct() { 
        parent::__construct($this->dbname,'md_top_up_config'); 
        $this->log->log_debug('TopUpConfigModel  model be initialized'); 
    }
    
    /**
     * 显示用户可选金额
     *
     */
    public function  getpay()
    {
        $sql = " SELECT amount,giving_amount,user_type,pay_type,status FROM ".$this->tablename;
        return $this->getCacheResultArray($sql);
    }


    /**
     * 根据条件获取所有提现
     */
    public function getAllTopUpConfig($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1".$where;
        return $this->getCacheResultArray($sql);
    }
    /**
     * 新增充值记录
     */
    public function addTopUpConfig($data){
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    /**
     * 获取除逻辑删除外的所有充值配置
     */
    public function getAlls(){
        $sql = " SELECT * FROM ".$this->tablename." WHERE status <> 2 ";
        return $this->getCacheResultArray($sql);
    }
    
    /**
     * 根据ID修改充值配置
     */
    public function updateTopUp($data){
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
    public function getTopUpConfigByAttr($data){
        $where="";
        foreach ($data as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }
    
    /**
     * 根据条件获取充值配置值数量
     */
    public function getTopUpByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }
    
    /**
     * 根据条件获得所有订单列表
     */
    public function getAllTopUp($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM  ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }
}
