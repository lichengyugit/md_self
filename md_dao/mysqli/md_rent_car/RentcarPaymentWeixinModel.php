<?php
header("content-type:text/html;charset=utf-8");
class RentcarPaymentWeixinModel extends Db_Model{
    protected $tables = array(
    );

    public function __construct() {
        parent::__construct('md_rent_car', 'md_rentcar_payment_weixin');
        $this->log->log_debug('RentcarPaymentWeixinModel  model be initialized');
    }


    public function addPaymentWeixin($data){
        $insert=$this->insert($data);
        return $this->lastInsertId();
    }
}