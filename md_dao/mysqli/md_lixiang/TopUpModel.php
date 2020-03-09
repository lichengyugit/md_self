<?php
class TopUpModel extends DB_Model {
    protected $tables = array(

    );

    public function __construct() { 
        parent::__construct($this->dbname,'md_top_up'); 
        $this->log->log_debug('TopUpModel  model be initialized'); 
    }

    /**
     * 根据ID获取充值记录
     */
    public function getTopUpInfoById($id){
        $sql = " SELECT * FROM ".$this->tablename." WHERE id=".$id;
        return $this->getCacheRowArray($sql);
    }

    /**
     * 根据充值订单号获取记录
     */
    public function getTopUpInfoByOrdersn($ordersn){
        $sql = " SELECT * FROM ".$this->tablename." WHERE order_sn='".$ordersn."'";
        return $this->getCacheRowArray($sql);
    }

    /**
     * 根据条件获取所有充值
     */
    public function getAllTopUp($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheResultArray($sql);
    }
    /**
     * 新增充值记录
     */
    public function addTopUp($data){
//         $data['create_time']=time();
//         $data['create_date']=date("Y-m-d H:i:s",$data['create_time']);
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }

    public function updateTopUpByAttr($data,$wheres){
        $update=$this->update($data, $wheres);
        return $update;
    }
    
    /**
     * 根据条件获取充值数量
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
     * 根据条件获得所有充值列表
     */
    public function getAllTopUpByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        return $this->getCacheResultArray($sql,$where);
    }


    /**
     * index页面获取列表数据
     */
    public function indexgetAllTopUpByAttr($limit,$parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where." LIMIT ".$limit;
        $arr=$this->getCacheResultArray($sql,$where);
        $arr['topupmodel']='充值列表';
        return $arr;
    }
    

}
?>
