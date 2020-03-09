<?php
class TicketGrantModel extends DB_Model 
{
    protected $tables = array(

    );

    public function __construct() {
        parent::__construct($this->dbname,'md_ticket');
        $this->log->log_debug('CabinetModel  model be initialized');
    }

 /**
    * 根据数量生成优惠券
  */
   public function getTicketid($parames){
        $use_time=floor(($parames['end_time']-$parames['start_time'])/86400);        
        for($i=0 ; $i<$parames['num'] ; $i++){
            $ticket_id=$parames['id'].$i.mt_rand(100,999).time();
            $data[$i]=[
                'number'=>$parames['id'],
                'ticket_id'=>$ticket_id,
                'user_id'=>0,
                'use_time'=>$use_time,
                'is_use'=>0,
                'discount'=>$parames['discount'],
                'status'=>$parames['status'],
                'create_date'=>date('Y-m-d H:i:s',time()),
                'create_time'=>time()
            ];
        }
        return $data;
   }




   /**
     *      发放优惠券配置
     */
    public function addTickedGrantConfig($data)
    {
        for($i=0 ; $i<count($data) ; $i++){
            $this->insert($data[$i]);
        }
        
        return $this->lastInsertId();
    }




    /**
      * 根据条件获取优惠券数量
     */
    public function getTicketGrantNumByAttr($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT count(1) as c FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        return $this->getCacheRowArray($sql,$where)['c'];
    }



    /**
      *  获取所有配置
     */
    public function getAllTicketGrantConfig($parames)
    {
        $where="";
        foreach ($parames as $k=>$v){
            $where.=" AND ".$k." = ".$v;
        }
        $sql = " SELECT * FROM ".$this->tablename." WHERE `status` < 2 ".$where;
        $arr=$this->getCacheResultArray($sql,$where);
        return $arr;
    }






}

