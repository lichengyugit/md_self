<?php
class BuyOrderInfoModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_buy_order_info');
        $this->log->log_debug('BuyOrderInfoModel  model be initialized');
    }

    //根据条件获取多条订单商品信息
    public function getBuyOrderInfosByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheResultArray($sql);
    }

    //根据条件获取单条订单商品信息
    public function getBuyOrderInfosByAttrOne($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE status=0 ".$where;
        return $this->getCacheRowArray($sql);
    }

    /**
     * 添加单条购车订单
     */
    public function saveBuyInfoOrder($data){
        $data['create_date']=date("Y-m-d H:i:s",time());
        $data['create_time']=time();
        return $this->insert($data);
    }
}
?>