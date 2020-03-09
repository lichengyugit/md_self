<?php
class BuyOrderModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_buy_order');
        $this->log->log_debug('BuyOrderModel  model be initialized');
    }

    //根据条件获取单条订单信息
    public function getBuyOrderByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheRowArray($sql);
    }

    //根据条件获取多条订单信息
    public function getBuyOrdersByAttr($parames,$type=0)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        if($type){
            $where.=' AND create_time >= '.$type;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheResultArray($sql);
    }


    /**
     * 添加单条购车订单
     */
    public function saveBuyOrder($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }

    /**
     * 修改订单信息
     */
    public function updateBuyOrderByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        if($update){
            return $update;
        }
        else{
            return false;
        }
    }
}
?>