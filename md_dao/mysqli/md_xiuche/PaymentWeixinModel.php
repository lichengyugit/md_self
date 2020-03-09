<?php
class PaymentWeixinModel extends DB_Model {
    protected $tables = array(
        //'user' => 'cro.sx_xiu_service'
    );

    public function __construct() {
        parent::__construct('md_xiuche', 'md_xiu_payment_weixin');
        $this->log->log_debug('PaymentWeixinModel  model be initialized');
    }

    public function addPaymentWeixin($data){
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }

    /**
     *
     * 根据属性获取合伙人信息
     */
    public function getPaymentInfoByAttr($parames){
        $where="";
        foreach ($parames as $k=>$v){
            $where.= " AND ".$k." = '".$v."'";
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }
}
?>