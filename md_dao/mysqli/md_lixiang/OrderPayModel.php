<?php
class OrderPayModel extends DB_Model
{
    protected $tables = array();


    public function __construct()
    {
        parent::__construct($this->dbname, 'md_user_token');
        $this->log->log_debug('OrderPayModel  model be initialized');
    }

    /**
     * 订单明细
     *
     */
    public function  getorderpay($user_id)
    {
       // echo  $user_id;die;
        $sql = " SELECT * FROM ".$this->tablename." WHERE user_id = ? ";
        return $this->getCacheRowArray($sql,array($user_id));

    }

    /**
     * 根据属性获取该用户订单信息
     */
    public function getUserOrderInfo($parames)
    {
        $where="";
        //var_dump($parames)；die;
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE 1=1 ".$where;
        return $this->getCacheRowArray($sql);
    }

}







