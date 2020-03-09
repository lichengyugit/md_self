<?php
class UserPaymentConfigModel extends Db_Model{
    protected $tables = array(

    );

    public function __construct() {
        parent::__construct($this->dbname, 'md_user_payment_config');
        $this->log->log_debug('UserPaymentConfigModel  model be initialized');
    }
    
    /**
     * 根据条件获得多条数据
     */
    public function getConfigByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }

    /**
     * 根据名称获取城市
     */
    public function getCityname($name){
        $sql=" SELECT id,COUNT(1) as c FROM md_user_payment_config WHERE status < 2 AND city = '$name'";
        $row=$this->getCacheRowArray($sql);
        if(!$row['c']){
            unset($row['id']);
        }
        return $row;
    }

    /**
     * 新增月卡配置
     */
    public function addPaymentConfig($data){
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d,H:i:s',$data['create_time']);
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }

    /**
     * 修改配置信息
     */
    public function updatePaymentByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return true;
        }
        else{
            return false;
        }
    }

    
    /**
     * 根据条件获得单条数据
     */
    public function getConfigByAttrs($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }



}
