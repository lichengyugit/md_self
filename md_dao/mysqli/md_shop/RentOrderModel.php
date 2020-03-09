<?php
class RentOrderModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_rent_order');
        $this->log->log_debug('RentOrderModel  model be initialized');
    }

    //根据条件获取单条订单信息
    public function getRentOrderByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheRowArray($sql);
    }

    //根据条件获取多条订单信息
    public function getRentOrdersByAttr($parames,$type=0)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        if($type){
            $where.=' AND return_vehicle_time >= '.$type;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheResultArray($sql);
    }

    /**
     * 修改订单信息
     */
    public function updateRentOrderByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }

    /**
     * 添加单条租车订单
     */
    public function saveRentOrder($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }
}
?>