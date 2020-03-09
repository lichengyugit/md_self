<?php
header("content-type:text/html;charset=utf-8");
class ShopPaymentWeixinModel extends Db_Model{
    protected $tables = array(
    );

    public function __construct() {
        parent::__construct('md_shop', 'md_shop_payment_weixin');
        $this->log->log_debug('ShopPaymentWeixinModel  model be initialized');
    }


    public function addPaymentWeixin($data){
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
}