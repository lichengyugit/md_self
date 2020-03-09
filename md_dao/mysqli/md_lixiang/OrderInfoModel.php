<?php
class OrderInfoModel extends DB_Model
{
    protected $tables = array();

    public function __construct()
    {
        parent::__construct($this->dbname, 'md_order_info');
        $this->log->log_debug('OrderInfoModel  model be initialized');
    }



    /**
     * 根据属性获取订单详情
     */
    public function getOrderInfoByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }

        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;

          return $this->getCacheRowArray($sql);

    }


    /**
     * 显示用户可选支付方式
     *
     */
    public function  paytype()
    {
        $sql = " SELECT * FROM ".$this->tablename;
        return $this->getCacheRowArray($sql);
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public function getTopUpInfoById($id){
        $sql = " SELECT * FROM ".$this->tablename." WHERE id=".$id;
        return $this->getCacheRowArray($sql);
    }
    
    /**
     * 根据条件获取订单数量
     */
    public function getOrderByAttr($parames){
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
    public function getAllOrder($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }
    
    /**
     * 新增订单详细
     */
    public function insertOrderInfo($data){
        $time=time();
        $data['create_date']=date("Y-m-d H:i:s",$time);
        $data['create_time']=$time;
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
    
    public function updateOrderInfoByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        return $update;
    }
    
}







