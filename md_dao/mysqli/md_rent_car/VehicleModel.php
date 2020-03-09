<?php
class VehicleModel extends Db_Model {
    protected $table=array(
    );

    public function __construct() {
        parent::__construct('md_rent_car','md_vehicle');
//        parent::__construct('md_rentcar','md_vehicle');
        $this->log->log_debug('VehicleModel  model be initialized');
    }


    
    /**
     * 添加数据
     */
    public function addVehicle($data){
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d H:i:s',time());
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    /**
     * 根据条件获取数量
     */
    public function getVehicleCount($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }



    /**
     * 根据条件获取单条信息
     */
    public function getVehicleByAttrs($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql,$where);
    }


    /**
     * 根据条件获取多条信息
     */
    public function getBatteryByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
             $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }
    

    /**
     * 多条件更改单个车辆状态
     */
    public function updateWheresVehicle($data,$where){
        $update=$this->update($data,$where);
        if($update){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * 根据条件获取多条车辆数据分页
     */
    public function getMerchantList($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 AND `status` = 0 ".$where." ORDER BY id DESC "." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }

    /**
     * 根据条件获取多条信息
     */
    public function getVehiclesByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql,$where);
    }

}


